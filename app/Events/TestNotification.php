<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class TestNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    /**
     * Create a new event instance.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('notification'),
        ];
    }

    /**
     * The channel the event should broadcast on.
     *
     * @return Channel
     */
    // public function broadcastOn(): PresenceChannel
    // {
        
    //     return new PresenceChannel('notification');
    // }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    // public function broadcastAs()
    // {
    //     return 'test.notification';
    // }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    // public function broadcastWith(): array
    // {
    //     return $this->data;
    // }
}

