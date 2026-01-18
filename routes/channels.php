<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

// Broadcast::channel('Messenger.{userId}', function ($user, $userId) {
//     // لازم يرجع object/hash للمستخدم، مش true
//     return [
//         'id' => $user->id,
//         'name' => $user->name,
//     ];
// });

// Broadcast::routes(['middleware' => ['auth', 'verified']]);

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });


Broadcast::channel('Messenger.{id}', function($user, $id) {
    if ($user->id == $id) {
        return $user;
    }
});


