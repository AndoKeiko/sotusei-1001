<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReminderMail;
use App\Models\Task;

class Kernel extends ConsoleKernel
{
  /**
   * Define the application's command schedule.
   */
  protected function schedule(Schedule $schedule): void
  {
      $schedule->call(function () {
          Log::info('This task runs every minute');

          // タスクを取得 (ID 1 を例にしています)
          $task = Task::find(1); // 実際には条件を指定してタスクを取得するかもしれません
          
          // もしタスクが存在すればメールを送信
          if ($task) {
              Mail::to('gajumaro.no.ki@gmail.com')->send(new ReminderMail($task));
              Log::info('ReminderMail sent for task: ' . $task->name);
          } else {
              Log::warning('No task found for reminder mail.');
          }

      })->everyMinute();
  }

  /**
   * Register the commands for the application.
   */
  protected function commands(): void
  {
    $this->load(__DIR__ . '/Commands');

    require base_path('routes/console.php');
  }
}
