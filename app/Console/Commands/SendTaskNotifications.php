<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Notifications\TaskReminder; 
use Illuminate\Support\Facades\Notification;
use App\Mail\ReminderMail;

class SendTaskNotifications extends Command
{
  protected $signature = 'app:send-task-notifications {minutes=15}';
  protected $description = 'Send notifications for tasks starting soon';


  public function handle()
  {
    $minutes = $this->argument('minutes');
    $tasks = Task::where('start_time', '>', Carbon::now())
      ->where('start_time', '<=', Carbon::now()->addMinutes($minutes))
      ->where('notified', false)
      ->get();

    foreach ($tasks as $task) {
      $user = $task->user;  // belongsTo リレーションを利用

      // メール認証済みのユーザーにのみ送信
      if ($user && $user->hasVerifiedEmail()) {
        // メール送信
        Mail::to($user->email)->send(new ReminderMail($task));

        // 通知送信
        Notification::send($user, new TaskReminder($task));
        $task->notified = true;
        $task->save();
      }
    }

    $this->info('Task notifications sent successfully.');
  }
}
