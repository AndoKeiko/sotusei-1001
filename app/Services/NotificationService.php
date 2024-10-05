<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendLineNotificationJob;
use App\Notifications\TaskReminder;

class NotificationService
{
    public function sendNotification(User $user, Task $task)
    {
        if ($task->start_time) {
            if ($user->line_notifications()) {
                // LINE認証済みの場合はLINE通知を送信
                SendLineNotificationJob::dispatch($user->id, $task->name . ' のタスクが開始されます');
            } elseif ($user->email_notifications()) {
                // メール認証済みの場合はメール通知を送信
                $user->notify(new TaskReminder($task));
            } else {
                Log::warning("User {$user->id} has no valid authentication for notification.");
            }
            // 通知が送信されたら、last_notification_sentを更新
            $task->last_notification_sent = now();
            $task->save();
        } else {
            Log::warning("Task {$task->id} has no start_time. No notification sent.");
        }
    }
}
