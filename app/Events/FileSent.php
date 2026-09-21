<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

class FileSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        // Jab event fire hota hai, toh Message object (with attachments) is constructor mein aata hai
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        // Same channel structure as MessageSent
        // Format: chat.{min(id)}.{max(id)}
        $ids = [$this->message->sender_id, $this->message->receiver_id];
        sort($ids);
        
        return [
            new PrivateChannel('chat.' . $ids[0] . '.' . $ids[1]),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        // Yeh event ka naam hai jo frontend (Native WebSocket) use karega
        return 'file.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        // Attachments array ko format karo
        $attachmentsData = [];
        
        foreach ($this->message->attachments as $attachment) {
            $attachmentsData[] = [
                'id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'file_path' => $attachment->file_path,
                'file_type' => $attachment->file_type,
                'file_category' => $attachment->file_category,
                'file_size' => $attachment->file_size,
                'thumbnail_path' => $attachment->thumbnail_path,
            ];
        }

        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'receiver_id' => $this->message->receiver_id,
            'message' => $this->message->message,
            'attachments' => $attachmentsData, // ✅ Attachments array
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
}