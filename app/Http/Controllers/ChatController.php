<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Models\MessageAttachment;
use App\Events\MessageSent; 
use Illuminate\Support\Facades\Storage;
use App\Events\FileSent;
use Illuminate\Support\Str;
use App\Events\UserStatusUpdated; 

class ChatController extends Controller
{
  

    //  Eloquent ka use: Saare users fetch karna (khud ko chhor kar)
    public function index()
    {
        // Eloquent Query: 
        $users = User::where('id', '!=', auth()->id())->get();
        return view('chat.index', compact('users'));
    }

   
    //  Naya Message Save Karna (AJAX ke liye)
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'receiver_id' => 'required|exists:users,id',
        ]);

        // Eloquent Query: Database mein naya message insert karo
        $message = Message::create([
            'sender_id' => auth()->id(),          // Current logged-in user
            'receiver_id' => $request->receiver_id, // Jisko message bhejna hai
            'message' => $request->message,         // Message text
        ]);

        // Eager Loading: Message ke sath sender ki details bhi load karo
        $message->load('sender');

        // EVENT FIRE: Yeh line Reverb ko trigger karti hai
        event(new MessageSent($message));

        // JSON Response return karo (Frontend AJAX ke liye)
        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    
    //Purani Chat History Fetch Karna (AJAX ke liye)
     
    public function getMessages($userId)
    {
        // Eloquent Query: Dono users ke beech ke saare messages nikalo
        $messages = Message::where(function($query) use ($userId) {
            // Condition 1: Main ne bheja ho aur usne receive kiya ho
            $query->where('sender_id', auth()->id())
                  ->where('receiver_id', $userId);
        })->orWhere(function($query) use ($userId) {
            // Condition 2: Usne bheja ho aur maine receive kiya ho
            $query->where('sender_id', $userId)
                  ->where('receiver_id', auth()->id());
        })
        ->with('sender', 'attachments') // Eager Loading: Har message ke sath sender ka data bhi fetch karo (N+1 problem avoid karne ke liye)
        ->orderBy('created_at', 'asc') // Purane messages pehle, naye baad mein
        ->get(); // Collection return karo

        return response()->json($messages);
    }

    public function uploadChunk(Request $request)
    {
        // 1. Validation
        $request->validate([
            'upload_id' => 'required|string',
            'file_name' => 'required|string',
            'chunk_index' => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1',
            'chunk' => 'required|file',
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:1000',
        ]);

        // 2. Temporary folder path
        $tempDir = storage_path('app/temp_uploads');
        
        // Agar temp folder nahi hai toh banao
        if (!Storage::exists('temp_uploads')) {
            Storage::makeDirectory('temp_uploads');
        }

        // 3. Temporary file path (upload_id based)
        $tempFilePath = $tempDir . '/' . $request->upload_id . '.tmp';

        // 4. Chunk ko temporary file mein append karo
        $chunkFile = $request->file('chunk');
        $chunkContent = file_get_contents($chunkFile->getPathname());
        
        file_put_contents($tempFilePath, $chunkContent, FILE_APPEND);

        // 5. Check karo ke yeh aakhri chunk hai ya nahi
        if ($request->chunk_index == $request->total_chunks - 1) {
            // ✅ YEH AAKHRI CHUNK HAI - Final processing shuru karo
            
            // 5.1. File category determine karo
            $mimeType = mime_content_type($tempFilePath);
            $category = $this->determineCategory($mimeType);
            
            // 5.2. Final file name aur path
            $finalFileName = time() . '_' . $request->file_name;
            $finalPath = 'chat_files/' . $finalFileName;
            
            // 5.3. Temporary file ko final destination par move karo
            Storage::disk('public')->put($finalPath, file_get_contents($tempFilePath));
            
            // 5.4. Temporary file delete karo (safai)
            Storage::delete('temp_uploads/' . $request->upload_id . '.tmp');
            
            // 5.5. Message create karo
            $message = Message::create([
                'sender_id' => auth()->id(),
                'receiver_id' => $request->receiver_id,
                'message' => $request->message ?? '',
            ]);
            
            // 5.6. Attachment create karo
            $attachment = MessageAttachment::create([
                'message_id' => $message->id,
                'user_id' => auth()->id(),
                'file_name' => $request->file_name,
                'file_path' => $finalPath,
                'file_type' => $mimeType,
                'file_category' => $category,
                'file_size' => filesize($tempFilePath), // Final file size
                'thumbnail_path' => null,
            ]);
            
            // 5.7. Message ko attachments ke sath load karo
            $message->load('sender', 'attachments');
            
            // 5.8. Event fire karo
            event(new FileSent($message));
            
            // 5.9. Response return karo
            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'message_id' => $message->id,
                    'attachment_id' => $attachment->id,
                    'file_path' => $finalPath,
                ]
            ]);
        }

        // 6. Agar yeh aakhri chunk nahi hai, toh sirf "chunk uploaded" response bhejo
        return response()->json([
            'success' => true,
            'message' => 'Chunk uploaded',
            'data' => [
                'next_chunk' => $request->chunk_index + 1
            ]
        ]);
    }

    // ✅ HELPER METHOD: File category determine karna
    private function determineCategory($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } else {
            return 'document';
        }
    }

    // ✅ NEW METHOD: File download karna
    public function download($attachmentId)
    {
        $attachment = MessageAttachment::findOrFail($attachmentId);
        
        // Security check: Kya current user is message ka participant hai?
        $message = $attachment->message;
        if ($message->sender_id != auth()->id() && $message->receiver_id != auth()->id()) {
            abort(403, 'Unauthorized access');
        }
        
        // File path determine karo
        $path = $attachment->file_path;
        
        // Public disk se download karo
        return Storage::disk('public')->download($path, $attachment->file_name);
    }

    // ✅ NEW METHOD: Message delete karna
    public function destroy($messageId)
    {
        $message = Message::findOrFail($messageId);
        
        // Security check: Kya current user is message ka sender hai?
        if ($message->sender_id != auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        // Attachments ki files delete karo (Storage se)
        foreach ($message->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
            
            // Thumbnail bhi delete karo agar hai
            if ($attachment->thumbnail_path) {
                Storage::disk('public')->delete($attachment->thumbnail_path);
            }
        }
        
        // Message delete karo (cascade se attachments bhi delete ho jayengi)
        $message->delete();
        
        return response()->json(['success' => true]);
    }
    /**
     * User ping handle karein (Online status update)
     */
      public function ping(Request $request)
    {
        $user = auth()->user();
        
        if ($user) {
            // last_seen ko current time par update karein
            $user->last_seen = now();
            $user->save();
            
            // Event fire karein taake doosre users ko real-time update mile
            event(new \App\Events\UserStatusUpdated($user));
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * User logout ya tab band hone par foran Offline signal bhejein
     */
        /**
     * User logout ya tab band hone par foran Offline signal bhejein
     */
      
   public function pingOffline(Request $request)
    {
        $userId = $request->input('user_id');
        
        if ($userId) {
            // Direct DB update (Fastest & most reliable)
            \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $userId)
                ->update(['last_seen' => now()->subMinutes(5)]);
            
            // Event fire karein
            $user = \App\Models\User::find($userId);
            if ($user) {
                event(new \App\Events\UserStatusUpdated($user));
            }
        }
        
        return response('', 204); // 204 No Content
    }
}