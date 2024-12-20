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
        if (!$task->start_time) {
            Log::warning("Task {$task->id} has no start time.");
            return false;
        }

        try {
            // LINE認証済みの場合はLINE通知、そうでなければメール通知
            if ($user->line_notifications()) {
                SendLineNotificationJob::dispatch($user->id, $task->name . ' のタスクが開始されます');
                Log::info("LINE notification dispatched for User {$user->id}, Task {$task->id}");
            } elseif ($user->email_notifications()) {
                $user->notify(new TaskReminder($task));
                Log::info("Email notification sent for User {$user->id}, Task {$task->id}");
            } else {
                Log::warning("User {$user->id} has no valid authentication for notification. Task {$task->id}");
                return false;
            }

            // last_notification_sent の更新
            $task->last_notification_sent = now();
            $task->save();
            Log::info("Notification sent for User {$user->id}, Task {$task->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send notification for User {$user->id}, Task {$task->id}. Error: " . $e->getMessage());
            return false;
        }
    }
}
