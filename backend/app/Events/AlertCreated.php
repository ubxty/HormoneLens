<?php

namespace App\Events;

use App\Models\Alert;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Alert $alert,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->alert->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'alert.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->alert->id,
            'type' => $this->alert->type,
            'title' => $this->alert->title,
            'message' => $this->alert->message,
            'severity' => $this->alert->severity,
            'created_at' => $this->alert->created_at?->toISOString(),
        ];
    }
}
