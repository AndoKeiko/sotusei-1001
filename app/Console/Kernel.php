<?php

namespace App\Console;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskReminder;
use App\Services\NotificationService;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Application;

class Kernel extends ConsoleKernel
{

  protected $notificationService;

  public function __construct(Application $app, Dispatcher $events)
  {
      parent::__construct($app, $events);
      $this->notificationService = new NotificationService();
  }

  protected function schedule(Schedule $schedule): void
  {
    // 認証済みユーザーに対して、開始予定時間10分前のタスク通知を送信
      $schedule->command('app:send-task-notifications 10')->everyMinute();
  }

  protected function notifyUsersBeforeTask()
  {
    // 現在の時刻
    $currentTime = Carbon::now();
    // 現在時刻から10分以内に開始するタスクを通知対象にする
    $notificationTime = $currentTime->copy()->addMinutes(10);

    // 認証済みのユーザーを取得
    $users = User::whereNotNull('email_verified_at')->get();

    foreach ($users as $user) {
      // 該当するユーザーのタスクを取得
      $tasks = Task::where('user_id', $user->id)
        ->whereNotNull('start_time')  // start_time が null でないことを確認
        ->where('start_time', '>=', $currentTime) // 現在時刻以降
        ->where('start_time', '<=', $notificationTime) // 10分以内に開始
        ->where(function ($query) {
          $query->whereNull('last_notification_sent')  // 通知が送られていない場合
            ->orWhere('last_notification_sent', '<', Carbon::now()->subMinutes(10));  // 前回の通知から10分以上経過している場合
        })
        ->get();

      // タスクが存在する場合、通知を送信
      foreach ($tasks as $task) {
        try {
          // メールまたはLINEで通知を送信
          $this->sendNotification($user, $task);

          // 通知が正常に送信されたら、last_notification_sent カラムを更新
          $task->last_notification_sent = Carbon::now();
          $task->save();

          Log::info("Task notification sent successfully to: {$user->email} for task: {$task->name}");
        } catch (\Exception $e) {
          // エラー処理
          Log::error("Failed to send task notification to: {$user->email}. Error: " . $e->getMessage());
        }
      }
    }
  }

  protected function sendNotification(User $user, Task $task)
  {
      $this->notificationService->sendNotification($user, $task);
      
  }

  protected function commands(): void
  {
    $this->load(__DIR__ . '/Commands');
    require base_path('routes/console.php');
  }
}
