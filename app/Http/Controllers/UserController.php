<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
  public function updateNotificationSettings(Request $request)
  {
      $user = auth()->user();
      $user->email_notifications = $request->email_notifications;
      $user->line_notifications = $request->line_notifications;
      $user->save();
  
      return response()->json(['success' => true]);
  }
}
