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

  public function __construct(Application $app, Dispatcher $events, NotificationService $notificationService)
  {
      parent::__construct($app, $events);
      $this->notificationService = $notificationService;
  }

  protected function schedule(Schedule $schedule): void
  {
      $schedule->call(function () {
          $this->notifyUsersBeforeTask();
      })->everyFiveMinutes();  // 5分ごとに通知を確認
  }

  protected function notifyUsersBeforeTask()
  {
      // 現在の時刻を取得して再利用
      $currentTime = Carbon::now();
      $notificationTime = $currentTime->copy()->addMinutes(10);
  
      // 認証済みのユーザーを取得
      $users = User::whereNotNull('email_verified_at')->get();
  
      foreach ($users as $user) {
          // 該当するユーザーのタスクを取得
          $tasks = Task::where('user_id', $user->id)
              ->whereNotNull('start_time')
              ->where('start_time', '>=', $currentTime) 
              ->where('start_time', '<=', $notificationTime)
              ->where(function ($query) use ($currentTime) {
                  $query->whereNull('last_notification_sent')
                        ->orWhere('last_notification_sent', '<', $currentTime->subMinutes(10));
              })
              ->get();
  
          foreach ($tasks as $task) {
              try {
                  // 通知送信
                  $this->sendNotification($user, $task);
  
                  // 通知が送信されたらlast_notification_sentを更新
                  $task->last_notification_sent = Carbon::now();
                  $task->save();
  
                  Log::info("Task notification sent successfully to: {$user->email} for task: {$task->name}");
              } catch (\Exception $e) {
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
