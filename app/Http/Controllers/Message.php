<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

use Log;
use Illuminate\Http\Request;
use \Str;

class Message extends Controller
{
    /**
     * Returns a message.
     */
    public function get( $id ) {
        // return ['username' => 'hello'];
    }

    /**
     * Return all message stored in database with said username in from or to.
     */
    public function get_all( $username )
    {
        //$db_messages = DB::table('messages')->get();
        $db_messages = \App\Message::where('from', $username)->orWhere('to', $username)->first();
        
        // Only returns some fields
        $messages = [];
        foreach ($db_messages as $message) {
            $messages[] = [
                'id' => $message->id,
                'from' => $message->from,
                'to' => $message->to,
                'text' => $message->text
            ];
        }
        return [
            "status" => "ok",
            "messages" => $messages
        ];
    }

    /**
     * Create a new message.
     */
    public function create( Request $request )
    {
        // Automatically decode json input, depending on the content-type
        $to = $request->input('to');
        $from = $request->input('from');
        $text = $request->input('text');

        if (empty( $to ) ) {
            return response()
                ->json( ["status" => "error", 'error' => '"username To" field is empty'], 500 );
        }
        if (empty( $from ) ) {
            return response()
                ->json( ["status" => "error", 'error' => '"username From" field is empty'], 500 );
        };
        if (empty( $text ) ) {
            return response()
                ->json( ["status" => "error", 'error' => '"text" field is empty'], 500 );
        };
  
        $message_to_insert = [
            'to' => $to,
            'from' => $from,
            'text' => $text
        ];
        
        $message_id = null;
        try {
            $message_id = DB::table('message')->insertGetId(
                    $message_to_insert
                );
            } catch (\Illuminate\Database\QuerusernameusernameusernameyException $e) {
                return response()
                    ->json(
                    [
                        'status' => 'error',
                        'error' =>'Unable to create the message',
                    ]
                );
        }

        $message_to_insert['id'] = $message_id;
        return response()
            ->json(
                ['status' => 'ok'],
                200
            );
    }
}
