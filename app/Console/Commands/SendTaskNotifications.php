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
use Illuminate\Support\Facades\Log;

class SendTaskNotifications extends Command
{
    protected $signature = 'app:send-task-notifications {minutes=15}';
    protected $description = 'Send notifications for tasks starting soon';

    public function handle()
    {
        $minutes = $this->argument('minutes');
        $tasks = Task::whereNotNull('start_time')//nullじゃないものだけ
            ->where('start_time', '>', Carbon::now())
            ->where('start_time', '<=', Carbon::now()->addMinutes($minutes))
            ->where('notified', false)
            ->get();

            $notifiedCount = 0;
            $failedCount = 0;

        // タスクが存在しない場合
        if ($tasks->isEmpty()) {
            Log::info("No tasks found to notify within the next {$minutes} minutes.");
        }

        foreach ($tasks as $task) {
            $user = $task->user;  // belongsTo リレーションを利用
            Log::info("Processing task ID: {$task->id} for user ID: {$user->id}");

            // メール認証済みのユーザーにのみ送信
            if ($user && $user->hasVerifiedEmail()) {
                Log::info("Sending notification for task ID: {$task->id}");
                try {
                    Mail::to($user->email)->send(new ReminderMail($task));
                    Notification::send($user, new TaskReminder($task));
                    
                    // 通知済みフラグを更新
                    $task->notified = true;
                    $task->save();
                    $notifiedCount++;

                    $message = "Task ID: {$task->id} has been notified.";
                    $this->info($message);
                    Log::info($message);
                } catch (\Exception $e) {
                    $failedCount++;
                    $errorMessage = "Failed to send notification for task ID: {$task->id}, Error: " . $e->getMessage();
                    $this->error($errorMessage);
                    Log::error($errorMessage);
                }
            }
        }

        $this->info("Task notifications process completed.");
        $this->info("Total tasks processed: " . $tasks->count());
        $this->info("Notifications sent successfully: " . $notifiedCount);
        $this->info("Failed notifications: " . $failedCount);

        Log::info("Task notifications process completed. Total: {$tasks->count()}, Sent: {$notifiedCount}, Failed: {$failedCount}");
    }
}

