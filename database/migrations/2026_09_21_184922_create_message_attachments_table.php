<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('user_id'); // File upload karne wale user ki ID
            
            // File Information
            $table->string('file_name');          // Original file name (e.g., 'video.mp4')
            $table->string('file_path');          // Final storage path (e.g., 'chat_files/abc123_video.mp4')
            $table->string('file_type');          // MIME type (e.g., 'video/mp4', 'image/jpeg')
            $table->string('file_category');      // Custom category: 'image', 'video', 'document'
            $table->unsignedBigInteger('file_size'); // File size in Bytes
            $table->string('thumbnail_path')->nullable(); // Future use: Video/Image thumbnail
            
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade'); 
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_attachments');
    }
};