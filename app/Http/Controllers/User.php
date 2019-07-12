<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

use Log;
use Illuminate\Http\Request;
use \Str;

class User extends Controller
{

    /**
     * Return all users stored in database.
     */
    public function get_all()
    {
        $db_users = DB::table('users')->get();
        
        // Only returns some fields
        $users = [];
        foreach ($db_users as $user) {
            $users[] = [
                'id' => $user->id,
                'username' => $user->username
            ];
        }
        return [
            "status" => "ok",
            "users" => $users
        ];
    }

    /**
     * Create a new user.
     */
    public function create( Request $request )
    {

        // Automatically decode json input, depending on the content-type
        $username = strtolower($request->input('username'));
        $password = $request->input('password');

        if (empty( $username ) ) {
            return response()
                ->json( ["status" => "error", 'error' => '"username" field is empty'], 500 );
        }
        if (empty( $password ) ) {
            return response()
                ->json( ["status" => "error", 'error' => '"password" field is empty'], 500 );
        };
  
        // Check for user existence in database
        $user = \App\User::where('username', $username)->first();
        if ($user) {
            return response()
                ->json([
                    'status' => 'error',
                    'error' => 'username field already exists'
                ]);
        }
        $user_to_insert = [
            'username' => $username,
            'password' => $password,
        ];
        
        $user_id = null;
        try {
            $user_id = DB::table('users')->insertGetId(
                    $user_to_insert
                );
            } catch (\Illuminate\Database\QuerusernameusernameusernameyException $e) {
                return response()
                    ->json(
                    [
                        'status' => 'error',
                        'error' =>'Unable to create the user',
                    ]
                );
        }

        $user_to_insert['id'] = $user_id;
        return response()
            ->json(
                ['status' => 'ok'],
                200
            );
    }

    public function updateUser( $id, Request $request ) {
        // TODO: check if logged in user is the same as $email

        $user = \App\User::where('id', $id)->first();
        if (!$user) {
            return [
                "status" => "error", 
                "error" => "User $id not found"
            ];
        }

        // Check if the logged in user is the same as the one we want to modify
        $api_token = \DB::table('api_tokens')
            ->where('value', $request->header('api_token'))
            ->get();
        
        // Check if token belongs to the user with the specified id
        if (count( $api_token ) == 0 || $api_token[0]->user_id != $id ) {
            Log::debug('user id and token user id same');
            return response()
                ->json([
                    'status' => 'error',
                    'error' => 'You are not allowed to modify another user'
                ]);
        }

        if ($request->input('username') ) {
            Log::debug("Updating ".$request->input('username'));
            $user->username = $request->input('username');
        }

        if ($request->input('password') ) {
            Log::debug("Updating ".$request->input('password'));
            $user->password = $request->input('password');
        }

        $user->save();
        return ["status" => "ok", "message" => "Updated user $id"];
    }

    public function login(Request $request) {
        $username = strtolower($request->input('username'));
        $password = $request->input('password');

        $user = NULL;
        if ($username) {
            $user = \App\User::where('username', $username)
                ->where('password', $password)
                ->first();
        } else {
            return ["status" => "error", 'error' => 'Please specify an username.'];
        }
        
        if ($user) {
            // There is a user, login ok
            // Create a token
            $expiration = new \DateTime();
            $expiration->add( new \DateInterval('P1D') );
            $token_value = Str::random(60);

            DB::table('api_tokens')->insert([
                "user_id" => $user->id,
                "expires" => $expiration,
                "value" => $token_value
            ]);

            // Return some user informations, and the token
            $result = [
                "id" => $user->id,
                "username" => $user->username,
                "api_token" => $token_value
            ];
        } else {
            $result = ["status" => "error", 'error' => 'Unable to login, wrong username/password'];
        }

        return $result;
    }

    public function deleteUser( $id, Request $request ) {
        $user = \App\User::where('id', $id)->first();

        // Check if the logged in user is the same as the one we want to modify
        if ($request->header('api_token') !== $user->api_token) {
            return response()
                ->json([
                    'status' => 'error',
                    'error' => 'You are not allowed to delete another user'
                ]);
        }

        // TODO: check if logged in user is the same as $username
        if ($user) {
            $user->delete();
            return ["status" => "ok", 'message' => "User $username deleted"];
        } else {
            return ["status" => "error", 'error' => "Cannot delete user $username"];
        }
    }
}
