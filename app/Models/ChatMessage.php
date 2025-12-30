<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatMessage extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'chat_room_id',
        'user_id',
        'employee_id',
        'message',
    ];

    protected static function booted()
    {
        static::creating(function ($msg) {
            if (! $msg->id) {
                $msg->id = (string) Str::uuid();
            }
        });
    }

    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
