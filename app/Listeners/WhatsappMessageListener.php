<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\ChatbotService;
use App\Services\WhatsappGatewayService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Kstmostofa\LaravelWhatsApp\Events\Web\MessageReceived;

class WhatsappMessageListener
{
    public function __construct(
        private readonly ChatbotService $chatbotService,
        private readonly WhatsappGatewayService $gatewayService
    ) {}

    public function handle(object $event): void
    {
        try {
            if ($event instanceof MessageReceived) {
                if ($event->fromMe()) {
                    Log::debug("[WhatsappMessageListener] Skipped message from self (fromMe=true)");
                    return;
                }

                if ($event->isGroup()) {
                    Log::debug("[WhatsappMessageListener] Skipped message from group");
                    return;
                }

                $rawFrom      = $event->from() ?? '';
                $body         = $event->body() ?? '';
                $msg          = $event->message() ?? [];
                $msgId        = $msg['id'] ?? null;
                $time         = $msg['timestamp'] ?? time();
                $senderNumber = $msg['senderNumber'] ?? $msg['author'] ?? null;
            } else {
                $rawFrom      = (string) (method_exists($event, 'from') ? $event->from() : ($event->from ?? ''));
                $body         = (string) (method_exists($event, 'body') ? $event->body() : ($event->body ?? ''));
                $msgId        = method_exists($event, 'id') ? $event->id() : null;
                $time         = time();
                $senderNumber = null;
            }

            if (empty($rawFrom) || empty($body)) {
                return;
            }

            // Filter out ONLY newsletters, status updates, and broadcasts
            if (
                str_contains($rawFrom, '@newsletter') ||
                str_contains($rawFrom, '@status') ||
                str_contains($rawFrom, '@broadcast')
            ) {
                Log::debug("[WhatsappMessageListener] Ignored non-personal channel: {$rawFrom}");
                return;
            }

            // Deduplication locking: Ensures each unique incoming message is processed ONLY ONCE
            $uniqueKey = 'wa_msg_dedup_' . md5(($msgId ?? '') . '_' . $rawFrom . '_' . $body . '_' . $time);
            if (! Cache::add($uniqueKey, true, 10)) {
                Log::debug("[WhatsappMessageListener] Skipped duplicate message event dispatch: {$uniqueKey}");
                return;
            }

            // Resolve real phone number if WhatsApp sends an LID JID (@lid)
            $resolvedPhone = $this->gatewayService->resolvePhoneNumber($rawFrom);
            if (! $senderNumber && $resolvedPhone) {
                $senderNumber = $resolvedPhone;
            }

            Log::info("[WhatsappMessageListener] Processing inbound message from {$rawFrom} (resolved phone: {$senderNumber}): {$body}");

            $this->chatbotService->process($rawFrom, (string) $body, $senderNumber);
        } catch (\Exception $e) {
            Log::error('[WhatsappMessageListener] Error processing message: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
