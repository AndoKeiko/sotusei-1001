<?php

namespace App\Http\Controllers;

use App\Jobs\SomeJob;
use Illuminate\Http\Request;

class SomeController extends Controller
{
    public function triggerEvent()
    {
        $data = '何かのデータ';  // ジョブに渡したいデータ
        SomeJob::dispatch($data);  // ジョブをディスパッチしてデータを渡す
    }
}
