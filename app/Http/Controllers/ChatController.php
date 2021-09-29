<?php

namespace App\Http\Controllers;

use App\Events\Message;
use App\Models\Messages;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all()->except(Auth::id());
        return view('chat.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data=[
            'message' => $request->msg,
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
        ];

        Messages::create($data);

        event(new Message(
                        $data['message'],
                        $data['receiver_id'],
                        $data['sender_id'],
                        Auth::user()->name
                    ));

        return response()->json();
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($receiver_id)
    {
        $messages_user = Messages::with(['users' => function ($q) {
                $q->selection();
            }])
            ->where(function ($q) use ($receiver_id) {
                $q->auth_receiver()->where('sender_id', $receiver_id);
            })
            ->orWhere(function ($q) use ($receiver_id) {
                $q->Where('receiver_id', $receiver_id)->auth_sender();
            })
            ->latest()->limit(3)->get();

        if (count($messages_user) == 0) {
            return response()->json(['error' => 'messages not found'], 404);
        }

        return response()->json(['messages' => $messages_user]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $receiver_id)
    {
        $messages_user = Messages::with(['users' => function ($q) {
                $q->selection();
            }])
            ->where(function ($q) use ($receiver_id, $request) {
                $q->auth_receiver()->where('sender_id', $receiver_id)
                    ->where('id', '<', $request->first_msg_id);
            })
            ->orWhere(function ($q) use ($receiver_id, $request) {
                $q->Where('receiver_id', $receiver_id)->auth_sender()
                    ->where('id', '<', $request->first_msg_id);
            })
            ->latest()->limit(3)->get();

        if (count($messages_user) == 0) {
            return response()->json(['error' => 'messages not found'], 404);
        }

        return response()->json(['messages' => $messages_user]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
