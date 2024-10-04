<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Jobs\SendLineNotificationJob; // LINE通知用のジョブ
use App\Notifications\TaskReminder; // メール通知


class LineController extends Controller
{
  // LINEログインのリダイレクト処理
  public function redirectToLine()
  {
    $state = Str::random(40); // LaravelのStrヘルパーを使用
    session(['line_login_state' => $state]); // セッションに保存
    Log::info('Session state: ' . session('line_login_state'));

    // LINEの認証URLにリダイレクト
    $lineLoginUrl = "https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=" . env('LINE_LOGIN_CHANNEL_ID') .
      "&redirect_uri=" . urlencode(env('LINE_REDIRECT_URI')) . "&state=" . $state . "&scope=profile%20openid";

    return redirect($lineLoginUrl);
  }

  // LINEログインのコールバック処理
  public function handleLineCallback(Request $request)
  {
    $stateFromSession = session('line_login_state'); // セッションからstateを取得
    $stateFromRequest = $request->input('state'); // リクエストからstateを取得
    Log::info('Request state: ' . $request->input('state'));

    // stateが一致しない場合のエラーハンドリング
    if ($stateFromSession !== $stateFromRequest) {
      Log::error('State mismatch. Possible CSRF attack.');
      return redirect()->route('login')->with('error', 'Invalid state parameter.');
    }

    $code = $request->input('code');

    // アクセストークンを取得するためにLINEにリクエスト
    $response = Http::asForm()->post('https://api.line.me/oauth2/v2.1/token', [
      'grant_type' => 'authorization_code',
      'code' => $code,
      'redirect_uri' => env('LINE_REDIRECT_URI'),
      'client_id' => env('LINE_LOGIN_CHANNEL_ID'),
      'client_secret' => env('LINE_LOGIN_CHANNEL_SECRET'),
    ]);

    if ($response->failed()) {
      Log::error('LINE認証失敗: ' . $response->body());
      return redirect()->route('login')->with('error', 'LINE認証に失敗しました');
    }

    $accessToken = $response->json()['access_token'];

    // LINEプロフィール情報を取得
    $profileResponse = Http::withHeaders([
      'Authorization' => 'Bearer ' . $accessToken,
    ])->get('https://api.line.me/v2/profile');

    if ($profileResponse->failed()) {
      Log::error('LINEプロフィール取得失敗: ' . $profileResponse->body());
      return redirect()->route('login')->with('error', 'LINEプロフィールの取得に失敗しました');
    }

    $lineUserId = $profileResponse->json()['userId'];
    $displayName = $profileResponse->json()['displayName'];

    // 既存のユーザーか確認し、なければ新規作成
    $user = User::where('line_user_id', $lineUserId)
      ->orWhere('email', $lineUserId . '@line.com') // 既存のメールアドレスを検索
      ->first();

    if (!$user) {
      // ユーザーが存在しない場合は新規作成
      $user = User::create([
        'name' => $displayName,
        'line_user_id' => $lineUserId,
        'email' => $lineUserId . '@line' . time() . '.com', // ユニークなメールアドレスを生成
        'password' => bcrypt(Str::random(16)), // ダミーのパスワードを設定
      ]);
    }

    // ユーザーをログインさせる
    Auth::login($user, true);

    // 認証後に /goals へリダイレクト
    return redirect('/goals');
  }

  // LINE通知のスケジュール
  public function scheduleNotification($userId, $message = 'デフォルトのメッセージ')
  {
      SendLineNotificationJob::dispatch($userId, $message);
      return response()->json(['success' => true, 'message' => 'Notification queued for sending']);
  }
  

  // LINE通知のトグル（オン・オフ切り替え）
  public function toggleNotifications(Request $request)
  {
    $user = auth()->user();
    $user->notifications_enabled = !$user->notifications_enabled;
    $user->save();

    return response()->json([
      'success' => true,
      'notifications_enabled' => $user->notifications_enabled
    ]);
  }

  public function sendNotification(User $user, Task $task)
  {
      if ($task->start_time) { // start_timeが設定されているか確認
          if ($user->isLineAuthenticated()) {
              // LINE認証済みの場合はLINE通知を送信
              SendLineNotificationJob::dispatch($user->id, $task->name . ' のタスクが開始されます');
          } elseif ($user->isEmailAuthenticated()) {
              // メール認証済みの場合はメール通知を送信
              $user->notify(new TaskReminder($task));
          } else {
              Log::warning("User {$user->id} has no valid authentication for notification.");
          }
      } else {
          Log::warning("Task {$task->id} has no start_time. No notification sent.");
      }
  }
  
}
