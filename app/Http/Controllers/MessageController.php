<?php

namespace App\Http\Controllers;

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

        if ($request->expectsJson()) {
            $message->load('sender');
            return response()->json([
                'success' => true,
                'message' => $message,
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
