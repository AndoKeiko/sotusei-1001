<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function updateNotificationSettings(Request $request)
    {
        // 現在のユーザーを取得
        $user = Auth::user();

        // ユーザーの通知設定を更新
        $user->email_notifications = $request->email_notifications;
        $user->line_notifications = $request->line_notifications;
        $user->save();

        // 成功レスポンスを返す
        return response()->json(['success' => true]);
    }
}
