<?php

namespace Database\Factories;

use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatMessageFactory extends Factory
{
    protected $model = ChatMessage::class;

    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_id'       => User::factory(),
            'body'            => fake()->sentence(),
            'attachment_path' => null,
            'attachment_type' => 'none',
            'read_at'         => fake()->boolean(60) ? fake()->dateTimeBetween('-1 week', 'now') : null,
        ];
    }

    public function unread(): static
    {
        return $this->state(['read_at' => null]);
    }

    public function withImage(): static
    {
        return $this->state([
            'attachment_path' => 'messages/attachments/test-image.jpg',
            'attachment_type' => 'image',
        ]);
    }

    public function inConversation(Conversation $conv, User $sender): static
    {
        return $this->state([
            'conversation_id' => $conv->id,
            'sender_id'       => $sender->id,
        ]);
    }
}
