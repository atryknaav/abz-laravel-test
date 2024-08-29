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
     * 
     * This method is intended to list all tokens but is currently not implemented.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     * 
     * This method creates a new token, deletes existing tokens, and sets a cookie for the new token.
     */
    public function create()
    {
        // Delete all existing tokens and clear any associated cookies before createing a new one
        $this->deleteTokens();

        // Generate a new random token value and unique ID
        $tokenValue = Str::random(60);  
        $id = uniqid();  
        $expiresAt = now()->addMinutes(40);  

        // Hash the token value for storage
        $hashedToken = Hash::make($tokenValue);

        // Create a new token record in the database
        $token = Token::create([
            'id_' => $id,
            'token' => $tokenValue,  // Store plain token value, not hashed
            'expires_at' => $expiresAt
        ]);

        // Set a cookie for the new token
        $cookieName = 'access_token_' . $id;
        $cookieValue = $hashedToken;  
        $cookieExpire = $expiresAt->timestamp;  

        // Set the cookie in the response
        setcookie($cookieName, $cookieValue, $cookieExpire, "/", null, false, true);

        // Log information about the created token and cookie
        \Log::info('Token created:', ['token' => $token]);
        \Log::info('Cookie set:', ['name' => $cookieName, 'value' => $cookieValue]);

        // Redirect to the users page with a success message
        return redirect('/users')->with('success', 'Token created successfully.');
    }

    /**
     * Delete all existing token cookies and truncate the Token table.
     * 
     * This method removes all cookies associated with tokens and clears the token records from the database.
     */
    public function deleteTokens()
    {
        $tokens = [];

        // Check if there are any cookies and collect those related to tokens
        if (!empty($_COOKIE)) {
            foreach ($_COOKIE as $name => $value) {
                if (str_contains($name, 'access_token')) {
                    $tokens[] = $name;
                }
            }

            // Delete the collected token cookies
            if (!empty($tokens)) {
                foreach ($tokens as $token) {
                    setcookie($token, '', -1, '/');  // Expire the token cookies
                }
            }
        }

        // Clear all token records from the database
        Token::truncate();
    }

    /**
     * Store a newly created resource in storage.
     * 
     * This method is intended to store a new token but is currently not implemented.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     * 
     * This method is intended to display a specific token but is currently not implemented.
     */
    public function show(Token $token)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * 
     * This method is intended to show a form for editing a specific token but is currently not implemented.
     */
    public function edit(Token $token)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * 
     * This method is intended to update a specific token but is currently not implemented.
     */
    public function update(Request $request, Token $token)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * 
     * This method is intended to delete a specific token but is currently not implemented.
     */
    public function destroy(Token $token)
    {
        //
    }
}
