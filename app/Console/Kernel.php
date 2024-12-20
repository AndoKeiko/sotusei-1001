<?php

namespace App\Console;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Events\Dispatcher;

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

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
