<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Message;
use App\Models\Participant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;


// class MessageCreated implements ShouldBroadcast
class MessageCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     * 
     *  @param \App\Models\Message $message
     * 
     * @return void
     */
    public $message;

    public function __construct(Message $message)
    {
        $this->message= $message->fresh();

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {   
        $other_user= $this->message->conversation->participants()
        ->where('user_id', '<>', $this->message->user_id)
        ->first();
        return new PresenceChannel('Messenger.' . $other_user->id);
    }

    public function broadcastAs()
    {
        return 'new-message';
    }

    
}
