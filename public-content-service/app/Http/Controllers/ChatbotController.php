<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageEvent;
use App\Events\TestWebsocketEvent;
use App\Helpers\ApiResponse;
use App\Http\Requests\SendMessageRequest;
use App\Models\ChatLog;
use App\Models\ChatSession;
use App\Models\ChatSetting;
use App\Models\MsFaq;
use App\Services\ChatBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ChatbotController extends Controller
{
    protected $chatBotService;

    public function __construct()
    {
        $this->chatBotService = new ChatBotService;
    }

    public function startSession(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = auth('pemohon')->user();
            $userId = $user ? $user->id : null;

            $session = ChatSession::create([
                'pemohon_id' => $userId,
                'last_activity_at' => now(),
                'started_at' => now(),
            ]);
            $sessionId = $session->uuid;

            $kategoriList = $this->chatBotService->kategoriList();
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

            DB::commit();

            return ApiResponse::success([
                'session_id' => $sessionId,
                'bot_reply' => $botReply,
            ], 'Sesi chat berhasil dibuat');
        } catch (\Throwable $th) {
            DB::rollBack();

            return ApiResponse::error('Terjadi kesalahan pada server, silakan coba lagi nanti.', $th->getMessage(), 500);
        }
    }

    public function endSession(Request $request)
    {
        DB::beginTransaction();
        try {
            $sessionId = $request->session_id;

            $chat = ChatSession::where(['uuid' => $sessionId, 'is_active' => true])->first();

            if (! $sessionId || ! $chat) {
                return ApiResponse::error('Session tidak ditemukan atau sudah berakhir.', '', 404);
            }
            $chat->ended_at = now();
            $chat->is_active = false;
            $chat->save();

            DB::commit();

            return ApiResponse::success(null, 'Sesi chat berhasil diakhiri');
        } catch (\Throwable $th) {
            DB::rollBack();

            return ApiResponse::error('Terjadi kesalahan pada server, silakan coba lagi nanti.', '', 500);
        }
    }

    public function sendMessageV1(SendMessageRequest $request)
    {
        $userMessage = strtolower(trim($request->message));
        $sessionId = $request->session_id;

        // broadcast(new ChatMessageEvent($userMessage, 'user'));

        // cek apakah user sedang dalam konteks pertanyaan sebelumnya
        $lastContext = Cache::get("chat_context_{$sessionId}");

        // jika sedang menunggu jawaban ya/tidak
        if ($lastContext === 'awaiting_confirmation') {
            if (in_array($userMessage, ['ya', 'iya', 'yes'])) {
                $botReply = 'Terima kasih atas konfirmasinya. Kami senang dapat membantu Anda. 😊';
                Cache::forget("chat_context_{$sessionId}");
            } elseif (in_array($userMessage, ['tidak', 'no', 'nggak'])) {
                $botReply = 'Baik, mohon maaf jika jawaban sebelumnya belum membantu. '
                    .'Untuk mendapatkan bantuan lebih lanjut, silakan menghubungi tim layanan pelanggan kami melalui WhatsApp di tautan berikut: '
                    .'https://wa.me/6281234567890. Tim kami akan dengan senang hati membantu Anda secara langsung.';
                Cache::forget("chat_context_{$sessionId}");
            } else {
                $botReply = "Mohon ketik 'ya' jika jawaban sebelumnya membantu, atau 'tidak' jika belum sesuai.";
            }

            // broadcast(new ChatMessageEvent($botReply, 'bot'));
            return ApiResponse::success([
                'user_message' => $userMessage,
                'bot_reply' => $botReply,
            ], 'Berhasil mengirimkan balasan');
        }

        // cari kecocokan dengan FAQ
        $faqs = MsFaq::where('is_active', true)->get();

        $faq = $faqs->first(function ($faq) use ($userMessage) {
            similar_text(strtolower($faq->title), strtolower($userMessage), $percent);

            return $percent >= 70;
        });

        if ($faq) {
            $botReply = $faq->description."\n\nApakah jawaban ini membantu Anda? (ya/tidak)";
            Cache::put("chat_context_{$sessionId}", 'awaiting_confirmation', now()->addMinutes(5));
        } else {
            $botReply = 'Mohon maaf, untuk saat ini saya belum dapat menemukan jawaban yang sesuai dengan pertanyaan Anda. '
                ."\nUntuk mendapatkan bantuan lebih lanjut, silakan menghubungi tim layanan pelanggan kami melalui WhatsApp di tautan berikut: "
                ."https://wa.me/6281234567890. \nTim kami akan dengan senang hati membantu Anda secara langsung.";
        }

        // broadcast(new ChatMessageEvent($botReply, 'bot'));

        return ApiResponse::success([
            'user_message' => $userMessage,
            'bot_reply' => $botReply,
        ], 'Berhasil mengirimkan balasan');
    }

    public function sendMessageV2(SendMessageRequest $request)
    {
        DB::beginTransaction();
        try {

            $session = ChatSession::where(['uuid' => $request->session_id, 'is_active' => true])->first();
            if (! $session) {
                return ApiResponse::error('Session tidak ditemukan atau sudah berakhir.', '', 404);
            }

            ChatLog::create([
                'chat_session_id' => $session->id,
                'sender' => 'user',
                'message' => $request->message,
            ]);

            $session->update([
                'last_activity_at' => now(),
            ]);

            $botReply = $this->chatBotService->handleMessage(
                $request->message,
                $request->session_id
            );

            ChatLog::create([
                'chat_session_id' => $session->id,
                'sender' => 'bot',
                'message' => json_encode($botReply),
            ]);

            if (env('ENABLE_WS_CHAT', false)) {
                event(new ChatMessageEvent($request->session_id, 'bot', $botReply));
            }

            DB::commit();

            return ApiResponse::success([
                'user_message' => $request->message,
                'bot_reply' => $botReply,
            ], 'Berhasil mengirimkan balasan');
        } catch (\Throwable $th) {
            DB::rollBack();

            return ApiResponse::error('Terjadi kesalahan pada server, silakan coba lagi nanti.', $th->getMessage(), 500);
        }
    }

    public function history($uuid)
    {
        $chatSession = ChatSession::where('uuid', $uuid)->where('is_active', true)->first();
        if (! $chatSession) {
            return ApiResponse::error('Session tidak ditemukan atau sudah berakhir.', '', 404);
        }
        $log = ChatLog::where('chat_session_id', $chatSession->id)
            ->select('sender', 'message', 'created_at')
            ->orderBy('created_at', 'asc')
            ->get();

        return ApiResponse::success($log, 'Berhasil mengambil histori chat');
    }

    public function test(Request $request)
    {
        $message = $request->get('message', 'Hello from Laravel WebSocket!');

        event(new TestWebsocketEvent($message));

        return response()->json([
            'success' => true,
            'message' => 'Event broadcasted!',
            'data' => $message,
        ]);
    }
}
