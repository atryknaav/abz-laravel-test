<?php

namespace App\Http\Middleware;

use Error;
use Closure;
use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class VerifyTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {   
        $ckName = null;  // Variable to store the cookie name
        $tokenValue = null;  // Variable to store the token value from the cookie

        // Check if there are any cookies
        if (!empty($_COOKIE)) {
            // Iterate through cookies to find one with 'access_token' in its name
            foreach ($_COOKIE as $name => $value) {
                if (strpos($name, 'access_token') !== false) {
                    $ckName = $name;
                    $tokenValue = $value;
                    break;
                }
            }

            // If no valid token cookie is found, return an unauthorized response
            if ($ckName === null) {
                $cookies = [];
                // Collect all cookies for debugging or logging
                foreach ($_COOKIE as $name => $value) {
                    $cookies[] = "$name: $value";
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: you are not authorized for this action. The cookie does not exist. Click the green button to receive permissions.',
                ], 401);
            }
        } else {
            // If no cookies are present, return an unauthorized response
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: you are not authorized for this action. The cookie does not exist.',
            ], 401);
        }

        // Extract the token ID from the cookie name
        $id = str_replace('access_token_', '', $ckName);

        // Retrieve the token record from the database using the ID
        $tokenRow = Token::where('id_', $id)->first();

        // If the token record does not exist, return an unauthorized response
        if (!$tokenRow) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: the token does not exist in the database.',
            ], 401);
        }

        // Check if the provided token value matches the hashed token stored in the database
        try{
            Hash::needsRehash($tokenValue, $tokenRow->token);
        }
        catch (Error $e){
            return response()->json([
                'success' => false,
                'message' => 'The cookie value does not use the Bcrypt algorithm.',
            ], 401);
        }
        if (!Hash::check($tokenValue, $tokenRow->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: the token value is invalid.',
            ], 401);
        }

        // Check if the token has expired
        if ($tokenRow->expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'The token expired.',
            ], 401);
        }

        // If all checks pass, proceed with the request
        return $next($request);
    }
}
