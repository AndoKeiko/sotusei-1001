@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold mb-6">目標一覧</h2>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <a href="{{ route('goals.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 mb-4">
                    新しい目標を作成
                </a>

                @if ($goals->isNotEmpty())
                    <ul class="space-y-6">
                        @foreach ($goals as $goal)
                            <li class="bg-gray-50 p-4 rounded-lg shadow">
                                <h3 class="font-semibold text-lg text-gray-800">
                                    {{ $goal->name }} ({{ $goal->period_start }} - {{ $goal->period_end }})
                                </h3>
                                <div class="mt-2">
                                    <a href="{{ route('tasks.index', $goal) }}" class="text-blue-600 hover:underline">
                                        タスク一覧を表示
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500">目標がまだありません。</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection