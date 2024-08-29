<?php

namespace App\Http\Controllers;

use App\Models\Token;
use Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TokenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {   
   
        $this->deleteTokens();

        
        $tokenValue = Str::random(60);
        $id = uniqid();
        $expiresAt = now()->addMinutes(40);

        $hashedToken = Hash::make($tokenValue);

      
        $token = Token::create([
            'id_' => $id,
            'token' => $tokenValue,
            'expires_at' => $expiresAt
        ]);

        $cookieName = 'access_token_' . $id;
        $cookieValue = $hashedToken;
        $cookieExpire = $expiresAt->timestamp;

      
        setcookie($cookieName, $cookieValue, $cookieExpire, "/", null, false, true);

        \Log::info('Token:', ['token' => $token]);
        \Log::info('Cookie:', ['name' => $cookieName, 'value' => $cookieValue]);


        return redirect('/users')->with('success', 'Token created successfully.');
    }

    /**
     * Delete all existing token cookies and truncate the Token table.
     */
    public function deleteTokens()
    {
        $tokens  = [];
       
        if(!empty($_COOKIE)){
            foreach ($_COOKIE as $name => $value) {
                if(str_contains($name, 'access_token')){
                    $tokens[] = $name;
                }
            }

            if(!empty($tokens)){
                foreach($tokens as $token){
                    setcookie($token, '', -1, '/'); 
                }
            }
        }

        Token::truncate();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Token $token)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Token $token)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Token $token)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Token $token)
    {
        //
    }
}
