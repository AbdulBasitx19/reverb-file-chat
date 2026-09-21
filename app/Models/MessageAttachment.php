<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageAttachment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'message_id',
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'file_category',
        'file_size',
        'thumbnail_path',
    ];

    /**
     * Get the message that owns this attachment.
     * Relationship: MessageAttachment belongs to Message
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the user who uploaded this attachment.
     * Relationship: MessageAttachment belongs to User (Uploader)
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}