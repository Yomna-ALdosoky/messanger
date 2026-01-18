<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Conversation;

class MessengerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user1 = User::find(1); // المستخدم الأول
        $user2 = User::find(2); 
        // إنشاء محادثة إذا مش موجودة
        $conversation = Conversation::firstOrCreate(
            ['type' => 'pear', 'user_id' => $user1->id]
        );

        // attach المستخدمين فقط لو مش موجودين
        $conversation->participants()->syncWithoutDetaching([
            $user1->id => ['joined_at' => now()],
            $user2->id => ['joined_at' => now()]
        ]);

        // إنشاء رسالة فقط لو مش موجودة
        if ($conversation->messages()->count() === 0) {
            $message = $conversation->messages()->create([
                'user_id' => $user1->id,
                'body' => 'مرحباً! هذه رسالة تجريبية'
            ]);

            // تحديث last_message_id في المحادثة
            $conversation->update([
                'last_message_id' => $message->id
            ]);
        }
    }
}
