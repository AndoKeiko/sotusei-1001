<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Task;
use App\Services\ScheduleGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use OpenAI\Laravel\Facades\OpenAI;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class GoalController extends Controller
{
  protected $scheduleGenerator;

  public function __construct(ScheduleGeneratorService $scheduleGenerator)
  {
      $this->scheduleGenerator = $scheduleGenerator;
  }

  // 目標の一覧表示
  public function index()
  {
    $user = Auth::user();

    // ログインユーザーのすべての目標を取得し、それに関連するタスクをロード
    $goals = Goal::where('user_id', $user->id)->with('tasks')->get();

    return view('goals.index', compact('goals'));
  }

  // 新規目標作成フォームの表示
  public function create()
  {
    return view('goals.create');
  }

  // 目標の保存
  public function store(Request $request)
  {
    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'current_status' => 'nullable|string',
      'period_start' => 'required|date',
      'period_end' => 'required|date|after:period_start',
      'work_hours_per_day' => 'nullable|numeric|min:0|max:24',
    ]);

    $goal = new Goal($validatedData);
    $goal->user_id = Auth::id();
    $goal->save();

    // AIを利用してタスクを生成
    $tasks = $this->generateTasks($goal);

    return redirect()->route('tasks.index', ['goal' => $goal->id])
                  ->with('success', '新しい目標とタスクが作成されました');
  }

  // 目標の詳細表示
  public function show(Goal $goal)
  {
      $scheduleGenerator = new ScheduleGeneratorService();
      $workPeriodStart = $goal->period_start;
      $startTime = $goal->work_start_time ?? '09:00'; // null の場合、午前9時をデフォルトとする
      $hoursPerDay = $goal->work_hours_per_day ?? 8.0;

      $schedule = $scheduleGenerator->generateSchedule($goal, $workPeriodStart, $startTime, $hoursPerDay);
 
      return view('schedules.show', compact('goal', 'schedule'));
  }

  // 目標編集ページの表示
  public function edit(Goal $goal)
  {
    return view('goals.edit', compact('goal'));
  }

  // 目標の更新
  public function update(Request $request, Goal $goal)
  {
    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'current_status' => 'nullable|string',
      'period_start' => 'required|date',
      'period_end' => 'required|date|after:period_start',
      'work_hours_per_day' => 'nullable|numeric|min:0|max:24',
    ]);

    $goal->update($validatedData);

    return redirect()->route('goals.show', $goal)->with('success', '目標が更新されました');
  }

  // 目標の削除
  public function destroy(Goal $goal)
  {
    $goal->delete();
    return redirect()->route('goals.index')->with('success', '目標が削除されました');
  }

  // タスクの生成 (AI利用)
  private function generateTasks(Goal $goal)
  {
    $userMessage = "新しい目標が作成されました";
    $prompt = $this->buildPrompt($goal, $userMessage);
    $aiResponse = $this->getAIResponse($prompt);
    $tasks = $this->parseAIResponse($aiResponse);
    return $this->createTasks($tasks, $goal->id, Auth::id());
  }

  // AIへのプロンプト作成
  private function buildPrompt(Goal $goal, string $userMessage): string
  {
    return "目標: {$goal->name}\n"
      . "現在の状況: {$goal->current_status}\n"
      . "目標期間開始: {$goal->period_start}\n"
      . "目標期間終了: {$goal->period_end}\n\n"
      . "その他注釈事項: {$userMessage}\n\n"
      . "この情報に基づいて、目標期間内に目標を達成するための学習スケジュールを作成してください。\n"
      . "以下の点に注意してスケジュールを作成してください：\n"
      . "1. 各タスクや活動に推奨される時間を含めてください。時間は数値（小数点以下1桁まで）で表してください。\n"
      . "2. スケジュールは日単位、週単位、または月単位で構成し、具体的な活動内容を記載してください。\n"
      . "3. 週単位の時間（例：週5時間）や月単位の時間（例：月20時間）は、目標期間内の総時間に変換してください。\n"
      . "4. すべての時間は、目標期間全体での合計時間として計算してください。\n"
      . "5. 回答はタスク10件程度で、タスクごとに改行してください。\n\n"
      . "回答は日本語で、以下のJSONフォーマットにて作成してください：\n\n"
      . "[\n"
      . "  {\n"
      . "    \"taskName\": \"[タスク名]\",\n"
      . "    \"taskTime\": [時間数（数値）],\n"
      . "    \"taskPriority\": [重要度（1-3の数値）]\n"
      . "  },\n"
      . "  {\n"
      . "    \"taskName\": \"[タスク名]\",\n"
      . "    \"taskTime\": [時間数（数値）],\n"
      . "    \"taskPriority\": [重要度（1-3の数値）]\n"
      . "  }\n"
      . "  ...\n"
      . "]";
  }

  // AIの応答を取得
  private function getAIResponse(string $prompt): string
  {
    try {
      $result = OpenAI::chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => [
          ['role' => 'system', 'content' => $prompt],
        ],
      ]);

      $content = $result->choices[0]->message->content;
      Log::info('AI Response received: ' . $content);
      return preg_replace('/```json\s*|\s*```/', '', $content);
    } catch (\Exception $e) {
      Log::error('Failed to get AI response: ' . $e->getMessage());
      throw new \Exception('Failed to generate AI response');
    }
  }

  // AI応答をパースしてタスクを生成
  private function parseAIResponse(string $response): array
  {
    try {
      $tasks = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
      if (!is_array($tasks)) {
        throw new \Exception('Decoded response is not an array');
      }
      return $tasks;
    } catch (\JsonException $e) {
      Log::error('Failed to parse AI response: ' . $e->getMessage());
      throw new \Exception('Failed to parse AI response');
    }
  }

  // タスクのデータベース保存
  private function createTasks(array $tasks, int $goalId, int $userId): array
  {
    $createdTasks = [];
    foreach ($tasks as $task) {
      $createdTask = Task::create([
        'goal_id' => $goalId,
        'user_id' => $userId,
        'name' => $task['taskName'],
        'estimated_time' => $task['taskTime'],
        'priority' => $task['taskPriority'],
      ]);
      $createdTasks[] = [
        'id' => $createdTask->id,
        'taskName' => $createdTask->name,
        'taskTime' => $createdTask->estimated_time,
        'taskPriority' => $createdTask->priority,
      ];
    }
    return $createdTasks;
  }

  public function history()
  {
    $goals = Goal::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
    return view('goals.history', compact('goals'));
  }

  public function scheduleGenerate(Request $request, Goal $goal)
    {
        try {
            $validatedData = $request->validate([
                'work_period_start' => 'required|date',
                'work_start_time' => 'required',
                'work_hours_per_day' => 'required|numeric|min:0|max:24',
            ]);

            $schedule = $this->scheduleGenerator->generateSchedule(
                $goal,
                $validatedData['work_period_start'],
                $validatedData['work_start_time'],
                $validatedData['work_hours_per_day']
            );

            $calendarEvents = $this->generateCalendarEvents($schedule);

            return response()->json([
                'success' => true,
                'schedule' => $schedule,
                'calendarEvents' => $calendarEvents
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function generateCalendarEvents($schedule)
    {
        $events = [];
        foreach ($schedule as $date => $tasks) {
            foreach ($tasks as $task) {
              // $startDateTime = Carbon::parse($date . ' ' . $task['start_time']);
              // $this->scheduleEmailNotification($task, $startDateTime);

                $events[] = [
                    'title' => $task['name'],
                    'start' => $date . 'T' . $task['start_time'],
                    'end' => $date . 'T' . $task['end_time'],
                ];
            }
        }
        return $events;
      }
    // メール通知をスケジュールするメソッド
    // private function scheduleEmailNotification($task, $startDateTime)
    // {
    //     // スケジュールされたタスクの開始1時間前にメールを送信する
    //     $mailTime = $startDateTime->subHour();

    //     // タスク開始1時間前にメールを送信
    //     Mail::later($mailTime, 'emails.taskNotification', ['task' => $task], function (Message $message) use ($task) {
    //         $message->to('recipient@example.com') // 宛先のメールアドレス
    //                 ->subject('タスクの開始通知: ' . $task['name']); // メールの件名
    //     });
    // }
}
