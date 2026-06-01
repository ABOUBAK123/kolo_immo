<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * List all conversations for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();

        $conversations = Conversation::where('tenant_id', $user->id)
            ->orWhere('owner_id', $user->id)
            ->with([
                'property',
                'tenant',
                'owner',
                'lastMessage',
                'booking',
            ])
            ->orderByDesc('last_message_at')
            ->paginate(15);

        // Add unread count for each conversation
        $conversations->each(function ($conv) use ($user) {
            $conv->unread_count = $conv->unreadCountFor($user);
        });

        return view('messages.index', compact('conversations'));
    }

    /**
     * Show a conversation with all its messages.
     */
    public function show(Conversation $conversation)
    {
        $this->authorizeConversationAccess($conversation);

        $conversation->load([
            'messages.sender',
            'property',
            'tenant',
            'owner',
            'booking',
        ]);

        // Mark all messages from the other party as read
        $user = Auth::user();
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('messages.show', compact('conversation'));
    }

    /**
     * Send a message in a conversation.
     */
    public function send(Request $request, Conversation $conversation)
    {
        $this->authorizeConversationAccess($conversation);

        $request->validate([
            'body'       => ['required_without:attachment', 'nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,pdf,doc,docx', 'max:5120'],
        ], [
            'body.required_without' => 'Le message ne peut pas être vide.',
            'body.max'              => 'Le message ne peut pas dépasser 2000 caractères.',
            'attachment.max'        => 'La pièce jointe ne peut pas dépasser 5 Mo.',
        ]);

        $attachmentPath = null;
        $attachmentType = 'none';

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('messages/attachments', 'public');

            $mimeType = $file->getMimeType();
            if (str_starts_with($mimeType, 'image/')) {
                $attachmentType = 'image';
            } else {
                $attachmentType = 'document';
            }
        }

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => Auth::id(),
            'body'            => $request->body,
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
        ]);

        // Update conversation last_message_at
        $conversation->update(['last_message_at' => now()]);

        // Broadcast to WebSocket channel (no-op with log/null driver, live with Reverb)
        try {
            broadcast(new MessageSent($message->load('sender'), $conversation))->toOthers();
        } catch (\Throwable) {
            // Silent: broadcasting is optional, polling handles real-time fallback
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message_id' => $message->id,
                'message'    => [
                    'id'          => $message->id,
                    'body'        => $message->body,
                    'sender_id'   => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'created_at'  => $message->created_at->toIso8601String(),
                    'attachment'  => $message->attachment_path
                        ? ['path' => asset('storage/' . $message->attachment_path), 'type' => $message->attachment_type]
                        : null,
                ],
            ]);
        }

        return back()->with('success', 'Message envoyé.');
    }

    /**
     * Mark messages in a conversation as read.
     */
    public function markRead(Conversation $conversation)
    {
        $this->authorizeConversationAccess($conversation);

        $conversation->messages()
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    /**
     * Polling endpoint: return messages newer than a given message ID.
     * GET /messages/{conversation}/poll?after={lastId}
     */
    public function poll(Request $request, Conversation $conversation)
    {
        $this->authorizeConversationAccess($conversation);

        $afterId = (int) $request->query('after', 0);
        $user    = Auth::user();

        $messages = $conversation->messages()
            ->where('id', '>', $afterId)
            ->with('sender:id,name')
            ->orderBy('id')
            ->get()
            ->map(fn($m) => [
                'id'          => $m->id,
                'body'        => $m->body,
                'sender_id'   => $m->sender_id,
                'sender_name' => $m->sender->name ?? 'Utilisateur',
                'is_mine'     => $m->sender_id === $user->id,
                'created_at'  => $m->created_at->diffForHumans(),
                'read_at'     => $m->read_at,
                'attachment'  => $m->attachment_path
                    ? ['url' => asset('storage/' . $m->attachment_path), 'type' => $m->attachment_type]
                    : null,
            ]);

        // Mark received messages as read
        if ($messages->isNotEmpty()) {
            $conversation->messages()
                ->whereIn('id', $messages->where('is_mine', false)->pluck('id'))
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return response()->json(['messages' => $messages, 'count' => $messages->count()]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    protected function authorizeConversationAccess(Conversation $conversation): void
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->id !== $conversation->tenant_id && $user->id !== $conversation->owner_id) {
            abort(403, 'Accès non autorisé à cette conversation.');
        }
    }
}
