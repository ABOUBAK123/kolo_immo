<?php

namespace App\Events;

use App\Models\ChatMessage;
use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly ChatMessage   $message,
        public readonly Conversation  $conversation,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversation->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->message->id,
            'body'        => $this->message->body,
            'sender_id'   => $this->message->sender_id,
            'sender_name' => $this->message->sender->name ?? 'Utilisateur',
            'created_at'  => $this->message->created_at->toIso8601String(),
            'attachment'  => $this->message->attachment_path
                ? ['path' => $this->message->attachment_path, 'type' => $this->message->attachment_type]
                : null,
        ];
    }
}
