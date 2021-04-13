<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use FFI\Exception;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class User extends Model
{
    use HasFactory, Notifiable;

    const TOKEN_LENGTH = 64; //100 max db field length
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'token',
    ];
    /**
     * create user in DB
     * @param  array $input
     * @return  Response
     */
    public static function register(array $input): Response
    {

        $input['password'] = Hash::make($input['password']);
        try { //try create in DB user entety
            $user = User::create($input);
        } catch (Exception $ex) {
            return response(['error' => $ex->getMessage()], 500);
        } catch (QueryException $ex) {
            Log::error("got Exception on user register: " . $ex->getMessage());
            return response(['error' => 'internal error'], 500);
        }
        if (false != $user) { //user was created
            return response(['result' => 'register successully'], 201);
        } else {
            return response(['error' => 'failed to create student'], 500);
        }
    }
    /**
     * login user by email angd password and create token in DB
     *  @return  Response with user token
     */
    public static function login(array $input): Response
    {
        try { //get user by email
            $user = User::where('email', $input['email'])->firstOrFail();
        } catch (ModelNotFoundException $ex) {
            Log::debug($input['email'] . "not found");
            return response(['error' => 'user not exist or wrong password'], 404);
        } catch (Exception $ex) {
            Log::error("got Exception on user login: " . $ex->getMessage());
            return response(['error' => 'internal error'], 500);
        } catch (QueryException $ex) {
            Log::error("got Exception on user login: " . $ex->getMessage());
            return response(['error' => 'internal error'], 500);
        }

        if (Hash::check($input['password'], $user->password)) // The passwords match...
        {
            Log::debug($input['email'] . " Login succefully");
            if ($user->token == null) { //create new token
                $user->token = bin2hex(openssl_random_pseudo_bytes(self::TOKEN_LENGTH / 2));
                try {
                    $user->save(); //update DB
                } catch (QueryException $ex) {
                    return response(['error' => $ex->getMessage()], 500);
                }
            }
            return response(['token' => $user->token]);
        } else {
            Log::debug($input['email'] . " not match password");
            return response(['error' => 'email not exist or wrong password'], 404);
        }
    }
    /**
     * get user records from DB.
     *
     * @param  array  $input
     * @return Response with user records
     *
     */
    public static function read(array $input): Response
    {
        try { //get user by email
            $user = self::where('email', $input['email'])->where('token', $input['token'])->firstOrFail();
        } catch (ModelNotFoundException $ex) {
            Log::debug($input['email'] . "not match token");
            return response(['error' => 'user not exist or wrong token'], 404);
        } catch (Exception $ex) {
            Log::error("got Exception on user read: " . $ex->getMessage());
            return response(['error' => 'internal error'], 500);
        } catch (QueryException $ex) {
            Log::error("got Exception on user register: " . $ex->getMessage());
            return response(['error' => 'internal error'], 500);
        }
        return response($user);
    }
    /**
     * get user name from DB
     * @param  array  $input email and token
     * @return Response with user name
     */
    public static function getUserName(array $input): Response
    {
        $response = self::read($input);
        if ($response->getStatusCode() == 200) {
            $name = json_decode($response->getContent(), true)['name'];
            $response->setContent(['name' => $name]);
        }
        return $response;
    }
}
