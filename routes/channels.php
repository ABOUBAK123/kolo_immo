<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

Broadcast::channel('conversation.{conversationId}', function ($user, int $conversationId) {
    $conversation = Conversation::find($conversationId);
    if (!$conversation) return false;

    // Only tenant and owner of the conversation may listen
    return $user->id === $conversation->tenant_id
        || $user->id === $conversation->owner_id;
});
