<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class CalendarController extends Controller
{
    public function getEvents(Request $request)
    {
        $goalId = $request->input('goal_id');
        $tasks = Task::where('goal_id', $goalId)->get();

        $events = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->name,
                'start' => $task->start_date . 'T' . $task->start_time,
                'end' => $task->end_date ? $task->end_date . 'T' . $task->end_time : null,
                'extendedProps' => [
                    'description' => $task->description,
                    'priority' => $task->priority,
                    'estimatedTime' => $task->estimated_time,
                    'start_date' => $task->start_date,
                    'start_time' => $task->start_time,
                ]
            ];
        });

        return response()->json(['events' => $events]);
    }
}