<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class VerifyTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {   
        $ckName = null;
        $tokenValue = null;

        if (!empty($_COOKIE)) {
        
            foreach ($_COOKIE as $name => $value) {
                if (strpos($name, 'access_token') !== false) {
                    $ckName = $name;
                    $tokenValue = $value;
                    break;
                }
            }
        
            if ($ckName === null) {
                $cookies = [];
                foreach ($_COOKIE as $name => $value) {
                    $cookies[] = "$name: $value";
                }
        
                return response()->json([
                    'message' => 'Unauthorized: you are not authorized for this action. The cookie does not exist. Click the green button to receive permissions.',
                ], 401);
            }

            
        }
        else{
            return response()->json(['message' => 'Unauthorized: you are not authorized for this action. The cookie does not exist.'], 401);
        }

        $id = str_replace('access_token_', '', $ckName);

        $tokenRow = Token::where('id_', $id)->first();
        
        // return response()->json(['message' => 'Unauthorized on db, id: '.$id], 401);
        if($tokenRow){
            if( !Hash::check($tokenRow->token, $tokenValue)) {
            }
        }
        else{
            return response()->json(['message' => 'Unauthorized: the token does not exist in the data base.'], 401);

        }

        if ($tokenRow->expires_at < now()) {
            return response()->json(['message' => 'The token expired.'], 401);
        }

        return $next($request);
    }
}