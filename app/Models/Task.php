<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Task extends Model
{
  use HasFactory;

  // テーブル名や主キーの設定
  protected $table = 'tasks';
  protected $primaryKey = 'id';
  public $incrementing = true;
  protected $keyType = 'int';

  // 複数代入可能な属性
  protected $fillable = [
    'user_id',
    'goal_id',
    'name',
    'description',
    'elapsed_time',
    'estimated_time',
    'start_date',
    'start_time',
    'priority',
    'order',
    'review_interval',
    'repetition_count',
    'last_notification_sent',
    'end_date',
  ];

  // キャスト（データ型の変換）
  protected $casts = [
    'user_id' => 'integer',
    'goal_id' => 'integer',
    'elapsed_time' => 'integer',
    'estimated_time' => 'float',
    'start_date' => 'date:Y-m-d',
    'priority' => 'integer',
    'order' => 'integer',
    'review_interval' => 'string',
    'repetition_count' => 'integer',
    'last_notification_sent' => 'datetime',
    'end_date' => 'date',
  ];

  // ルートキー名の設定
  public function getRouteKeyName()
  {
    return 'id';
  }

  // 定数の定義
  public const REVIEW_INTERVALS = [
    'next_day',
    '7_days',
    '14_days',
    '28_days',
    '56_days',
    'completed'
  ];

  // アクセサとミューテータ
  // 開始日時を取得
  public function getStartAttribute()
  {
    if ($this->start_date && $this->start_time) {
      return Carbon::parse($this->start_date . ' ' . $this->start_time)->format('Y-m-d\TH:i');
    } elseif ($this->start_date) {
      return $this->start_date->format('Y-m-d\TH:i');
    }
    return null;
  }

  // 開始時間の取得と設定
  public function getStartTimeAttribute($value)
  {
    if (!$value) {
      return null;
    }
    try {
      return Carbon::parse($value)->format('H:i');
    } catch (\Exception $e) {
      Log::warning("Invalid start_time format for task {$this->id} (User ID: {$this->user_id}, Task Name: {$this->name}): {$value}");
      return null;
    }
  }


  public function setStartTimeAttribute($value)
  {
    try {
      $this->attributes['start_time'] = Carbon::parse($value)->format('H:i');
    } catch (\Exception $e) {
      Log::warning("Invalid time format provided for start_time: {$value}");
      $this->attributes['start_time'] = null; // 不正な場合はnullを設定
    }
  }

  // 終了日時の取得
  public function getEndAttribute()
  {
    if ($this->start_date && $this->estimated_time) {
      $startDateTime = Carbon::parse($this->start_date . ' ' . ($this->start_time ?? '00:00:00'));
      return $startDateTime->addHours($this->estimated_time)->format('Y-m-d\TH:i');
    }
    return null;
  }

  // カレンダーイベントの取得
  public function getCalendarEventAttribute()
  {
    // $this->start_date を Carbon インスタンスに変換
    $startDate = $this->start_date ? Carbon::parse($this->start_date) : Carbon::today();
    $startTime = $this->start_time ? $this->start_time : '09:00:00';

    $startDateTime = Carbon::parse($startDate->format('Y-m-d') . ' ' . $startTime);
    $start = $startDateTime->format('Y-m-d\TH:i');

    if ($this->end_date) {
      $endDateTime = Carbon::parse($this->end_date);
      $end = $endDateTime->format('Y-m-d\TH:i');
    } else {
      $estimatedHours = $this->estimated_time ?? 1;
      $endDateTime = $startDateTime->copy()->addHours($estimatedHours);
      $end = $endDateTime->format('Y-m-d\TH:i');
    }

    return [
      'id' => $this->id,
      'title' => $this->name,
      'start' => $start,
      'end' => $end,
      'extendedProps' => [
        'description' => $this->description,
        'priority' => $this->priority,
        'estimatedTime' => $this->estimated_time,
        'start_date' => $startDate->format('Y-m-d'),
        'start_time' => $startTime,
      ],
    ];
  }



  // リレーションシップ
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function goal()
  {
    return $this->belongsTo(Goal::class, 'goal_id', 'id');
  }
}
