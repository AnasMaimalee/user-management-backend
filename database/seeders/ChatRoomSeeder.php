<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChatRoom;

class ChatRoomSeeder extends Seeder
{
    public function run(): void
    {
        ChatRoom::firstOrCreate([
            'id' => uniqid(),
            'name' => 'Company Chat'
        ]);
    }
}
