<?php

namespace App\Http\Controllers;

use App\Events\MessageCreated;
use App\Models\Conversation;
use App\Models\Participant;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

use Throwable;

class MessagesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $user = Auth::user();
        $conversation= $user->conversations()
        ->with([
            'lastMessage',
            'participants' => function($builder) use ($user){
            $builder->where('id', '<>', $user->id);
        }])
        ->findOrFail($id);
        return [
            'conversation'=> $conversation,
            'messages' =>$conversation->messages()->with('user')->paginate(100),
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'message' =>['required', 'string'],
            'conversation_id' => [
                Rule::requiredIf(function() use ($request) {
                    return !$request->input('user_id');
                }),
                'integer',
                'exists:conversations,id'
                ],
                //مين user الي هتبعت له الرسال
            'user_id' => [
                Rule::requiredIf(function() use ($request) {
                    return !$request->input('conversation_id');
                }),
                'integer',
                'exists:users,id'
            ],
        ]);

        $user = Auth::user();
  
        $conversation_id = $request->post('conversation_id');
        $user_id = $request->post('user_id');

        
        DB::beginTransaction();
        try {
            if($conversation_id){
                $conversation= $user->conversations()->findOrFail($conversation_id);
            }else{
                $conversation = Conversation::where('type', '=', 'pear')
                ->whereHas('participants', function($builder) use ($user_id, $user){
                    $builder->join('participants as participants2', 'participants2.conversation_id', '=', 'participants.conversation_id')
                    ->where('participants.user_id', '=', $user_id)
                    ->where('participants2.user_id', '=', $user->id);
                })->first();
            }
            if(!$conversation){
                $conversation = Conversation::create([
                    'user_id' => $user->id,
                    'type' => 'pear'
                    
                ]);
                $conversation->participants()->attach([
                    $user->id => ['joined_at' => now()],
                    $user_id => ['joined_at' => now()],
                ]);
            }

            $message = $conversation->messages()->create([
                'user_id' =>$user->id,
                'body' => $request->post('message'),
            ]);

            DB::statement('
                INSERT INTO recipients(user_id, message_id)
                SELECT user_id,? 
                FROM participants
                WHERE conversation_id = ?
                ', [
                    $message->id,
                    $conversation->id
            ]);
            $conversation->update([
                'last_message_id' => $message->id,
            ]);
            DB::commit();
            $message->load('user');
            broadcast(new MessageCreated($message));
            // broadcast(new MessageCreated($message->fresh()));
        } catch(Throwable $e) {
            DB::rollBack();

            throw $e;
        }
        return $message;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Recipient::where([
            'user_id'=> Auth::user($id),
            'message_id'=> $id,
        ])->delete();

        return [
            'message'=> 'deleted!',
        ];
    }
}
