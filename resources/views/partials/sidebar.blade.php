<div class="w-5/12 bg-gray-900 relative flex">
    <nav class="w-16 bg-gray-600 text-white flex flex-col items-start py-10 text-xs h-full">
        <div class="flex flex-col items-center justify-center">
            <x-dropdown-link :href="route('profile.edit')">
                {{ __('Profile') }}
            </x-dropdown-link>
            <a href="{{ route('home') }}" class="mb-4">
                <i class="fas fa-home"></i> ホーム
            </a>
            <a href="{{ route('goals.index') }}" class="mb-4">
                <i class="fas fa-bullseye"></i> 目標
            </a>
            <a href="{{ route('tasks.index') }}" class="mb-4">
                <i class="fas fa-tasks"></i> タスクリスト
            </a>
            <a href="{{ route('posts.create') }}" class="mb-4">
                <i class="fas fa-book-open"></i> 学習記録
            </a>
        </div>
        <div class="flex flex-col items-center justify-center w-full mt-auto">
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-white">
                        <i class="fas fa-sign-out-alt"></i> ログアウト
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-white">
                    <i class="fas fa-sign-in-alt"></i> ログイン
                </a>
            @endauth
        </div>
    </nav>
    <div class="p-12 mt-16 text-white w-full text-left">
        @yield('left-side-text')
    </div>
</div>