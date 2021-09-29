<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Message implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $message;
    public int    $receiver_id;
    public int    $sender_id;
    public string $user_name;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data,$name)
    {
        $this->message     = $data['message'];
        $this->sender_id   = $data['sender_id'];
        $this->receiver_id = $data['receiver_id'];
        $this->user_name   = $name;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chat.'.$this->receiver_id);
    }
}
