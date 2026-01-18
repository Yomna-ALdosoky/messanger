<?php

use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ConversationsController;
use App\Http\Controllers\MessagesController;
use App\Models\Conversation;
use Illuminate\Support\Facades\Route;
use Termwind\Components\Raw;

Route::middleware('auth:sanctum')->group(function(){
    // بجيب كل  المحادثات الخاصه ب user معين
    Route::get('conversations', [ConversationsController::class, 'index']);
    Route::get('conversations/{conversation}', [ConversationsController::class, 'show']);
    Route::post('conversations/{conversation}/participants', [ConversationsController::class, 'addparticipant']);
    Route::delete('conversations/{conversation}/participants', [ConversationsController::class, 'removeparticipant']);

    // هعرض الرسايل بنئا عل conversations
    Route::get('conversation/{id}/messages', [MessagesController::class, 'index']);

    Route::post('/messages', [MessagesController::class, 'store'])->name('api.messages.store');
    //حذف الرساله من طريف ال user الي فاتح بس
    Route::delete('messages/{id}/destroy', [MessagesController::class, 'destroy']);
});