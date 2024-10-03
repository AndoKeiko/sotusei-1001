<?php
//使ってない他所に散乱
namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Task;
use App\Models\ChatHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception; // この行を追加

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function generateTasksFromChat(Request $request, Goal $goal)
    {
        Log::info('Received task generation request:', $request->all());
        try {
            $userMessage = $request->input('message', 'ユーザーからのメッセージ');

            // チャットのためのプロンプトを構築
            $prompt = $this->buildPrompt($goal, $userMessage);
            $aiResponse = $this->getAIResponse($prompt);
            $tasks = $this->parseAIResponse($aiResponse);

            DB::beginTransaction();
            try {
                $createdTasks = $this->createTasks($tasks, $goal->id, Auth::id());
                
                // チャット履歴の保存
                TASK::create([
                    'goal_id' => $goal->id,
                    'user_id' => Auth::id(),
                    'message' => $userMessage,
                    'response' => $aiResponse,
                ]);

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Failed to create tasks or save chat history: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to create tasks or save chat history'], 500);
            }

            return response()->json([
                'message' => $aiResponse,
                'tasks' => $createdTasks
            ]);
        } catch (Exception $e) {
            Log::error('Unexpected error in generateTasksFromChat: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }
    }
    public function chat(Request $request, Goal $goal)
    {
        $userMessage = $request->input('message');
        $aiResponse = $this->getAIResponse($goal, $userMessage);
        
        // チャット履歴の保存
        TASK::create([
            'goal_id' => $goal->id,
            'user_id' => Auth::id(),
            'message' => $userMessage,
            'response' => $aiResponse,
        ]);

        // タスクの生成や更新のロジック

        return response()->json([
            'message' => $aiResponse,
            'tasks' => $createdTasks ?? []
        ]);
    }

    public function getHistory(Goal $goal)
    {
        $task = TASK::where('goal_id', $goal->id)
                                  ->orderBy('created_at', 'asc')
                                  ->get();

        return response()->json(['task' => $task]);
    }

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

    public function convertPriorityToInt($priority)
    {
      switch (strtolower($priority)) {
        case '高':
          return 3;
        case '中':
          return 2;
        case '低':
          return 1;
        default:
          return 2;
      }
    }
  
    public function getChatHistory($id)
    {
      $goal = Goal::findOrFail($id);
      return response()->json(['task' => $goal->task]);
    }

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
}