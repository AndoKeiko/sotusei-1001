<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SomeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data; // ジョブで処理するデータ

    /**
     * Create a new job instance.
     *
     * @param mixed $data
     */
    public function __construct($data)
    {
        // ジョブに渡されたデータをセット
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 渡されたデータをログに出力する
        Log::info('SomeJobが実行されました。データ: ' . $this->data);
    }
}
