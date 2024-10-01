<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Notifications\TaskReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendTaskReminders extends Command
{
    protected $signature = 'tasks:send-reminders';
    protected $description = 'Send reminders for tasks starting soon';

    public function handle()
    {
        $now = Carbon::now();
        $thirtyMinutesFromNow = $now->copy()->addMinutes(30);

        Log::info("Checking for tasks between {$now} and {$thirtyMinutesFromNow}");

        $tasks = Task::whereBetween('start_time', [$now, $thirtyMinutesFromNow])->get();

        Log::info("Found " . $tasks->count() . " tasks to send reminders for.");

        foreach ($tasks as $task) {
            Log::info("Sending reminder for task: {$task->id} - {$task->name} to user: {$task->user_id}");
            $task->user->notify(new TaskReminder($task));
            $this->info("Sent reminder for task: {$task->name}");
        }

        Log::info('Task reminders sent successfully.');
        $this->info('Task reminders sent successfully.');
    }
}