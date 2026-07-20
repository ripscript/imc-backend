<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Models\ChatSetting;
use App\Models\MsKategoriFaq;
use App\Models\MsFaq;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class ChatBotService
{
    public function handleMessage($message, $sessionId)
    {
        $userMessage = strtolower(trim($message));

        $settings = ChatSetting::first();
        $settings->wa_number = SiteSetting::first()->value('wa_number') ?? "";

        $botReply = [];
        if (Str::startsWith($userMessage, 'kategori_faq_')) {
            $botReply = $this->handleKategori($userMessage, $settings);
        } else if (Str::startsWith($userMessage, 'faq_')) {
            $botReply = $this->handleFaq($userMessage, $settings);
        } else if (Str::startsWith($userMessage, 'confirmation_')) {
            $botReply = $this->handleConfirmation($userMessage, $settings);
        } else if (Str::startsWith($userMessage, 'another_question_')) {
            $botReply = $this->handleAnotherQuestion($userMessage, $settings, $sessionId);
        } else {
            $botReply = [[
                'type' => 'message',
                'message' => 'Mohon maaf, pesan tidak dikenali.',
            ]];
        }

        return $botReply;
    }

    private function handleKategori($message, $settings)
    {
        $uuid = Str::after($message, 'kategori_faq_');

        if ($uuid === '0') {
            return [
                ...$this->toWhatsApp($settings),
                ...$this->askAnotherQuestion($settings),
            ];
        }

        $kategori = MsKategoriFaq::where('uuid', $uuid)->first();
        if (!$kategori) {
            return [[
                'type' => 'message',
                'message' => 'Kategori yang dipilih tidak valid.',
            ]];
        }

        $faq = MsFaq::where('kategori_faq_id', $kategori->id)
            ->where('is_active', true)
            ->select('uuid', 'title')
            ->get();

        $faq->push([
            'uuid' => '0',
            'title' => 'Lainnya',
        ]);

        return [
            [
                'type' => 'message',
                'message' => 'Silakan pilih pertanyaan yang tersedia pada kategori '.$kategori->name.' berikut:',
            ],
            [
                'type' => 'option',
                'category' => 'faq',
                'message' => $faq,
            ]
        ];
    }

    private function handleFaq($message, $settings)
    {
        $uuid = Str::after($message, 'faq_');

        if ($uuid === '0') {
            return [
                ...$this->toWhatsApp($settings),
                ...$this->askAnotherQuestion($settings)
            ];
        }

        $faq = MsFaq::where('uuid', $uuid)->where('is_active', true)->first();
        if (!$faq) {
            return [[
                'type' => 'message',
                'message' => 'Pertanyaan yang dipilih tidak valid.',
            ]];
        }

        return [
            [
                'type' => 'message',
                'message' => $faq->description,
            ],
            ...$this->confirmation($settings)
        ];
    }

    private function handleConfirmation($message, $settings)
    {
        $uuid = Str::after($message, 'confirmation_');

        if ($uuid == '1') {
            return [
                ...$this->askAnotherQuestion($settings)
            ];
        } else if ($uuid == '2') {
            return [
                ...$this->toWhatsApp($settings),
                ...$this->askAnotherQuestion($settings),
            ];
        } else {
            return [[
                'type' => 'message',
                'message' => 'Pilihan tidak valid.',
            ]];
        }
    }

    private function handleAnotherQuestion($message, $settings, $sessionId)
    {
        $uuid = Str::after($message, 'another_question_');

        if ($uuid == '1') {
            return [
                ...$this->kategoriList()
            ];
        } else if ($uuid == '2') {
            return [[
                'type' => 'message',
                'message' => $settings['closing'] ?? 'Terima kasih telah menghubungi kami!',
            ]];
        } else {
            return [[
                'type' => 'message',
                'message' => 'Pilihan tidak valid.',
            ]];
        }
    }

    private function toWhatsApp($settings)
    {
        return [
            [
                'type' => 'message',
                'message' => $settings['to_whatsapp'] ?? 'Silakan hubungi WhatsApp kami.',
            ],
            [
                'type' => 'option',
                'category' => 'wa_number',
                'message' => [['uuid' => 'wa_number', 'title' => $settings['wa_number']]],
            ],
        ];
    }

    private function askAnotherQuestion($settings)
    {
        return [
            [
                'type' => 'message',
                'message' => $settings['another_question'] ?? 'Apakah Anda ingin bertanya lagi?',
            ],
            [
                'type' => 'option',
                'category' => 'another_question',
                'message' => [
                    ['uuid' => '1', 'title' => 'Ya'],
                    ['uuid' => '2', 'title' => 'Tidak'],
                ],
            ],
        ];
    }

    private function confirmation($settings)
    {
        return [
            [
                'type' => 'message',
                'message' => $settings['confirmation'] ?? 'Apakah jawaban ini membantu?',
            ],
            [
                'type' => 'option',
                'category' => 'confirmation',
                'message' => [
                    ['uuid' => '1', 'title' => 'Ya'],
                    ['uuid' => '2', 'title' => 'Tidak'],
                ],
            ],
        ];
    }

    public function kategoriList()
    {
        $list = MsKategoriFaq::whereHas('faqActive')->select('uuid', 'name as title')->get();
        $list->push(['uuid' => '0', 'title' => 'Lainnya']);
        return [
            [
                'type' => 'message',
                'message' => 'Pilih kategori informasi yang sesuai dengan kebutuhan Anda:',
            ],
            [
                'type' => 'option',
                'category' => 'kategori_faq',
                'message' => $list,
            ],
        ];
    }
}
