<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // マスアサインメント可能な属性
    protected $fillable = [
        'name',
        'nickname',
        'email',
        'password',
        'google_id',
        'line_user_id',
        'avatar',
        'email_verified_at',
    ];

    // 非表示にする属性
    protected $hidden = [
        'password',
        'remember_token',
        'line_user_id',  // 必要に応じて変更
    ];

    // キャストする属性
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'nickname' => 'string',
        'google_id' => 'string',
        'avatar' => 'string',
    ];


}
