@extends('layouts.app')
<script>
  let initialSchedule = @json($initialSchedule);
  let initialCalendarEvents = @json($calendarEvents);
  let updateTaskUrl = "{{ route('tasks.update', ':taskId') }}";
  console.log('initialCalendarEvents:', initialCalendarEvents);
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
        <button id="saveAllTasksBtn" class="mt-4 px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
          全てのタスクを保存
        </button>
        <div id="calendar" class="mt-8"></div>
        <ul class="flex flex-nowrap flex-row justify-start items-center mt-4">
          <li> <a id="saveAllTasksBtn" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
              全てのタスクを保存
            </a></li>
          <li>
            <a href="{{ route('goals.index', $goal) }}" class="px-4 py-2 ml-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">目標ページに戻る</a>
          </li>
          <li>
            <a href="{{ route('tasks.index', $goal) }}" class="px-4 py-2 ml-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">タスク一覧に戻る</a>
          </li>
        </ul>
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
          displaySchedule(groupEventsByDate(calendar.getEvents()));
        } else {
          console.error('Error: Dropped event is undefined');
          alert('イベントの移動中にエラーが発生しました: イベントが見つかりません。');
        }
      },
      eventResize: function(info) {
        if (info.event) {
          updateTask(info.event, true);
          displaySchedule(groupEventsByDate(calendar.getEvents()));
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


    if (typeof initialSchedule !== 'undefined' && initialSchedule !== null) {
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
            calendar.removeAllEvents();
            calendar.addEventSource(data.calendarEvents);
            displaySchedule(data.calendarEvents);
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



    function displaySchedule(eventsData) {
      console.log('displaySchedule called with:', eventsData);

      let groupedEvents = {};

      if (Array.isArray(eventsData)) {
        // カレンダーイベントの配列の場合
        groupedEvents = eventsData.reduce((groups, event) => {
          const date = new Date(event.start).toISOString().split('T')[0];
          if (!groups[date]) {
            groups[date] = [];
          }
          groups[date].push(event);
          return groups;
        }, {});
      } else if (typeof eventsData === 'object' && eventsData !== null) {
        // 既にグループ化されたデータの場合
        groupedEvents = eventsData;
      } else {
        console.error('Invalid events data:', eventsData);
        return;
      }

      console.log('Processed events:', groupedEvents);

      let scheduleHtml = `
    <h3 class="text-base font-semibold cursor-pointer mb-2" id="accordionBtn">
      生成されたスケジュール
    </h3>
    <div id="scheduleAccordion" class="">
      <ul class="list-none">
  `;

      for (const date in groupedEvents) {
        scheduleHtml += `
    <li class="mb-2 text-sm">
      <div class="font-semibold py-0 px-1 bg-gray-200 hover:bg-gray-300 rounded cursor-pointer">
        ${date}
      </div>
      <ul id="schedule-${date}" class="list-disc pl-8 mt-2">
    `;

        groupedEvents[date].forEach(event => {
          const startTime = event.start_time || (event.start ? new Date(event.start).toTimeString().substr(0, 5) : '');
          const endTime = event.end_time || (event.end ? new Date(event.end).toTimeString().substr(0, 5) : '');
          const duration = event.duration ||
            (event.start && event.end ? (new Date(event.end) - new Date(event.start)) / (1000 * 60 * 60) : 0);
          const roundedDuration = Math.round(duration * 10) / 10;
          scheduleHtml += `<li class="text-xs">${event.name || event.title} (${roundedDuration}時間, ${startTime} - ${endTime})</li>`;
        });

        scheduleHtml += '</ul></li>';
      }

      scheduleHtml += '</ul></div>';

      const scheduleOutput = document.getElementById('scheduleOutput');
      if (scheduleOutput) {
        scheduleOutput.innerHTML = scheduleHtml;
        scheduleOutput.classList.remove('hidden');
      } else {
        console.error('scheduleOutput element not found');
      }
    }


    const saveAllTasksBtn = document.getElementById('saveAllTasksBtn');
    saveAllTasksBtn.addEventListener('click', saveAllTasks);

    function saveAllTasks() {
      const events = calendar.getEvents();
      const tasksToSave = events.map(event => {
        const startDate = event.start.toISOString().split('T')[0];
        const endDate = event.end ? event.end.toISOString().split('T')[0] : startDate;
        const startTime = dateToHi(event.start);
        const endTime = event.end ? dateToHi(event.end) : '10:00'; // デフォルトの終了時間を10:00に設定

        return {
          id: event.id,
          name: event.title,
          description: event.extendedProps?.description || '',
          estimated_time: event.extendedProps?.estimatedTime || 1,
          start_date: startDate,
          start_time: startTime,
          end_date: endDate,
          end_time: endTime,
          priority: event.extendedProps?.priority || '2',
          goal_id: goalId
        };
      });

      console.log('Saving all tasks:', tasksToSave);

      const saveUrl = "{{ route('tasks.saveAll') }}";

      fetch(saveUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            tasks: tasksToSave
          }),
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            console.log('All tasks saved successfully', data);
            // カレンダーイベントを更新
            calendar.removeAllEvents();
            data.tasks.forEach(task => {
              calendar.addEvent({
                id: task.id,
                title: task.name,
                start: `${task.start_date}T${task.start_time}`,
                end: `${task.end_date}T${task.end_time}`,
                extendedProps: {
                  description: task.description,
                  estimatedTime: task.estimated_time,
                  priority: task.priority,
                  end_time: task.end_time
                }
              });
            });
            alert('全てのタスクが正常に保存されました。');
          } else {
            console.error('Failed to save all tasks', data);
            alert('タスクの保存中にエラーが発生しました。');
          }
        })
        .catch(error => {
          console.error('Error saving all tasks:', error);
          alert('タスクの保存中にエラーが発生しました: ' + error);
        });
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
      document.getElementById('editTaskEstimatedTime').value = event.extendedProps.estimatedTime;
      document.getElementById('editTaskStartDate').value = event.extendedProps.start_date || event.start.toISOString().split('T')[0];
      document.getElementById('editTaskStartTime').value = event.extendedProps.start_time || event.start.toTimeString().substr(0, 5);
      document.getElementById('editTaskPriority').value = event.extendedProps.priority || '2';

      taskEditModal.classList.remove('hidden');
      console.log('Task edit modal opened', event.extendedProps);
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
      calendar.on('eventChange', function(info) {
        displaySchedule(groupEventsByDate(calendar.getEvents()));
      });

      calendar.on('eventDrop', function(info) {
        displaySchedule(groupEventsByDate(calendar.getEvents()));
      });

      calendar.on('eventResize', function(info) {
        displaySchedule(groupEventsByDate(calendar.getEvents()));
      });
      updateTask(calendar.getEventById(taskId));
      taskEditModal.classList.add('hidden');
      // closeTaskModal();
    });

    function groupEventsByDate(events) {
      return events.reduce((groups, event) => {
        const date = event.start.toISOString().split('T')[0];
        if (!groups[date]) {
          groups[date] = [];
        }
        groups[date].push(event);
        return groups;
      }, {});
    }

    function formatTime(date) {
      const hours = date.getHours().toString().padStart(2, '0');
      const minutes = date.getMinutes().toString().padStart(2, '0');
      return `${hours}:${minutes}`; // コメント: H:i形式に変更
    }

    function updateTask(event, isDropEvent = false) {
      // event が undefined または null の場合のチェック
      if (!event || !event.id) {
        console.error('Error: event is undefined or null');
        return;
      }

      // フラグを利用して二重更新を防ぐ
      if (isDropEvent && event.extendedProps.isUpdated) {
        console.log('Already updated, skipping...');
        return;
      }

      const taskData = {
        id: event.id,
        name: event.title,
        description: event.extendedProps?.description || '',
        estimated_time: event.extendedProps?.estimatedTime || 1,
        start_date: event.start.toISOString().split('T')[0],
        start_time: dateToHi(event.start),
        end_date: event.end ? event.end.toISOString().split('T')[0] : event.start.toISOString().split('T')[0],
        end_time: event.end ? dateToHi(event.end) : '10:00', // デフォルトの終了時間を10:00に設定
        priority: event.extendedProps?.priority || '2',
        is_partial_update: isDropEvent
      };

      console.log('Updating task with data:', taskData);

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
          return response.json();
        })
        .then(data => {
          if (data.success) {
            console.log('Task updated successfully', data.task);
            // カレンダーイベントを更新
            event.remove();
            calendar.addEvent({
              id: data.task.id,
              title: data.task.name,
              start: `${data.task.start_date}T${data.task.start_time}`,
              end: `${data.task.end_date}T${data.task.end_time}`,
              extendedProps: {
                description: data.task.description,
                estimatedTime: data.task.estimated_time,
                priority: data.task.priority,
                end_time: data.task.end_time
              }
            });
            alert('タスクの更新が完了しました。');
          } else {
            console.error('Failed to update task', data);
            alert('タスクの更新に失敗しました。');
          }
        })
        .catch(error => {
          console.error('Error updating task:', error);
          alert('タスクの更新中にエラーが発生しました: ' + error);
        });
    }

    function reloadCalendarEvents() {
      console.log(`Reloading calendar events for goal ${goalId}`);
      fetch(`/get-calendar-events/${goalId}`, {
          cache: 'no-store',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(response => response.json())
        .then(data => {
          console.log('Raw fetched data:', data);
          let events;
          if (data.calendarEvents) {
            events = data.calendarEvents;
          } else if (Array.isArray(data)) {
            events = data;
          } else {
            events = Object.values(data);
          }

          console.log('Processed events:', events);

          if (Array.isArray(events) && events.length > 0) {
            calendar.removeAllEvents();
            calendar.addEventSource(events);
            console.log('Events added to calendar');
          } else {
            console.error('No valid events found in data');
          }
        })
        .catch(error => {
          console.error('Error fetching calendar events:', error);
        });
    }
    // ページロード時にイベントを取得してカレンダーに表示
    document.addEventListener('DOMContentLoaded', function() {
      reloadCalendarEvents();
    });


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

    // ISO8601形式の日時文字列をHTML時刻入力形式に変換する関数
    function isoToHtmlTime(isoString) {
      return isoString.split('T')[1].substr(0, 5);
    }

    // HTML日付と時刻入力をISO8601形式に変換する関数
    function htmlToIso(date, time) {
      return `${date}T${time}:00`;
    }

    function formatTimeToISO8601(date, timeString) {
      const [hours, minutes] = timeString.split(':');
      const newDate = new Date(date);
      newDate.setHours(hours, minutes, 0, 0);
      return newDate.toISOString();
    }

    // ISO8601形式の日時文字列をH:i形式に変換する関数
    function isoToHi(isoString) {
      const date = new Date(isoString);
      return date.toTimeString().substr(0, 5);
    }

    // 日付オブジェクトをH:i形式に変換する関数
    function dateToHi(date) {
      return date.toTimeString().substr(0, 5);
    }
  });
</script>
@endpush