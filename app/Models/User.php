<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    protected $appends = [
        'avatar_url',
    ];

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'participants')
            ->latest('last_message_id')
            ->withPivot(['joined_at', 'role']);    
    }

    public function sentMessage()
    {
        return $this->hasMany(Message::class, 'user_id', 'id');
    }

    public function receivedMessage()
    {
        return $this->belongsToMany(Message::class, 'recipients')->withPivot([
            'read_at' => 'deletd at',
        ]);
    }

    public function getAvatarUrlAttribute(){
        return 'https://ui-avatars.com/api/?rounded=true&background=0D8ABC&color=fff&name=' . $this->name;
    }
}
