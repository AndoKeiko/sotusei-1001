<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class LineWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $input = $request->all();
        Log::info('LINE Webhook Received:', $input);

        if (!empty($input['events'])) {
            foreach ($input['events'] as $event) {
                if ($event['type'] === 'message') {
                    $messageType = $event['message']['type'];
                    if ($messageType === 'text') {
                        $messageText = $event['message']['text'];
                        $replyToken = $event['replyToken'];

                        Log::info('Message received: ' . $messageText);

                        // メッセージに応じて返信
                        $this->replyMessage($replyToken, $messageText);
                    }
                }
            }
        }

        return response()->json(['message' => 'Webhook received'], 200);
    }

    private function replyMessage($replyToken, $messageText)
    {
        $replyText = 'デフォルトの返信';

        // キーワードによって返信を変更
        if (strpos($messageText, 'テスト') !== false) {
            $replyText = 'これはテストメッセージです。';
        } elseif (strpos($messageText, '出来る') !== false) {
            $replyText = 'はい、出来ますよ。';
        } elseif (strpos($messageText, 'うまく') !== false) {
            $replyText = 'うまくいって良かったです！';
        }

        // LINEにメッセージを返信
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('LINE_CHANNEL_ACCESS_TOKEN'),
        ])->post('https://api.line.me/v2/bot/message/reply', [
            'replyToken' => $replyToken,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $replyText,
                ],
            ],
        ]);

        Log::info('Reply sent: ' . $response->body());
    }
}
