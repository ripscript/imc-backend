<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\EndSessionRequest;
use App\Http\Requests\SendMessageRequest;
use App\Services\ChatBotService;
use App\Services\ChatSessionService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(
        protected ChatSessionService $chatSessionService
    ) {}

    public function startSession(Request $request)
    {
        $user = auth('pemohon')->user();
        $userId = $user ? $user->id : null;

        $result = $this->chatSessionService->start($userId);

        return ApiResponse::success($result, 'Sesi chat berhasil dibuat');
    }

    public function endSession(EndSessionRequest $request)
    {
        $this->chatSessionService->end($request->session_id);

        return ApiResponse::success(null, 'Sesi chat berhasil diakhiri');
    }

    public function sendMessageV1(SendMessageRequest $request)
    {
        $chatBotService = new ChatBotService;

        $userMessage = strtolower(trim($request->message));
        $sessionId = $request->session_id;

        $lastContext = cache()->get("chat_context_{$sessionId}");

        if ($lastContext === 'awaiting_confirmation') {
            if (in_array($userMessage, ['ya', 'iya', 'yes'])) {
                $botReply = 'Terima kasih atas konfirmasinya. Kami senang dapat membantu Anda. 😊';
                cache()->forget("chat_context_{$sessionId}");
            } elseif (in_array($userMessage, ['tidak', 'no', 'nggak'])) {
                $botReply = 'Baik, mohon maaf jika jawaban sebelumnya belum membantu. '
                    .'Untuk mendapatkan bantuan lebih lanjut, silakan menghubungi tim layanan pelanggan kami melalui WhatsApp di tautan berikut: '
                    .'https://wa.me/6281234567890. Tim kami akan dengan senang hati membantu Anda secara langsung.';
                cache()->forget("chat_context_{$sessionId}");
            } else {
                $botReply = "Mohon ketik 'ya' jika jawaban sebelumnya membantu, atau 'tidak' jika belum sesuai.";
            }

            return ApiResponse::success([
                'user_message' => $userMessage,
                'bot_reply' => $botReply,
            ], 'Berhasil mengirimkan balasan');
        }

        $faqs = \App\Models\MsFaq::where('is_active', true)->get();

        $faq = $faqs->first(function ($faq) use ($userMessage) {
            similar_text(strtolower($faq->title), strtolower($userMessage), $percent);

            return $percent >= 70;
        });

        if ($faq) {
            $botReply = $faq->description."\n\nApakah jawaban ini membantu Anda? (ya/tidak)";
            cache()->put("chat_context_{$sessionId}", 'awaiting_confirmation', now()->addMinutes(5));
        } else {
            $botReply = 'Mohon maaf, untuk saat ini saya belum dapat menemukan jawaban yang sesuai dengan pertanyaan Anda. '
                ."\nUntuk mendapatkan bantuan lebih lanjut, silakan menghubungi tim layanan pelanggan kami melalui WhatsApp di tautan berikut: "
                ."https://wa.me/6281234567890. \nTim kami akan dengan senang hati membantu Anda secara langsung.";
        }

        return ApiResponse::success([
            'user_message' => $userMessage,
            'bot_reply' => $botReply,
        ], 'Berhasil mengirimkan balasan');
    }

    public function sendMessageV2(SendMessageRequest $request)
    {
        $result = $this->chatSessionService->sendMessage(
            $request->message,
            $request->session_id
        );

        return ApiResponse::success($result, 'Berhasil mengirimkan balasan');
    }

    public function history($uuid)
    {
        $log = $this->chatSessionService->history($uuid);

        return ApiResponse::success($log, 'Berhasil mengambil histori chat');
    }

    public function test(Request $request)
    {
        $message = $request->get('message', 'Hello from Laravel WebSocket!');

        event(new \App\Events\TestWebsocketEvent($message));

        return ApiResponse::success($message, 'Event broadcasted!');
    }
}
