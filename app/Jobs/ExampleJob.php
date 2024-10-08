<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExampleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data; // ジョブに渡されるデータ

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;  // コンストラクタでデータを受け取る
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // ここにジョブの処理を記述します
        Log::info('ExampleJobが実行されました！データ: ' . $this->data);
    }
}
