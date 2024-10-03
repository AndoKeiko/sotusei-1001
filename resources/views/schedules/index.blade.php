@extends('layouts.app')
<script>
  let initialSchedule = @json($initialSchedule);
  let initialCalendarEvents = @json($calendarEvents);
  let updateTaskUrl = "{{ route('tasks.update', ':taskId') }}";
</script>
@section('content')
<div class="py-12">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 bg-white border-b border-gray-200">
        <h1 class="text-2xl font-semibold mb-6">{{ $goal->name }} のスケジュール</h1>

        <div class="mb-4">
          <label for="work_period_start" class="block text-sm font-medium text-gray-700">開始日</label>
          <input type="date" name="work_period_start" id="work_period_start" value="{{ $goal->period_start->format('Y-m-d') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>

        <div class="mb-4">
          <label for="work_start_time" class="block text-sm font-medium text-gray-700">作業開始時刻</label>
          <input type="time" id="work_start_time" name="work_start_time"
            value="{{ $goal->work_start_time ? $goal->work_start_time->format('H:i') : '09:00' }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>

        <div class="mb-4">
          <label for="work_hours_per_day" class="block text-sm font-medium text-gray-700">1日の作業時間</label>
          <input type="number" id="work_hours_per_day" name="work_hours_per_day" min="0" step="0.5" value="{{ $goal->work_hours_per_day ?? 8 }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>

        <button id="generateScheduleBtn" class="mb-4 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">スケジュール生成</button>

        <div id="scheduleOutput" class="mt-4 p-4 border rounded-md hidden accordion-collapse" data-accordion="collapse"></div>

        <div id="calendar" class="mt-8"></div>
        <div class="mt-8">
          <a href="{{ route('goals.index', $goal) }}" class="text-blue-600 hover:text-blue-800">目標ページに戻る</a>
        </div>
        <div class="mt-8">
          <a href="{{ route('tasks.index', $goal) }}" class="text-blue-600 hover:text-blue-800">タスク一覧に戻る</a>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- タスク編集モーダル -->
<div id="taskEditModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
          タスクの編集
        </h3>
        <div class="mt-2">
          <input type="hidden" id="editTaskId">
          <div class="mb-4">
            <label for="editTaskName" class="block text-sm font-medium text-gray-700">タスク名</label>
            <input type="text" id="editTaskName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div class="mb-4">
            <label for="editTaskDescription" class="block text-sm font-medium text-gray-700">説明</label>
            <textarea id="editTaskDescription" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
          </div>
          <div class="mb-4">
            <label for="editTaskEstimatedTime" class="block text-sm font-medium text-gray-700">予定時間（時間）</label>
            <input type="number" id="editTaskEstimatedTime" step="0.5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div class="mb-4">
            <label for="editTaskStartDate" class="block text-sm font-medium text-gray-700">開始日</label>
            <input type="date" id="editTaskStartDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div class="mb-4">
            <label for="editTaskStartTime" class="block text-sm font-medium text-gray-700">開始時刻</label>
            <input type="time" id="editTaskStartTime" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div class="mb-4">
            <label for="editTaskPriority" class="block text-sm font-medium text-gray-700">優先度</label>
            <select id="editTaskPriority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
              <option value="1">低</option>
              <option value="2">中</option>
              <option value="3">高</option>
            </select>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
        <button type="button" id="saveTaskChanges" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
          保存
        </button>
        <button type="button" id="closeTaskModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
          キャンセル
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css' rel='stylesheet' />
@endpush
@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js'></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('Calendar Events:', initialCalendarEvents);
    const calendarEl = document.getElementById('calendar');
    const generateScheduleBtn = document.getElementById('generateScheduleBtn');
    const workPeriodStart = document.getElementById('work_period_start');
    const workStartTime = document.getElementById('work_start_time');
    const workHoursPerDay = document.getElementById('work_hours_per_day');
    const taskEditModal = document.getElementById('taskEditModal');
    const saveTaskChanges = document.getElementById('saveTaskChanges');
    const scheduleOutput = document.getElementById('scheduleOutput');
    const goalId = @json($goalId);
    const generateScheduleUrl = "{{ route('goals.schedule.generate', ['goal' => $goal->id]) }}";

    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      events: initialCalendarEvents,
      editable: true,
      eventDisplay: 'block',
      displayEventTime: true,
      displayEventEnd: true,
      nextDayThreshold: '00:00:00',
      eventTimeFormat: {
        hour: 'numeric',
        minute: '2-digit',
        meridiem: 'short'
      },
      eventClick: function(info) {
        openEditModal(info.event);
      },
      eventDrop: function(info) {
        if (info.event) {
          updateTask(info.event, true);
        } else {
          console.error('Error: Dropped event is undefined');
          alert('イベントの移動中にエラーが発生しました: イベントが見つかりません。');
        }
      },
      eventResize: function(info) {
        if (info.event) {
          updateTask(info.event, true);
        } else {
          console.error('Error: Resized event is undefined');
          alert('イベントのリサイズ中にエラーが発生しました: イベントが見つかりません。');
        }
      },
      eventDidMount: function(info) {
        if (info.event.end && info.event.end.getDate() !== info.event.start.getDate()) {
          info.el.style.background = 'linear-gradient(90deg, #3788d8 0%, #3788d8 50%, #62a8e8 50%, #62a8e8 100%)';
          info.el.style.color = 'white';
        }
      }
    });

    calendar.render();

    if (typeof initialSchedule !== 'undefined') {
      displaySchedule(initialSchedule);
    }


    generateScheduleBtn.addEventListener('click', function() {
      const workPeriodStartValue = workPeriodStart.value;
      const startTimeValue = workStartTime.value;
      const hoursPerDayValue = parseFloat(workHoursPerDay.value);

      if (!workPeriodStartValue || !startTimeValue || isNaN(hoursPerDayValue)) {
        alert('すべての項目を正しく入力してください。');
        return;
      }

      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      console.log('Sending request with:', {
        workPeriodStartValue,
        startTimeValue,
        hoursPerDayValue
      });

      fetch(generateScheduleUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            work_period_start: workPeriodStartValue,
            work_start_time: startTimeValue,
            work_hours_per_day: hoursPerDayValue
          })
        })
        .then(response => {
          console.log('Response status:', response.status);
          console.log('Response headers:', response.headers);
          if (!response.ok) {
            return response.text().then(text => {
              console.error('Error response text:', text);
              throw new Error(`HTTP error! status: ${response.status}, message: ${text}`);
            });
          }
          return response.json();
        })
        .then(data => {
          console.log('Received data:', data);
          if (data.success) {
            displaySchedule(data.schedule);
            updateCalendar(data.calendarEvents);
          } else {
            console.error('Server reported failure:', data);
            alert(data.message || 'スケジュールの生成に失敗しました。');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('エラーが発生しました。コンソールを確認してください。');
        });
    });


    function displaySchedule(schedule) {
      console.log('Displaying schedule:', schedule);
      let scheduleHtml = `
        <h3 class="text-base font-semibold cursor-pointer mb-2" id="accordionBtn">
          生成されたスケジュール
        </h3>
        <div id="scheduleAccordion" class="">
          <ul class="list-none">
      `;
      let calendarEvents = [];
      for (const date in schedule) {
        scheduleHtml += `
          <li class="mb-2 text-sm">
            <div class="font-semibold py-0 px-1 bg-gray-200 hover:bg-gray-300 rounded cursor-pointer">
              ${date}
            </div>
            <ul id="schedule-${date}" class="list-disc pl-8 mt-2">
        `;
        for (const task of schedule[date]) {
          const roundedDuration = Math.round(task.duration * 10) / 10;
          scheduleHtml += `<li class="text-xs">${task.name} (${roundedDuration}時間, ${task.start_time} - ${task.end_time})</li>`;

          // カレンダーイベントを作成
          const startDateTime = new Date(`${date}T${task.start_time}`);
          const endDateTime = new Date(`${date}T${task.end_time}`);
          calendarEvents.push({
            id: task.id, // タスクIDを追加
            title: task.name,
            start: startDateTime.toISOString(),
            end: endDateTime.toISOString(),
            extendedProps: {
              duration: roundedDuration,
              description: task.description || '',
              estimatedTime: task.estimated_time || 0,
              priority: task.priority || '2',
            }
          });

        }
        scheduleHtml += '</ul></li>';
      }

      scheduleHtml += '</ul></div>';

      scheduleOutput.innerHTML = scheduleHtml;
      updateCalendar(calendarEvents);
      saveCalendarEvents(calendarEvents);
      scheduleOutput.classList.remove('hidden');
    }


    function updateCalendar(events) {
      console.log('Updating calendar with events:', events);
      calendar.removeAllEvents();
      calendar.addEventSource(events);
      console.log('Calendar updated');
    }

    function openEditModal(event) {
      const taskId = event.id;
      document.getElementById('editTaskId').value = taskId;
      document.getElementById('editTaskName').value = event.title;
      document.getElementById('editTaskDescription').value = event.extendedProps.description || '';
      document.getElementById('editTaskEstimatedTime').value = event.extendedProps.estimatedTime || '';
      document.getElementById('editTaskStartDate').value = event.extendedProps.start_date || event.start.toISOString().split('T')[0];
      document.getElementById('editTaskStartTime').value = event.extendedProps.start_time || event.start.toTimeString().substr(0, 5);
      document.getElementById('editTaskPriority').value = event.extendedProps.priority || '2';

      taskEditModal.classList.remove('hidden');
    }


    saveTaskChanges.addEventListener('click', function() {
      console.log('saveTaskChanges button clicked');
      const taskId = document.getElementById('editTaskId').value;
      const taskName = document.getElementById('editTaskName').value;
      const taskDescription = document.getElementById('editTaskDescription').value;
      const taskEstimatedTime = parseFloat(document.getElementById('editTaskEstimatedTime').value);
      const taskStartDate = document.getElementById('editTaskStartDate').value;
      const taskStartTime = document.getElementById('editTaskStartTime').value;
      const taskPriority = document.getElementById('editTaskPriority').value;
      const updateTaskUrl = "{{ route('tasks.update', ':taskId') }}";
      const event = calendar.getEventById(taskId);
      if (event) {
        event.remove();
      }

      const newStart = new Date(taskStartDate + 'T' + taskStartTime);
      const newEnd = new Date(newStart.getTime() + taskEstimatedTime * 60 * 60 * 1000);

      calendar.addEvent({
        id: taskId,
        title: taskName,
        start: newStart,
        end: newEnd,
        extendedProps: {
          description: taskDescription,
          estimatedTime: taskEstimatedTime,
          priority: taskPriority
        }
      });

      updateTask(calendar.getEventById(taskId));
      taskEditModal.classList.add('hidden');
      // closeTaskModal();
    });

    function formatTime(date) {
      const hours = date.getHours().toString().padStart(2, '0');
      const minutes = date.getMinutes().toString().padStart(2, '0');
      const seconds = date.getSeconds().toString().padStart(2, '0');
      return `${hours}:${minutes}:${seconds}`;
    }


    function updateTask(event, isDropEvent = false) {
      // event が undefined または null の場合のチェック
      if (!event) {
        console.error('Error: event is undefined or null');
        alert('タスクの更新中にエラーが発生しました: イベントが見つかりません。');
        return;
      }

      // event.id が undefined または null の場合のチェック
      if (!event.id) {
        console.error('Error: event.id is undefined or null');
        alert('タスクの更新中にエラーが発生しました: イベントIDが見つかりません。');
        return;
      }

      const taskData = {
        id: event.id,
        name: event.title,
        description: event.extendedProps?.description || '',
        estimated_time: event.extendedProps?.estimatedTime || 0,
        start_date: event.start ? event.start.toISOString().split('T')[0] : null,
        start_time: event.start ? formatTime(event.start) : null,
        end_date: event.end ? event.end.toISOString().split('T')[0] : null,
        end_time: event.end ? formatTime(event.end) : null,
        priority: event.extendedProps?.priority || '2',
        is_partial_update: isDropEvent
      };

      console.log('Updating task with data:', taskData);



      function formatTimeToHI(date) {
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        return `${hours}:${minutes}`;
      }

      function validateAndFixStartTime(startTime) {
        // すでに H:i フォーマットであればそのまま返す
        if (/^\d{2}:\d{2}$/.test(startTime)) {
          return startTime;
        }

        // H:i 形式でない場合、適切な時刻を生成して返す（ここでは現在時刻を使用）
        const date = new Date(); // 例: 現在時刻を使用
        console.warn('Invalid start_time format, correcting to current time:', date);
        return formatTimeToHI(date);
      }

      // タスクデータの start_time をチェックする部分
      let startTime = taskData.start_time;
      startTime = validateAndFixStartTime(startTime); // H:i に強制変換

      if (!startTime) {
        console.error('start_time is invalid and could not be corrected');
        alert('開始時間は H:i 形式で入力してください (例: 09:00)');
        return;
      }

      taskData.start_time = startTime; // 修正後の start_time を taskData に再代入


      const url = updateTaskUrl.replace(':taskId', event.id);

      fetch(url, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
          },
          body: JSON.stringify(taskData),
        })
        .then(response => {
          if (!response.ok) {
            return response.json().then(data => {
              throw new Error(`HTTP error! status: ${response.status}, message: ${data.message || 'Unknown error'}`);
            });
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            console.log('Task updated successfully', data.task);
            // 更新成功時の処理
            // カレンダーイベントを更新
            event.remove();
            calendar.addEvent({
              id: data.task.id,
              title: data.task.name,
              start: data.task.start_date ? `${data.task.start_date}T${data.task.start_time}` : null,
              end: data.task.end_date ? `${data.task.end_date}T${data.task.end_time}` : null,
              extendedProps: {
                description: data.task.description,
                estimatedTime: data.task.estimated_time,
                priority: data.task.priority
              }
            });
            alert('タスクの更新が完了しました。'); // 成功時のアラート
          } else {
            console.error('Failed to update task', data);
            alert('タスクの更新に失敗しました。');
          }
        })
        .catch(error => {
          console.error('Error updating task:', error.message);
          alert('タスクの更新中にエラーが発生しました: ' + error.message);
        });
    }

    function validateAndFixStartTime(time) {
      const regex = /^\d{2}:\d{2}$/;
      if (!regex.test(time)) {
        const parsedTime = new Date(`1970-01-01T${time}`);
        if (isNaN(parsedTime.getTime())) {
          return null;
        }
        const hours = parsedTime.getHours().toString().padStart(2, '0');
        const minutes = parsedTime.getMinutes().toString().padStart(2, '0');
        return `${hours}:${minutes}`;
      }
      return time;
    }


    function reloadCalendarEvents() {
      fetch('/api/get-calendar-events') // サーバー側で最新のイベントデータを返すエンドポイントを作成
        .then(response => response.json())
        .then(data => {
          calendar.removeAllEvents();
          calendar.addEventSource(data.calendarEvents);
        })
        .catch(error => {
          console.error('Error fetching calendar events:', error);
        });
    }

    function closeTaskModal() {
      const modal = document.getElementById('taskEditModal');
      if (modal) {
        modal.classList.add('hidden');
      }
    }
    // イベントリスナーの追加
    const closeButton = document.getElementById('closeTaskModal');
    if (closeButton) {
      closeButton.addEventListener('click', closeTaskModal);
    }

    function saveCalendarEvents(events) {
      const saveEventsUrl = "{{ route('tasks.saveEvents') }}";
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      fetch(saveEventsUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            events: events
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            console.log('Events saved successfully');
          } else {
            console.error('Failed to save events', data);
          }
        })
        .catch(error => {
          console.error('Error saving events:', error);
        });
    }



  });
</script>
@endpush