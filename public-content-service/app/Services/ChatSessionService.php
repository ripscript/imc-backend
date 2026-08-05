<?php

namespace App\Services;

use App\Exceptions\Domain\EntityNotFoundException;
use App\Models\ChatLog;
use App\Models\ChatSession;
use App\Models\ChatSetting;
use Illuminate\Support\Facades\DB;

class ChatSessionService
{
    public function start(int $userId = null): array
    {
        return DB::transaction(function () use ($userId) {
            $session = ChatSession::create([
                'pemohon_id' => $userId,
                'last_activity_at' => now(),
                'started_at' => now(),
            ]);
            $sessionId = $session->uuid;

            $chatBotService = new ChatBotService;
            $kategoriList = $chatBotService->kategoriList();
            $botReply = [
                [
                    'type' => 'message',
                    'message' => ChatSetting::first()->value('opening') ?? 'Halo! Ada yang bisa kami bantu?',
                ],
                ...$kategoriList,
            ];

            ChatLog::create([
                'chat_session_id' => $session->id,
                'sender' => 'bot',
                'message' => json_encode($botReply),
            ]);

            return [
                'session_id' => $sessionId,
                'bot_reply' => $botReply,
            ];
        });
    }

    public function end(string $sessionId): void
    {
        $chat = ChatSession::where(['uuid' => $sessionId, 'is_active' => true])->first();

        if (! $chat) {
            throw new EntityNotFoundException('Session');
        }

        $chat->ended_at = now();
        $chat->is_active = false;
        $chat->save();
    }

    public function sendMessage(string $message, string $sessionId): array
    {
        return DB::transaction(function () use ($message, $sessionId) {
            $session = ChatSession::where(['uuid' => $sessionId, 'is_active' => true])->first();
            if (! $session) {
                throw new EntityNotFoundException('Session');
            }

            ChatLog::create([
                'chat_session_id' => $session->id,
                'sender' => 'user',
                'message' => $message,
            ]);

            $session->update([
                'last_activity_at' => now(),
            ]);

            $chatBotService = new ChatBotService;
            $botReply = $chatBotService->handleMessage($message, $sessionId);

            ChatLog::create([
                'chat_session_id' => $session->id,
                'sender' => 'bot',
                'message' => json_encode($botReply),
            ]);

            if (env('ENABLE_WS_CHAT', false)) {
                event(new \App\Events\ChatMessageEvent($sessionId, 'bot', $botReply));
            }

            return [
                'user_message' => $message,
                'bot_reply' => $botReply,
            ];
        });
    }

    public function history(string $uuid)
    {
        $chatSession = ChatSession::where('uuid', $uuid)->where('is_active', true)->first();
        if (! $chatSession) {
            throw new EntityNotFoundException('Session');
        }

        return ChatLog::where('chat_session_id', $chatSession->id)
            ->select('sender', 'message', 'created_at')
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
