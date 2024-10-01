<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class LineWebhookController extends Controller
{
  public function handleWebhook(Request $request)
  {
    // Webhookからのデータをすべて取得
    $input = $request->all();
    Log::info('LINE Webhook Received:', $input);

    // イベントが空でない場合に処理
    if (!empty($input['events'])) {
      foreach ($input['events'] as $event) {
        // メッセージイベントかどうかを確認
        if ($event['type'] === 'message') {
          $messageType = $event['message']['type'];

          // メッセージがテキストの場合
          if ($messageType === 'text') {
            $messageText = $event['message']['text']; // 送信されたメッセージ内容
            $replyToken = $event['replyToken']; // 返信用トークン

            Log::info('Message received: ' . $messageText);

            // メッセージに応じた返信
            $this->replyMessage($replyToken, 'あなたのメッセージ: ' . $messageText);
          }
        }
      }
    }

    // ステータスコード200を返すことで、LINEプラットフォームに正常な受信を伝える
    return response()->json(['message' => 'Webhook received'], 200);
  }

  // 返信メッセージを送信するメソッド
  private function replyMessage($replyToken, $messageText)
{
    $replyText = 'デフォルトの返信';

    // キーワードによって返信内容を変更
    if (strpos($messageText, 'テスト') !== false) {
        $replyText = 'これはテストメッセージです。';
    } elseif (strpos($messageText, '出来る') !== false) {
        $replyText = 'はい、出来ますよ。';
    } elseif (strpos($messageText, 'うまく') !== false) {
        $replyText = 'うまくいって良かったです！';
    }

    // メッセージを返信
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
