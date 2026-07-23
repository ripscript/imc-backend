<?php

namespace App\Services;

use App\Models\TemplateEmail;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailTemplateService
{
    public function renderTemplate($templateContent, $data = []): string
    {
        try {
            preg_match_all('/{{(.*?)}}/', $templateContent, $matches);

            if (! empty($matches[1])) {
                foreach ($matches[1] as $rawVar) {
                    $key = trim($rawVar);
                    $value = data_get($data, $key, '');
                    $templateContent = str_replace('{{'.$key.'}}', e($value), $templateContent);
                }
            }

            $templateContent = $this->formatEmailHtml($templateContent);

            return $templateContent;
        } catch (Exception $e) {
            Log::error('Gagal render template email: '.$e->getMessage());

            return $templateContent;
        }
    }

    public function formatEmailHtml($html)
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument('1.0', 'UTF-8');

        $dom->loadHTML(
            mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $xpath = new \DOMXPath($dom);

        /** @var \DOMElement $img */
        foreach ($xpath->query('//img') as $img) {

            if (! ($img instanceof \DOMElement)) {
                continue;
            }

            $styles = [];

            $style = trim($img->getAttribute('style'));

            if ($style !== '') {
                foreach (explode(';', $style) as $item) {
                    $item = trim($item);

                    if ($item === '') {
                        continue;
                    }

                    [$property] = array_pad(explode(':', $item, 2), 2, null);

                    $property = strtolower(trim($property));

                    // Hapus property yang kurang didukung email
                    if (in_array($property, [
                        'aspect-ratio',
                        'height',
                        'max-height',
                    ])) {
                        continue;
                    }

                    $styles[$property] = $item;
                }
            }

            // Tambahkan style email-friendly
            $styles['height'] = 'height:auto';
            $styles['max-width'] = 'max-width:100%';
            $styles['border'] = 'border:0';
            $styles['outline'] = 'outline:none';
            $styles['text-decoration'] = 'text-decoration:none';

            $parent = $img->parentNode;

            if (
                $parent instanceof \DOMElement &&
                strtolower($parent->tagName) === 'p' &&
                str_contains(
                    strtolower($parent->getAttribute('style')),
                    'text-align:center'
                )
            ) {
                $styles['display'] = 'display:inline-block';
            } else {
                $styles['display'] = 'display:block';
            }

            $img->setAttribute(
                'style',
                implode(';', $styles).';'
            );

            // Hapus class bawaan CKEditor
            $img->removeAttribute('class');
        }

        libxml_clear_errors();

        return $dom->saveHTML();
    }

    public function sendTemplate($templateName, $to, $data = [], $subjectOverride = null): bool
    {
        try {
            $template = TemplateEmail::where('name', $templateName)->first();
            if (! $template) {
                Log::warning("Template email dengan name '{$templateName}' tidak ditemukan.");

                return false;
            }

            $header = TemplateEmail::where('name', 'Header/Kop Email')->first();
            $sender = TemplateEmail::where('name', 'Pengirim')->first();

            $renderedMain = $this->renderTemplate($template->content, $data);

            $finalContent = '';
            if ($header) {
                $renderedHeader = $this->renderTemplate($header->content, $data);
                $finalContent = $renderedHeader."\n".$renderedMain;
            } else {
                $finalContent = $renderedMain;
            }

            $subject = $template->subject ?: ($subjectOverride ?: 'Tanpa Subjek');

            $fromName = $sender ? strip_tags($this->renderTemplate($sender->content, $data)) : config('mail.from.name');
            $fromEmail = config('mail.from.address');

            Mail::html($finalContent, function ($message) use ($to, $subject, $fromEmail, $fromName) {
                $message->to($to)
                    ->subject($subject)
                    ->from($fromEmail, $fromName)
                    ->replyTo($fromEmail);

                $message->getHeaders()->addTextHeader('X-Mailer', 'Kemlu-SMTP');
                $message->getHeaders()->addTextHeader('X-Environment', app()->environment());

                // Log header yang dikirim
                Log::info('[MAIL] Email headers attached', [
                    'headers' => [
                        'From' => "{$fromName} <{$fromEmail}>",
                        'To' => $to,
                        'Subject' => $subject,
                        'Reply-To' => $fromEmail,
                        'X-Mailer' => 'Kemlu-SMTP',
                        'X-Environment' => app()->environment(),
                    ],
                ]);
            });

            return true;
        } catch (Exception $e) {
            Log::error('Gagal kirim email: '.$e->getMessage());

            return false;
        }
    }
}
