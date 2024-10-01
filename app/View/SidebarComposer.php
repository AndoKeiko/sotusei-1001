<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Route;

class SidebarComposer
{
    public function compose(View $view)
    {
        $routeName = Route::currentRouteName();
        $leftSideText = $this->getLeftSideText($routeName);
        $view->with('leftSideText', $leftSideText);
    }

    private function getLeftSideText($routeName)
    {
        switch ($routeName) {
            case 'home':
                return view('sidebar.home')->render();
            case 'tasks.index':
                return view('sidebar.tasks')->render();
            case 'posts.create':
                return view('sidebar.create_post')->render();
            default:
                return view('sidebar.default')->render();
        }
    }
}