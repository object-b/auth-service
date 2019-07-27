<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User; 
use Illuminate\Support\Facades\Auth; 
use Validator;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class UserController extends Controller 
{
    public $successStatus = 200;
    
    public function login()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) { 
            // user credentials are correct. Issue a token and use it in next requests
            $user = Auth::user(); 
            $success['access_token'] =  $user->createToken('My Token')->accessToken;

            $user->api_token = $success['access_token'];
            $user->save();
            
            return response()->json(['data' => $success], $this->successStatus);
        } else {
            // invalid credentials, act accordingly
            return response()->json([
                'message' => 'Пользователь не существует. Пожалуйста, проверьте правильность ввода данных.'
            ], 401); 
        }
    }
    
    public function register(Request $request) 
    { 
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ], [
            'email.unique' => 'Такой адрес электронной почты уже зарегистрирован в системе.',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 401);
        }
        
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        
        $user = User::create($input);
        
        $success['access_token'] = $user->createToken('My Token')->accessToken; 
        $success['name'] = $user->name;

        $user->api_token = $success['access_token'];
        $user->save();
        
        return response()->json(['data' => $success], $this->successStatus);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $request->user()->token()->delete();
        }
        
        return response()->json(['message' => 'User logged out.'], 200);
    }
    
    public function details()
    { 
        $user = Auth::user();
        
        return response()->json(['data' => $user], $this->successStatus); 
    }

    public function updateUser(Request $request)
    { 
        $user = User::find(Auth::user()->id);
        $user->update($request->all());
        
        return response()->json(['data' => $user], $this->successStatus); 
    }

    public function updateApiToken(Request $request)
    {
        $user = Auth::user();
        $user->api_token = $request->bearerToken();
        $user->save();
        
        return response()->json($user->save(), $this->successStatus); 
    }

    public function social(Request $request)
    {
        $domain = config('app.url');
        $client_id = 2;
        $client_secret = DB::table('oauth_clients')->where('id', $client_id)->value('secret');
        $http = new Client;

        // First, we get token with SocialGate
        $oauthResponse = $http->post($domain . '/oauth/token', [
            'form_params' => [
                'grant_type' => 'social', // static 'social' value
                'client_id' => $client_id, // client id
                'client_secret' => $client_secret, // client secret
                'provider' => $request->provider, // name of provider (e.g., 'facebook', 'google' etc.)
                'access_token' => $request->access_token, // access token issued by specified provider
            ],
        ]);

        $oauthData = json_decode($oauthResponse->getBody()->getContents(), true);

        if ($oauthResponse->getStatusCode() === 200) {
            $accessToken = Arr::get($oauthData, 'access_token');
            $headers = [
                'Authorization' => 'Bearer ' . $accessToken
            ];

            // Second, we updating user social oauth token in DB. Just because
            $http->post($domain . '/api/token', [
                'headers' => $headers
            ]);

            // And if we have vk there, update user email
            if ($request->provider === 'vkontakte' && $request->email) {
                $http->post($domain . '/api/update', [
                    'form_params' => [
                        'email' => $request->email,
                    ],
                    'headers' => $headers
                ]);
            }
        }
        
        return response()->json($oauthData, $oauthResponse->getStatusCode());
    }
}