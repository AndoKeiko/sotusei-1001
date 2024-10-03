<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskStartNotification;

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
            Notification::send($task->user, new TaskStartNotification($task));
            $task->notified = true;
            $task->save();
        }

        $this->info('Task notifications sent successfully.');
    }
}
