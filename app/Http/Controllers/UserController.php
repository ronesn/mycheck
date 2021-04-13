<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\User;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
     /**
     * Register API create user entity
     * 
     * @param  Request  $request
     * @return \Illuminate\Http\Response 
     */
    public function register(Request $request) {
        
        $input = $request->all();
        $validator = Validator::make(
        $request->all(), [
            'name' => 'required',
            'email' => 'required|email:rfc,dns|unique:users',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        return User::register($input);
    }

    /**
     * login API 
     * @param  Request  $request email and password
     * @return \Illuminate\Http\Response with token
     */
    public function login(Request $request) {
        $input = $request->all();
        $validator = Validator::make(
        $request->all(), [
            'email' => 'required|email:rfc,dns',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        return User::login($input);
    }
    /**
     * read API get user name
     * 
     * @param  Request  $request email and token
     * @return \Illuminate\Http\Response with user name
     */
    public function read(Request $request) {
        
        $input = $request->all();
        $validator = Validator::make(
        $request->all(), [
            'email' => 'required|email:rfc,dns',
            'token' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        return User::getUserName($input);
    }

}