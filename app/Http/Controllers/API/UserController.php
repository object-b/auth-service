<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User;
use App\Models\LinkedSocialAccount;
use Illuminate\Support\Facades\Auth; 
use Validator;
use GuzzleHttp\Client;
use Laravel\Socialite\Facades\Socialite;

class UserController extends Controller 
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ], [
            'email.unique' => 'Такой адрес электронной почты уже зарегистрирован в системе.',
            'password.required' => 'Пожалуйста, укажите пароль.',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 401);
        }

        $credentials = ['email' => request('email'), 'password' => request('password')];

        if (Auth::attempt($credentials)) { 
            $user = Auth::user();
            // $user->api_token = $user->createToken('My Token')->accessToken;
            // $user->save();
            
            return response()->json([
                'name' => $user->name,
                'api_key' => $user->api_key,
            ], 200);
        } else {
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
            'name.required' => 'Пожалуйста, укажите имя.',
            'password.required' => 'Пожалуйста, укажите пароль.',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 401);
        }
        
        $input = $request->all();
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => bcrypt($input['password']),
            'api_key' => md5(str_random(30))
        ]);
        // $user->api_token = $user->createToken('My Token')->accessToken; 
        // $user->save();

        return response()->json([
            'api_key' => $user->api_key,
            'name' => $user->name,
        ], 200);
    }

    public function social(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required',
            'access_token' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 401);
        }

        $providerUser = Socialite::driver($request->provider)->userFromToken($request->access_token);
        $linkedSocialAccount = LinkedSocialAccount::where('provider_name', $request->provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        // Если юзер найден, то скорее всего, он осуществил вход...
        if ($linkedSocialAccount) {
            $user = $linkedSocialAccount->user;
        } 
        
        // ... Или регистрацию
        else {
            $email = $providerUser->getEmail() ?: $request->email;

            if ($email) {
                $user = User::where('email', $email)->first();
            }

            if (!$user) {
                $user = User::create([
                    'name' => $providerUser->getName(),
                    'email' => $email,
                    'api_key' => uniqid(base64_encode(str_random(20))),
                ]);
            }

            $user->linkedSocialAccounts()->create([
                'provider_id' => $providerUser->getId(),
                'provider_name' => $request->provider,
            ]);
        }
        
        return response()->json([
            'api_key' => $user->api_key,
            'name' => $user->name,
        ], 200);
    }
}