<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User; 
use Illuminate\Support\Facades\Auth; 
use Validator;

class UserController extends Controller 
{
    public $successStatus = 200;
    
    public function login()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) { 
            // user credentials are correct. Issue a token and use it in next requests
            $user = Auth::user(); 
            $success['access_token'] =  $user->createToken('My Token')->accessToken; 
            
            return response()->json(['data' => $success], $this->successStatus); 
        } else {
            // invalid credentials, act accordingly
            return response()->json(['message' => 'Пользователь не существует. Пожалуйста, проверьте правильность ввода данных.'], 401); 
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
        
        $success['access_token'] =  $user->createToken('My Token')->accessToken; 
        $success['name'] =  $user->name;
        
        return response()->json(['data' => $success], $this->successStatus); 
    }
    
    public function details() 
    { 
        $user = Auth::user(); 
        
        return response()->json(['success' => $user], $this->successStatus); 
    } 
}