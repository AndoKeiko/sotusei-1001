<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

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

    protected $casts = [
        'user_id' => 'integer',
        'goal_id' => 'integer',
        'elapsed_time' => 'integer',
        'estimated_time' => 'float',
        'start_date' => 'date',
        'priority' => 'integer',
        'order' => 'integer',
        'review_interval' => 'string',
        'repetition_count' => 'integer',
        'last_notification_sent' => 'datetime',
        'end_date' => 'date',
    ];

    public function getRouteKeyName()
    {
        return 'id'; // または使用しているキーの名前
    }

    public const REVIEW_INTERVALS = [
        'next_day', '7_days', '14_days', '28_days', '56_days', 'completed'
    ];

    public function getStartAttribute()
    {
        if ($this->start_date && $this->start_time) {
            return Carbon::parse($this->start_date . ' ' . $this->start_time)->format('Y-m-d\TH:i:s');
        } elseif ($this->start_date) {
            return $this->start_date->format('Y-m-d\TH:i:s');
        }
        return null;
    }
    public function getStartTimeAttribute($value)
    {
        if (!$value) {
            return null;
        }
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            Log::warning("Invalid start_time format for task {$this->id}: {$value}");
            return null;
        }
    }

    public function setStartTimeAttribute($value)
    {
        $this->attributes['start_time'] = Carbon::parse($value)->format('H:i:s');
    }
    public function getEndAttribute()
    {
        if ($this->start_date && $this->estimated_time) {
            $startDateTime = Carbon::parse($this->start_date . ' ' . ($this->start_time ?? '00:00:00'));
            return $startDateTime->addHours($this->estimated_time)->format('Y-m-d\TH:i:s');
        }
        return null;
    }

    public function getCalendarEventAttribute()
    {
        $start = $this->start_date->format('Y-m-d') . 'T' . ($this->start_time ?? '09:00:00');
        $end = $this->end_date
            ? $this->end_date->format('Y-m-d\TH:i:s')
            : Carbon::parse($start)->addHours($this->estimated_time ?? 1)->format('Y-m-d\TH:i:s');

        return [
            'id' => $this->id,
            'title' => $this->name,
            'start' => $start,
            'end' => $end,
            'extendedProps' => [
                'description' => $this->description,
                'priority' => $this->priority,
                'estimatedTime' => $this->estimated_time,
                'goalName' => $this->goal->name ?? 'No Goal',
            ],
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function goal()
    {
        return $this->belongsTo(Goal::class, 'goal_id', 'id');
    }
}
