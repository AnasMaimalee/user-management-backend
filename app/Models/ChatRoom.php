<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatRoom extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['name'];

    protected static function booted()
    {
        static::creating(function ($room) {
            if (! $room->id) {
                $room->id = (string) Str::uuid();
            }
        });
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
