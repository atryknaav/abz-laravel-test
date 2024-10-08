<?php

namespace App\Http\Controllers;

use Error;
use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Token;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\RegistrationRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     * Handles pagination and validation of query parameters.
     */
    public function index(Request $request)
    {
        $count = $request->query('count', 6);
        $page = $request->query('page', 1);
        
        $fails = [];
        
        // Validate 'count' parameter
        if (!is_numeric($count) || (int)$count <= 0) {
            $fails['count'] = 'The count must be a positive integer.';
        }
        if(is_string($count)) $count = intval($count);

        // Validate 'page' parameter
        if (!is_numeric($page) || (int)$page < 1) {
            $fails['page'] = 'The page must be at least 1 and not beyond the total amount of users.';
        }

        // If validation fails, return error response
        if(!empty($fails))
            return response()->json([
                'success'=> false,
                'message' => 'Wrong data entered. Validation failed.',
                'fails' => $fails
            ], 422);

        // Check if requested page exceeds the total number of pages
        if ((int)$page >  ceil(User::count()/$count) ) {
            $users = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 1);       
            return response()->json([
                'success'=> false,
                'message' => 'The page does not exist: the page number is too high',
                'fails' => $fails
            ], 404);
        }

        // Fetch paginated users
        $users = User::orderBy('id', 'desc')->paginate((int)$count, ['*'], 'page', (int)$page);
        $users->withPath("/users?count=$count");

        // Return view with paginated users and metadata
        return view('users.index', [
            'success'=> true,
            'users' => $users,
            'page' => $users->currentPage(),
            'count' => $count,
            'total_pages' => $users->lastPage(),
            'total_users' => $users->total(),
            'links' => [
                    'next_url' => $users->nextPageUrl(),
                    'prev_url' => $users->previousPageUrl(),
                
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Handles validation, image processing, and user creation.
     */
    public function store(Request $request)
    {
        // Validation rules
        $rules = [
            'name' => 'required|string|min:2|max:60',
            'email' => 'required|string|min:6|max:100',
            'phone' => 'required|string|regex:/^\+380\d{9}$/',
            'position_id' => 'required|integer|min:1',
            'photo' => 'required|image|mimes:jpeg,jpg,png|dimensions:min_width=70,min_height=70',
        ];

        // Validate request data
        $validator = Validator::make($request->all(), $rules);

        // Handle validation failure
        if ($validator->fails()) {
            return response()->json([
                'success'=> false,
                'message' => 'Wrong data entered. Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for existing email or phone number
        if ($validator->passes()) {
            if (!User::where('email', $request->email)->doesntExist() || !User::where('phone', $request->phone)->doesntExist()) {
                return response()->json([
                    'success'=> false,
                    'message' => 'Wrong data entered. Validation failed. The user with this phone number or email already exists.',
                ], 409);
            }
        }

        // Prepare data for user creation
        $validatedData = $validator->validated();
        $imgPath = null;

        try {
            // Handle image upload and processing
            if ($request->hasFile('photo')) {
                $originalImagePath = $request->file('photo')->store('profile_images', 'public');
                $this->resizeAndCompressImage(storage_path('app/public/' . $originalImagePath));
                $imgPath = $originalImagePath;
            }

            // Create the user
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'photo' => $imgPath,
                'phone' => $validatedData['phone'],
                'position_id' => $validatedData['position_id'],
                'email_verified_at' => Carbon::now()
            ]);

            \Log::info('User registered successfully: ' . $user->email);

            // Clear token cookie and truncate tokens
            setcookie('access_token_' . Token::first()->id_, '', -1, '/');
            Token::truncate();

            return redirect('/users')->with('success', 'Registration successful.');

        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            return response()->json(['Registration failed.' . $e->getMessage()]);
        }
    }

    /**
     * Resize and compress the uploaded image using TinyPNG API.
     */
    protected function resizeAndCompressImage($imagePath)
    {
        try {
            \Tinify\setKey(env('TINYPNG_API_KEY'));

            $source = \Tinify\fromFile($imagePath);
            
            // Resize image to fit within 70x70 pixels
            $resized = $source->resize([
                "method" => "cover",
                "width" => 70,
                "height" => 70
            ]);
            
            $resized->toFile($imagePath);
        } catch (\Exception $e) {
            \Log::error('TinyPNG error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * Fetches a user by ID and handles errors if not found or invalid ID.
     */
    public function show(Request $request)
    {
        // Find user by ID
        if (!is_numeric($request->id) || !User::find($request->id, 'id')) {
                return response()->json( [
                   
                        'success' => false,
                        'message' => 'This user does not exist'
                    
                ], 404);
            }
          
        if(User::find($request->id)){
            return view('users.show', [
                'success' => true,
                'user' => User::find($request->id)
            ]);
        }else{
            return response()->json( [
                   
                'success' => false,
                'message' => 'Unexpected error'
            
        ], 404);
        }
    
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}