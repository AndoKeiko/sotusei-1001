@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-8">
  <div class="py-8">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-semibold leading-tight">{{ $goal->name }}タスク一覧</h2>
    </div>
    <div class="my-2 flex sm:flex-row flex-col">
      <div class="block relative">
        <span class="h-full absolute inset-y-0 left-0 flex items-center pl-2">
          <svg viewBox="0 0 24 24" class="h-4 w-4 fill-current text-gray-500">
            <path d="M10 4a6 6 0 100 12 6 6 0 000-12zm-8 6a8 8 0 1114.32 4.906l5.387 5.387a1 1 0 01-1.414 1.414l-5.387-5.387A8 8 0 012 10z"></path>
          </svg>
        </span>
        <input placeholder="タスクを検索"
          class="appearance-none rounded-r rounded-l sm:rounded-l-none border border-gray-400 border-b block pl-8 pr-6 py-2 w-full bg-white text-sm placeholder-gray-400 text-gray-700 focus:bg-white focus:placeholder-gray-600 focus:text-gray-700 focus:outline-none" />
      </div>
    </div>
    <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-x-auto">
      <div class="inline-block min-w-full shadow rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal" id="taskTable">
          <thead>
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">順序</th>
              <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                タスク名
              </th>
              <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">
                予想時間
              </th>
              <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">
                優先度
              </th>
              <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">
                アクション
              </th>
            </tr>
          </thead>
          <tbody>
            @foreach ($tasks as $index => $task)
            <tr class="task-item" data-task-id="{{ $task->id }}">
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="task-order">{{ $index + 1 }}</span>
              </td>
              <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                <span class="task-name-display">{{ $task->name }}</span>
                <input type="text" class="task-name-input hidden border rounded px-2 py-1 w-full" value="{{ $task->name }}">
              </td>
              <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm  text-center">
                <span class="task-time-display">{{ $task->estimated_time }}</span>
                <input type="number" class="task-time-input hidden border rounded px-2 py-1 w-full" value="{{ $task->estimated_time }}"> 時間
              </td>
              <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm  text-center">
                <span class="task-priority-display">
                  @if($task->priority == 1)
                  低
                  @elseif($task->priority == 2)
                  中
                  @else
                  高
                  @endif
                </span>
                <select class="task-priority-select hidden border rounded px-2 py-1 w-full">
                  <option value="1" {{ $task->priority == 1 ? 'selected' : '' }}>低</option>
                  <option value="2" {{ $task->priority == 2 ? 'selected' : '' }}>中</option>
                  <option value="3" {{ $task->priority == 3 ? 'selected' : '' }}>高</option>
                </select>
              </td>
              <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                <button class="edit-task bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded mr-2">編集</button>
                <button class="update-task bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded mr-2 hidden">更新</button>
                <button class="delete-task bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">削除</button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="mt-8">
    <a href="{{ route('schedules.index', $goal) }}" class="text-blue-600 hover:text-blue-800">カレンダーに書き出す</a>
  </div>
  <div class="mt-8">
    <a href="{{ route('goals.index', $goal) }}" class="text-blue-600 hover:text-blue-800">目標ページに戻る</a>
  </div>
</div>
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const taskTable = document.getElementById('taskTable');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const taskList = document.getElementById('taskList');
    
    if (typeof Sortable === 'undefined') {
        console.error('Sortable is not loaded');
        return;
    }

    new Sortable(taskTable.querySelector('tbody'), {
      animation: 150,
      onEnd: function(evt) {
        updateTaskOrder();
        // サーバーに新しい順序を送信する処理
        updateOrderOnServer();
      }
    });

    taskTable.addEventListener('click', function(e) {
      if (e.target.classList.contains('edit-task')) {
        const row = e.target.closest('tr');
        row.querySelectorAll('.task-name-display, .task-time-display, .task-priority-display, .edit-task').forEach(el => el.classList.add('hidden'));
        row.querySelectorAll('.task-name-input, .task-time-input, .task-priority-select, .update-task').forEach(el => el.classList.remove('hidden'));
      }

      if (e.target.classList.contains('update-task')) {
        const row = e.target.closest('tr');
        const taskId = row.dataset.taskId;
        const name = row.querySelector('.task-name-input').value;
        const time = row.querySelector('.task-time-input').value;
        const priority = row.querySelector('.task-priority-select').value;
        const start_time = row.querySelector('.task-time-input').value; 
        const start_date = row.querySelector('.task-time-input').value; 

        fetch(`https://gajumaro.jp/yumekanau/update-task`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              id: taskId,
              name,
              estimated_time: time,
              priority,
              start_date,
              start_time
            })
          })
          .then(response => {
            if (!response.ok) {
              return response.text().then(text => {
                throw new Error(`HTTP error! status: ${response.status}, message: ${text}`);
              });
            }
            return response.json();
          })
          .then(data => {
            if (data.success) {
              row.querySelector('.task-name-display').textContent = name;
              row.querySelector('.task-time-display').textContent = time;
              row.querySelector('.task-priority-display').textContent = ['低', '中', '高'][priority - 1];

              row.querySelectorAll('.task-name-input, .task-time-input, .task-priority-select, .update-task').forEach(el => el.classList.add('hidden'));
              row.querySelectorAll('.task-name-display, .task-time-display, .task-priority-display, .edit-task').forEach(el => el.classList.remove('hidden'));

              alert('タスクが正常に更新されました。');
            } else {
              alert('タスクの更新に失敗しました。');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert(`タスクの更新中にエラーが発生しました: ${error.message}`);
          });
      }

      if (e.target.classList.contains('delete-task')) {
        if (confirm('本当にこのタスクを削除しますか？')) {
          const row = e.target.closest('tr');
          const taskId = row.dataset.taskId;

          fetch(`/tasks/${taskId}`, {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
              }
            })
            .then(response => {
              if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
              }
              return response.json();
            })
            .then(data => {
              if (data.success) {
                row.remove();
                alert('タスクが正常に削除されました。');
              } else {
                alert('タスクの削除に失敗しました。');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              alert(`タスクの削除中にエラーが発生しました: ${error.message}`);
            });
        }
      }
    });

    function updateTaskOrder() {
      const taskItems = document.querySelectorAll('.task-item');
      taskItems.forEach((item, index) => {
        const orderSpan = item.querySelector('.task-order');
        orderSpan.textContent = index + 1;
      });
    }

    function updateOrderOnServer() {
      const taskItems = document.querySelectorAll('.task-item');
      const orderData = Array.from(taskItems).map((item, index) => {
        return {
          id: item.dataset.taskId,
          order: index + 1
        };
      });

      fetch('/update-task-order', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            tasks: orderData
          })
        })
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            console.log('Task order updated successfully');
          } else {
            console.error('Failed to update task order');
          }
        })
        .catch(error => {
          console.error('Error updating task order:', error);
        });
    }
  });
</script>
@endpush
@endsection