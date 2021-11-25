<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Token;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CAuth
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
        //Get Bearer Token
        $getToken = $request->bearerToken();

        if(!$getToken)
        {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }
        
        $ifJSON =  $header = $request->header('Accept');

        
        if($ifJSON != 'application/json')
        {
            return response([
                'message' => 'API expects JSON data, set Accept Header as application/json'
            ]);
        }

        //Decode
        $decoded = JWT::decode($getToken, new Key('ProgrammersForce', 'HS256'));

        //Get Id
        $userId = $decoded->data;

        $userExists = Token::where('user_id', $userId)->first();

        if (isset($userExists)) {
            return $next($request);
        } else {
            return response([
                'message' => 'Unauthorized'
            ],401);
        }
    }
}
