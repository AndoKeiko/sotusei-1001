@extends('layouts.app')

@section('content')
<div class="py-12">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 bg-white border-b border-gray-200">
        <h1 class="text-2xl font-semibold mb-6">{{ $goal->name }} のスケジュール</h1>

        <div class="mb-4">
          <p class="text-gray-700">目標期間: {{ $goal->period_start->format('Y-m-d') }} ~ {{ $goal->period_end->format('Y-m-d') }}</p>
          <p class="text-gray-700">作業開始時刻: {{ $goal->work_start_time }}</p>
          <p class="text-gray-700">1日の作業時間: {{ $goal->work_hours_per_day }} 時間</p>
        </div>

        <button id="generateScheduleBtn" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
          スケジュールを生成
        </button>

        <div id="calendar" class="mt-6"></div>

        <div class="mt-8">
          <a href="{{ route('goals.show', $goal) }}" class="text-blue-600 hover:text-blue-800">目標の詳細に戻る</a>
        </div>
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
  const calendarEl = document.getElementById('calendar');
  const generateScheduleBtn = document.getElementById('generateScheduleBtn');

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    events: @json($calendarEvents),
  });

  calendar.render();

  generateScheduleBtn.addEventListener('click', function() {
    fetch('{{ route("schedules.generate", $goal) }}')
      .then(response => response.json())
      .then(data => {
        calendar.removeAllEvents();
        calendar.addEventSource(data);
      })
      .catch(error => console.error('スケジュール生成中にエラーが発生しました:', error));
  });
});
</script>
@endpush