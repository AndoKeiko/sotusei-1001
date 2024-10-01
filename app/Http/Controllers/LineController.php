<?php

namespace App\Http\Controllers;

use App\Jobs\SendLineNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class LineController extends Controller
{
  public function login()
  {
    // LINE ログインのリダイレクト処理
    Log::debug('Login method called');

    // LINE ログイン URL のベース
    $line_login_url = 'https://access.line.me/oauth2/v2.1/authorize?';

    // 環境設定からLINEチャンネルIDを取得
    $client_id = config('services.line.channel_id');

    // リダイレクトURIの生成
    $redirect_uri = route('line.callback');  // ここでルート名が正しいか確認
    if (!$redirect_uri) {
      Log::error('Redirect URI could not be generated.');
      return redirect()->route('login')->withErrors('リダイレクトURLの生成に失敗しました。');
    }

    // CSRFトークンを生成
    $state = Str::random(40);  // セッション管理のためのランダムなトークン
    Log::debug('Generated CSRF State Token: ' . $state);

    // LINE ログインのパラメータを構築
    $query_params = [
      'response_type' => 'code',
      'client_id' => $client_id,
      'redirect_uri' => $redirect_uri,
      'state' => $state,
      'scope' => 'profile openid email',  // 必要なスコープに応じて修正
    ];

    // 完全なLINE ログイン URL を構築
    $line_login_url .= http_build_query($query_params);
    Log::debug('Final LINE Login URL: ' . $line_login_url);

    // セッションにstateを保存する
    session(['line_login_state' => $state]);
    Log::debug('State token saved in session: ' . session('line_login_state'));

    // LINEの認証ページにリダイレクト
    return redirect($line_login_url);
  }


  public function callback(Request $request)
  {
    // LINE ログインのコールバック処理
    $code = $request->input('code');
    $state = $request->input('state');

    // stateの検証（CSRFトークンの代わりにセッションで保存された値を比較）
    if ($state !== session('line_login_state')) {
      return redirect()->route('login')->with('error', 'Invalid state parameter');
    }

    // アクセストークンの取得処理
    $response = Http::post('https://api.line.me/oauth2/v2.1/token', [
      'grant_type' => 'authorization_code',
      'code' => $code,
      'redirect_uri' => route('line.callback'),
      'client_id' => env('LINE_LOGIN_CHANNEL_ID'),
      'client_secret' => env('LINE_LOGIN_CHANNEL_SECRET'),
    ]);

    $responseData = $response->json();

    // アクセストークン取得時のエラーハンドリング
    if (!$response->ok() || !isset($responseData['access_token'])) {
      Log::error('Failed to retrieve access token', ['response' => $responseData]);
      return redirect()->route('login')->with('error', 'Failed to retrieve access token from LINE.');
    }

    $accessToken = $responseData['access_token'];

    // ユーザー情報の取得
    $userInfo = Http::withHeaders([
      'Authorization' => 'Bearer ' . $accessToken
    ])->get('https://api.line.me/v2/profile')->json();

    if (!$userInfo || !isset($userInfo['userId'])) {
      Log::error('Failed to retrieve user info from LINE', ['userInfo' => $userInfo]);
      return redirect()->route('login')->with('error', 'Failed to retrieve user info from LINE.');
    }

    // ユーザーのLINE情報を保存
    $user = auth()->user();
    $user->line_user_id = $userInfo['userId'];
    $user->save();

    // 成功時にgoalsへリダイレクト
    return redirect()->route('goals.index')->with('success', 'LINE account linked successfully.');
  }


  public function scheduleNotification($userId, $message)
  {
      // LINE通知を送信するジョブをディスパッチ
      SendLineNotificationJob::dispatch($userId, $message);
      return response()->json(['success' => true, 'message' => 'Notification queued for sending']);
  }

  public function toggleNotifications(Request $request)
  {
      // ユーザーの通知設定をトグル（オン・オフ切り替え）
      $user = auth()->user();
      $user->notifications_enabled = !$user->notifications_enabled;
      $user->save();

      return response()->json([
          'success' => true,
          'notifications_enabled' => $user->notifications_enabled
      ]);
  }
}
