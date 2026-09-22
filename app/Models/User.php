<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


use Spatie\Permission\Traits\HasRoles; //Spatie ka HasRoles trait import for using assignRole() and other methods etc

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    //  HasRoles trait ka use 
    use HasRoles; 

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',  
        'phone_num',
        'last_seen',
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
            'last_seen' => 'datetime',
        ];
    }

     public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class, 'user_id');
    }

    /**
     * Check karein ke user online hai ya nahi
     * Agar last_seen 1 minute se kam purana hai to online
     */
    public function isOnline(): bool
    {
        return $this->last_seen && $this->last_seen->gt(now()->subMinute());
    }
    
    /**
     * Human-readable last seen format return karein
     */
    public function lastSeenFormatted(): string
    {
        if (!$this->last_seen) {
            return 'Never';
        }
        
        if ($this->isOnline()) {
            return 'Online';
        }
        
        return 'Last seen ' . $this->last_seen->diffForHumans();
    }
}
