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
     */
    public function index(Request $request)
{
    
    $count = $request->query('count', 5);
    $page = $request->query('page', 1);

    $fails = [];


    if (!is_numeric($count) || (int)$count <= 0) {
        $fails['count'] = 'The count must be a positive integer.';
    }

    if (!is_numeric($page) || (int)$page < 1 || (int)$page >  ceil(User::count()/$count) ) {
        $fails['page'] = 'The page must be at least 1 and not beyond the total amount o users.';
    }

    if(!empty($fails))
    return view('users.index', [
        'usersResponse' => [
            'success' => false,
            'page' => 1,
            'count' => $count,
            'total_pages' => 0,
            'total_users' => 0,
            'links' => [
                'next_url' => null,
                'prev_url' => null
            ],
            'users' => [],
            'usersResponse422' => [
                'success' => false,
                'message' => 'The page does not exist: the page number is too high.',
                'fails'=> $fails
            ],
        ],
    ]);


    if ((int)$page >  User::count()/$count + $count) {
        $users = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 1);

        return view('users.index', [
            'usersResponse' => [
                'success' => false,
                'page' => 0,
                'count' => $count,
                'total_pages' => 0,
                'total_users' => 0,
                'links' => [
                    'next_url' => null,
                    'prev_url' => null
                ],
                'users' => [],
            ],
            'usersResponse404' => [
                'success' => false,
                'message' => 'The page does not exist: the page number is too high'
            ],
        ]);
    }

    if (!empty($fails)) {
        $users = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 1);

        return view('users.index', [
            'usersResponse' => [
                'success' => false,
                'page' => 0,
                'count' => $count,
                'total_pages' => 0,
                'total_users' => 0,
                'links' => [
                    'next_url' => null,
                    'prev_url' => null
                ],
                'users' => [],
            ],
            'usersResponse422' => [
                'success' => false,
                'message' => 'Validation failed',
                'fails' => $fails,
            ],
        ]);
    }

    // Fetch the users with the validated count and page values
    $users = User::orderBy('id', 'desc')->paginate((int)$count, ['*'], 'page', (int)$page);

    return view('users.index', [
        'users' => $users,
        'usersResponse' => [
            'success' => true,
            'page' => $users->currentPage(),
            'count' => $count,
            'total_pages' => $users->lastPage(),
            'total_users' => $users->total(),
            'links' => [
                'next_url' => $users->nextPageUrl(),
                'prev_url' => $users->previousPageUrl(),
            ],
            'users' => UserResource::collection($users),
        ],
    ]);
}




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $rules = [
            'name' => 'required|string|min:2|max:60',
            'email' => 'required|string|min:6|max:100',
            'phone' => 'required|string|regex:/^\+380\d{9}$/',
            'position_id' => 'required|integer|min:1',
            'photo' => 'required|image|mimes:jpeg,jpg,png|dimensions:min_width=70,min_height=70',
        
    ];

    $validator = Validator::make($request->all(), $rules);


    if ($validator->fails()) {
        return response()->json([
            'message' => 'Wrong data entered. Validation failed.',
            'errors' => $validator->errors()
        ], 422);
    }

    if ($validator->passes()) {
        if(!User::where('email', $request->email)->doesntExist() || !User::where('phone', $request->phone)->doesntExist())
        return response()->json([
            'message' => 'Wrong data entered. Validation failed. The user with this phone number or email already exists.',
        ], 409);
    }


    $validatedData = $validator->validated();

    $imgPath = null;
    try {
        if ($request->hasFile('photo')) {
            $originalImagePath = $request->file('photo')->store('profile_images', 'public');
            $this->resizeAndCompressImage(storage_path('app/public/' . $originalImagePath));
            $imgPath = $originalImagePath;
        }

        
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'photo' => $imgPath,
            'phone'=>$validatedData['phone'],
            'position_id'=>$validatedData['position_id'],
            'email_verified_at'=>Carbon::now()
        ]);
        
        \Log::info('User registered successfully: ' . $user->email);
        setcookie('access_token_'.Token::first()->id_, '', -1, '/');
        Token::truncate();
        return redirect('/users')->with('success', 'Registration successful.');

    } catch (\Exception $e) {
        \Log::error('Registration error: ' . $e->getMessage());
        return response()->json(['Registration failed.'.$e->getMessage()]);
    }
}



    protected function resizeAndCompressImage($imagePath)
    {
        try {
            \Tinify\setKey(env('TINYPNG_API_KEY'));

            $source = \Tinify\fromFile($imagePath);
            
            // Resize the image
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
     */
    public function show(Request $request)
    {
        $fails = [];

        try {
         
            $user = User::findOrFail($request->id);
            return view('users.show', [
                
                    'success'=> true,
                    'user' => $user
                
            ]);
        } catch (ModelNotFoundException $e) {
            
            if(!is_numeric($request->id) || $request->id < 0){

                return view('users.show', [
                    'userResponse422' => [
                        'success'=> false,
                        'message'=> 'User ID must be a positive integer'
                    ],
                    'userResponse' => [],
                ]);
            }
            return view('users.show', [
                'userResponse404' => [
                    'success'=> false,
                    'message'=> 'This user does not exist'
                ],
                'userResponse' => [],
            ]);
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