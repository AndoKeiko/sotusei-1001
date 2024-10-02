<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
  use HasFactory;

  protected $table = 'goals';
  protected $primaryKey = 'id';
  public $incrementing = true;
  protected $keyType = 'int';

  // fillable: マスアサインメント可能な属性
  protected $fillable = [
    'user_id',
    'name',
    'current_status',
    'period_start',
    'period_end',
    'description',
    'status',
    'total_time',
    'progress_percentage',
    'work_hours_per_day',
    'work_start_time',
  ];

  // casts: 属性の型キャスト
  protected $casts = [
    'user_id' => 'integer',
    'current_status' => 'string',
    'period_start' => 'date',
    'period_end' => 'date',
    'description' => 'string',
    'status' => 'integer',
    'total_time' => 'integer',
    'progress_percentage' => 'integer',
    'work_hours_per_day' => 'float',
    'work_start_time' => 'datetime',
  ];

  // タスクとのリレーション
  public function tasks()
  {
    return $this->hasMany(Task::class);
  }
}
