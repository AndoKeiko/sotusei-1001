<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskStartNotification;

class SendTaskNotifications extends Command
{
    protected $signature = 'app:send-task-notifications';
    protected $description = 'Send notifications for tasks starting soon';

    public function handle()
    {
        $tasks = Task::where('start_time', '>', Carbon::now())
                     ->where('start_time', '<=', Carbon::now()->addMinutes(15))
                     ->where('notified', false)
                     ->get();

        foreach ($tasks as $task) {
            // ここでは、タスクの所有者に通知を送信すると仮定しています
            Notification::send($task->user, new TaskStartNotification($task));
            
            $task->notified = true;
            $task->save();
        }

        $this->info('Task notifications sent successfully.');
    }
}