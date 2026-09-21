@extends('layouts.master')

@section('title', 'Chat System')

@section('content')
<div class="chat-wrapper" style="height: calc(100vh - 190px); min-height: 450px;">    
    {{-- ===== LEFT SIDEBAR (Users List) ===== --}}
    <div class="chat-leftsidebar minimal-border" style="border-right: 1px solid var(--vz-border-color)">
        <div class="chat-sidebar-header px-4 pt-4 pb-3">
            <h5 class="mb-4">Chats</h5>
            <div class="search-box">
                <input type="text" id="user-search" class="form-control bg-light border-light" placeholder="Search users...">
                <i class="ri-search-2-line search-icon"></i>
            </div>
        </div>

        <div class="chat-room-list pt-3" style="flex-grow: 1; overflow-y: auto;">
            <div class="px-4 mb-2">
                <h4 class="mb-0 fs-11 text-muted text-uppercase">Direct Messages</h4>
            </div>
            <ul class="list-unstyled chat-list chat-user-list" id="userList">
                @foreach($users as $user)
                <li class="user-item" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name}}">
                    <a href="javascript:void(0)" class="d-flex align-items-center p-3">
                        <div class="flex-shrink-0 me-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&color=fff"
                            alt="{{ $user->name }}" class="rounded-circle" width="40" height="40">
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-truncate mb-0 fw-medium">{{ $user->name }}</p>
                            <small class="text-muted" style="font-size: 11px;">{{ $user->email }}</small>
                        </div>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- ===== RIGHT SIDE: CHAT WINDOW ===== --}}
    <div class="user-chat minimal-border" style="background: #fff;">        
        {{-- Empty state --}}
        <div id="no-chat-selected" class="d-flex align-items-center justify-content-center h-100">
            <div class="text-center text-muted p-5">
                <i class="ri-chat-3-line fs-1 d-block mb-3"></i>
                <p class="mt-3">Select a user to Start Chatting</p>
            </div>
        </div>

        {{-- Chat Content Area --}}
        <div id="chat-content-area" class="d-none flex-column h-100">
            <div class="p-3 user-chat-topbar border-bottom" style="background: #fff;"> 
                <h5 class="card-title mb-0" id="chat-with-user">Select a user to start chatting</h5>
            </div>
            
            <div class="chat-conversation p-3 p-lg-4 flex-grow-1 overflow-auto" id="chat-messages" style="background: var(--vz-body-bg);"> 
                <!-- Messages will be appended here -->
            </div>
            
            {{-- ✅ NEW: File Preview Area --}}
            <div id="file-preview-area" class="border-top" style="display: none; background: var(--vz-card-bg, #fff);">
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted fw-medium">
                            <i class="ri-attachment-2"></i> <span id="file-count">0</span> files selected
                        </small>
                        <button type="button" id="clear-all-files" class="btn btn-sm btn-link text-danger p-0">
                            <i class="ri-close-line"></i> Clear all
                        </button>
                    </div>
                    <div id="file-preview-container" class="d-flex flex-wrap gap-2"></div>
                </div>
            </div>
            
            <div class="chat-input-section p-3 border-top"> 
                {{-- ✅ NEW: Upload Progress Bar --}}
                <div id="upload-progress" class="mb-2" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted"><i class="ri-upload-cloud-2-line"></i> Uploading... <span id="upload-filename"></span></small>
                        <small class="text-primary fw-medium"><span id="upload-percentage">0</span>%</small>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div id="upload-progress-bar" class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>

                <form id="message-form" class="d-flex align-items-center gap-2">
                    <input type="hidden" id="receiver-id" value=""> 
                    
                    {{-- ✅ NEW: Attachment Button --}}
                    <label for="file-input" class="btn btn-light btn-icon rounded-circle" style="cursor: pointer;" title="Attach files">
                        <i class="ri-attachment-2 fs-5"></i>
                    </label>
                    <input type="file" id="file-input" multiple style="display: none;" 
                           accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar,.7z">
                    
                    <input type="text" id="message-input" class="form-control bg-light border-light" placeholder="Type your message ..." autocomplete="off">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-send-plane-2-line"></i> Send
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ✅ NEW: Video Preview Modal --}}
<div class="modal fade" id="videoPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoModalTitle">Video Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <video id="modalVideoPlayer" class="w-100" controls style="max-height: 80vh; background: #000;">
                    <source src="" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-css')
<style>
/* ===== YOUR EXISTING STYLES (PRESERVED) ===== */
.chat-wrapper, .user-chat, .chat-conversation, .chat-list .user-chat-content { min-width: 0 !important; }
.page-content:has(.chat-wrapper) { padding: 70px 0 60px 0 !important; }
.chat-wrapper { display: flex !important; flex-direction: row !important; width: 100% !important; max-width: 100% !important; height: calc(100vh - 130px) !important; overflow: hidden !important; border: 0 !important; border-bottom: 1px solid var(--vz-border-color) !important; border-radius: 0 !important; }
.chat-leftsidebar { display: flex !important; flex-direction: column !important; height: 100% !important; overflow: hidden !important; width: 320px !important; flex-shrink: 0 !important; border-right: 1px solid var(--vz-border-color) !important; }
.chat-sidebar-header { border-bottom: 1px solid #eff2f7; flex-shrink: 0; }
[data-bs-theme="dark"] .chat-sidebar-header { border-bottom: 1px solid var(--vz-border-color); }
.chat-room-list { flex-grow: 1 !important; overflow-y: auto !important; overflow-x: hidden !important; }
.user-chat { display: flex !important; flex-direction: column !important; height: 100% !important; overflow: hidden !important; flex: 1 1 0 !important; width: auto !important; min-width: 0 !important; }
.user-chat-topbar { border-color: var(--vz-border-color) !important; }
.user-chat-topbar, .chat-input-section { flex-shrink: 0 !important; }
.chat-input-section { border-color: var(--vz-border-color) !important; }
.chat-conversation { flex-grow: 1 !important; overflow-y: auto !important; overflow-x: hidden !important; }
.chat-room-list::-webkit-scrollbar, .chat-conversation::-webkit-scrollbar { width: 6px; }
.chat-room-list::-webkit-scrollbar-thumb, .chat-conversation::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }
[data-bs-theme="dark"] .chat-room-list::-webkit-scrollbar-thumb, [data-bs-theme="dark"] .chat-conversation::-webkit-scrollbar-thumb { background: var(--vz-border-color); }
.user-item { transition: all 0.3s; cursor: pointer; }
.user-item:hover { background-color: #f8f9fa; }
.user-item.active { background-color: #e7f3ff; border-left: 3px solid #0d6efd; }
[data-bs-theme="dark"] .user-item:hover { background-color: var(--vz-light); }
[data-bs-theme="dark"] .user-item.active { background-color: var(--vz-primary-bg-subtle); border-left: 3px solid var(--vz-primary); }
.chat-list.right { text-align: right !important; }
.chat-list.right .conversation-list { display: inline-flex !important; flex-direction: column !important; align-items: flex-end !important; width: 100% !important; }
.chat-list.left .conversation-list { display: inline-flex !important; flex-direction: column !important; align-items: flex-start !important; width: 100% !important; }
.chat-list .user-chat-content { max-width: 80% !important; text-align: left !important; }
.ctext-wrap-content { max-width: 100% !important; width: fit-content !important; min-width: 120px !important; overflow-wrap: break-word !important; word-break: break-word !important; }
#chat-messages li { list-style: none !important; }
.chat-wrapper, .chat-leftsidebar, .user-chat, .user-chat-topbar, .chat-input-section { background: #fff !important; }
.chat-conversation { background: #f8f9fa !important; }
.chat-leftsidebar .form-control, .chat-input-section .form-control { background: #fff !important; border-color: #eff2f7 !important; }
[data-bs-theme="dark"] .chat-wrapper, [data-bs-theme="dark"] .chat-leftsidebar, [data-bs-theme="dark"] .user-chat, [data-bs-theme="dark"] .user-chat-topbar, [data-bs-theme="dark"] .chat-input-section { background: var(--vz-card-bg) !important; }
[data-bs-theme="dark"] .chat-conversation { background: var(--vz-body-bg) !important; }
[data-bs-theme="dark"] .chat-leftsidebar .form-control, [data-bs-theme="dark"] .chat-input-section .form-control { background: var(--vz-input-bg) !important; border-color: var(--vz-input-border) !important; }

/* ===== NEW: File Upload UI Styles (Dark Mode Compatible) ===== */
.file-preview-card { position: relative; width: 100px; height: 100px; border-radius: 8px; overflow: hidden; background: var(--vz-card-bg, #fff); border: 1px solid var(--vz-border-color); transition: all 0.2s; }
.file-preview-card img { width: 100%; height: 100%; object-fit: cover; }
.file-preview-card .file-icon { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--vz-light, #f8f9fa); }
.file-preview-card .file-icon i { font-size: 32px; margin-bottom: 4px; }
.file-preview-card .file-icon small { font-size: 9px; color: var(--vz-muted, #6c757d); text-align: center; padding: 0 4px; word-break: break-all; }
.file-preview-card .remove-file { position: absolute; top: 4px; right: 4px; width: 20px; height: 20px; border-radius: 50%; background: rgba(0,0,0,0.6); color: #fff; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 12px; }
.file-preview-card .remove-file:hover { background: rgba(220,53,69,0.9); }

.message-file-image { max-width: 280px; border-radius: 8px; cursor: pointer; transition: opacity 0.2s; }
.message-file-image:hover { opacity: 0.9; }
.message-file-document { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 8px; max-width: 280px; text-decoration: none; transition: all 0.2s; border: 1px solid var(--vz-border-color); background: var(--vz-card-bg, #fff); }
.message-file-document:hover { background: var(--vz-light, #f8f9fa); }
.message-file-document .doc-icon { width: 40px; height: 40px; border-radius: 8px; background: var(--vz-light, #f8f9fa); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
.message-file-document .doc-info { flex: 1; min-width: 0; }
.message-file-document .doc-name { font-weight: 500; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--vz-body-color, #495057); }
.message-file-document .doc-size { font-size: 11px; color: var(--vz-muted, #6c757d); }
</style>
@endsection

@section('script-bottom')
<script>
$(document).ready(function() {
    console.log("✅ ✅ ✅ FILE UPLOAD VERSION 1.0 LOADED ✅ ✅ ✅");

    let currentUserId = parseInt("{{ auth()->id() }}");
    let selectedUserId = null;
    let socket = null;
    let currentChannel = null;
    let socketId = null;
    let selectedFiles = []; // ✅ NEW: Store selected files

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const CHUNK_SIZE = 1024 * 1024; // ✅ 1 MB chunks

    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // ✅ NEW: Helper Functions
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024; const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    function getFileCategory(fileType) {
        if (fileType.startsWith('image/')) return 'image';
        if (fileType.startsWith('video/')) return 'video';
        return 'document';
    }

    function getFileIcon(fileType) {
        if (fileType.startsWith('image/')) return 'ri-image-line text-success';
        if (fileType.startsWith('video/')) return 'ri-video-line text-danger';
        if (fileType.includes('pdf')) return 'ri-file-pdf-line text-danger';
        if (fileType.includes('word') || fileType.includes('doc')) return 'ri-file-word-line text-primary';
        if (fileType.includes('excel') || fileType.includes('sheet')) return 'ri-file-excel-line text-success';
        if (fileType.includes('zip') || fileType.includes('rar')) return 'ri-file-zip-line text-warning';
        return 'ri-file-line text-secondary';
    }

    function connectWebSocket() {
        const wsHost = '{{ env("REVERB_HOST", "127.0.0.1") }}';
        const wsPort = '{{ env("REVERB_PORT", 8083) }}'; // ✅ Updated to 8083
        const appKey = '{{ env("REVERB_APP_KEY") }}';
        const wsUrl = `ws://${wsHost}:${wsPort}/app/${appKey}?protocol=7&client=js&version=8.2.0`;
        socket = new WebSocket(wsUrl);
        socket.onopen = function() { console.log('✅ WebSocket connected'); };
        socket.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);
                handleWebSocketMessage(data);
            } catch (e) { console.error('JSON Parse Error:', e); }
        };
        socket.onerror = function(error) { console.error('❌ WebSocket error:', error); };
        socket.onclose = function(event) { if (event.code !== 1000) setTimeout(connectWebSocket, 3000); };
    }
    
    function handleWebSocketMessage(data) {
        if (data.event === 'pusher:connection_established') {
            socketId = JSON.parse(data.data).socket_id;
        } else if (data.event === 'pusher_internal:subscription_succeeded') {
            console.log('✅ Subscribed to:', currentChannel);
        } else if (data.event === 'message.sent' || data.event === 'file.sent') { // ✅ Listen for both
            let payload = typeof data.data === 'string' ? JSON.parse(data.data) : data.data;
            if (payload.message && typeof payload.message === 'object') payload = payload.message;
            appendMessage(payload);
        }
    }
    
    function subscribeToChannel(userId) {
        if (!socketId) { setTimeout(() => subscribeToChannel(userId), 1000); return; }
        if (currentChannel) { socket.send(JSON.stringify({ event: 'pusher:unsubscribe', data: { channel: currentChannel } })); }
        const ids = [currentUserId, userId].sort((a, b) => a - b);
        currentChannel = `private-chat.${ids[0]}.${ids[1]}`;
        fetch('/broadcasting/auth', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ channel_name: currentChannel, socket_id: socketId })
        })
        .then(res => res.ok ? res.json() : Promise.reject('Auth failed'))
        .then(authData => {
            socket.send(JSON.stringify({ event: 'pusher:subscribe', data: { auth: authData.auth, channel: currentChannel } }));
        })
        .catch(err => console.error('❌ Auth failed:', err));
    }
    
    $('.user-item').on('click', function() {
        selectedUserId = $(this).data('user-id');
        $('#chat-with-user').text('Chat with ' + $(this).data('user-name'));
        $('#receiver-id').val(selectedUserId);
        $('#no-chat-selected').addClass('d-none');
        $('#chat-content-area').removeClass('d-none').addClass('d-flex');
        loadMessages(selectedUserId);
        subscribeToChannel(selectedUserId);
        $('.user-item').removeClass('active');
        $(this).addClass('active');
        $('#message-input').focus();
    });
    
    function loadMessages(userId) {
        $.ajax({
            url: '/chat/' + userId + '/messages',
            method: 'GET',
            success: function(messages) {
                $('#chat-messages').empty();
                if (messages.length === 0) {
                    $('#chat-messages').html('<div class="text-center text-muted py-4"><small>No messages yet. Start the conversation!</small></div>');
                } else {
                    messages.forEach(msg => appendMessage(msg));
                }
                scrollToBottom();
            }
        });
    }

    // ✅ NEW: File Selection & Preview Logic
    $('#file-input').on('change', function(e) {
        const files = Array.from(e.target.files);
        files.forEach(file => {
            selectedFiles.push(file);
            addFilePreview(file);
        });
        updateFilePreviewArea();
        $(this).val('');
    });

    function addFilePreview(file) {
        const fileId = Date.now() + Math.random();
        const category = getFileCategory(file.type);
        const icon = getFileIcon(file.type);
        let previewHtml = '';
        if (category === 'image') {
            previewHtml = `<div class="file-preview-card" data-file-id="${fileId}">
                <img src="${URL.createObjectURL(file)}" alt="${escapeHtml(file.name)}">
                <button type="button" class="remove-file" onclick="removeFile(${fileId})"><i class="ri-close-line"></i></button>
            </div>`;
        } else {
            previewHtml = `<div class="file-preview-card" data-file-id="${fileId}">
                <div class="file-icon"><i class="${icon}"></i><small>${escapeHtml(file.name)}</small></div>
                <button type="button" class="remove-file" onclick="removeFile(${fileId})"><i class="ri-close-line"></i></button>
            </div>`;
        }
        $('#file-preview-container').append(previewHtml);
    }

    window.removeFile = function(fileId) {
        selectedFiles = selectedFiles.filter(f => f._fileId !== fileId);
        $(`.file-preview-card[data-file-id="${fileId}"]`).remove();
        updateFilePreviewArea();
    };

    function updateFilePreviewArea() {
        if (selectedFiles.length > 0) {
            $('#file-preview-area').show();
            $('#file-count').text(selectedFiles.length);
        } else {
            $('#file-preview-area').hide();
        }
    }

    $('#clear-all-files').on('click', function() {
        selectedFiles = [];
        $('#file-preview-container').empty();
        updateFilePreviewArea();
    });

    // ✅ NEW: Chunked Upload Logic
    async function uploadFileChunked(file, receiverId, messageText) {
        const uploadId = crypto.randomUUID();
        const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
        $('#upload-progress').show();
        $('#upload-filename').text(file.name);
        
        for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
            const start = chunkIndex * CHUNK_SIZE;
            const end = Math.min(start + CHUNK_SIZE, file.size);
            const chunk = file.slice(start, end);
            const formData = new FormData();
            formData.append('upload_id', uploadId);
            formData.append('file_name', file.name);
            formData.append('chunk_index', chunkIndex);
            formData.append('total_chunks', totalChunks);
            formData.append('chunk', chunk);
            formData.append('receiver_id', receiverId);
            if (chunkIndex === totalChunks - 1) formData.append('message', messageText);
            
            try {
                const response = await fetch('/chat/upload-chunk', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: formData
                });
                const data = await response.json();
                const progress = Math.round(((chunkIndex + 1) / totalChunks) * 100);
                $('#upload-percentage').text(progress);
                $('#upload-progress-bar').css('width', progress + '%');
                if (!response.ok) throw new Error(data.message || 'Upload failed');
            } catch (error) {
                console.error('Chunk upload error:', error);
                alert('File upload failed: ' + error.message);
                $('#upload-progress').hide();
                return false;
            }
        }
        $('#upload-progress').hide();
        $('#upload-progress-bar').css('width', '0%');
        return true;
    }

    // ✅ UPDATED: Form Submit Handler
    $('#message-form').on('submit', async function(e) {
        e.preventDefault();
        const messageText = $('#message-input').val().trim();
        if (selectedFiles.length === 0 && messageText === '') return;
        
        if (selectedFiles.length > 0) {
            for (let i = 0; i < selectedFiles.length; i++) {
                const success = await uploadFileChunked(selectedFiles[i], selectedUserId, i === 0 ? messageText : '');
                if (!success) return;
            }
            selectedFiles = [];
            $('#file-preview-container').empty();
            updateFilePreviewArea();
        } else if (messageText) {
            $.ajax({
                url: '/chat', method: 'POST',
                data: { message: messageText, receiver_id: selectedUserId, _token: csrfToken },
                error: function(xhr) { console.error("Failed to send", xhr); }
            });
        }
        $('#message-input').val('');
    });
    
    // ✅ UPDATED: Message Rendering (Text + Files)
    function appendMessage(msg) {
        let isOwnMessage = (parseInt(msg.sender_id) === parseInt(currentUserId));
        let alignment = isOwnMessage ? 'text-end' : 'text-start';
        let bgColor = isOwnMessage ? 'bg-primary text-white' : 'bg-light text-body';
        
        let rawText = msg.message || msg.body || '';
        let senderName = isOwnMessage ? 'You' : (msg.sender_name || (msg.sender && msg.sender.name) || 'Unknown');
        
        let timeStr = 'Just now';
        if (msg.created_at) {
            try {
                let cleanDate = String(msg.created_at).replace(' ', 'T');
                let dateObj = new Date(cleanDate);
                if (!isNaN(dateObj.getTime())) timeStr = dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } catch (e) {}
        }
        
        let attachmentsHtml = '';
        if (msg.attachments && msg.attachments.length > 0) {
            attachmentsHtml = '<div class="mt-2 d-flex flex-column gap-2">';
            msg.attachments.forEach(att => {
                const fileUrl = '/storage/' + att.file_path;
                const downloadUrl = '/chat/download/' + att.id;
                const fileSize = formatFileSize(att.file_size);
                
                if (att.file_category === 'image') {
                    attachmentsHtml += `<div><img src="${fileUrl}" class="img-fluid rounded message-file-image" onclick="window.open('${fileUrl}', '_blank')" alt="${escapeHtml(att.file_name)}"><div class="mt-1 text-end"><a href="${downloadUrl}" class="btn btn-sm btn-light" download><i class="ri-download-line"></i> Download</a></div></div>`;
                } else if (att.file_category === 'video') {
                    attachmentsHtml += `<div><div class="message-file-video rounded overflow-hidden" style="max-width: 280px; cursor: pointer; position: relative;" onclick="openVideoModal('${fileUrl}', '${escapeHtml(att.file_name)}')"><video src="${fileUrl}" preload="metadata" class="w-100"></video><div class="position-absolute top-50 start-50 translate-middle bg-dark bg-opacity-50 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; color: #fff;"><i class="ri-play-fill fs-3"></i></div></div><div class="mt-1 d-flex justify-content-between align-items-center"><small class="text-muted text-truncate" style="max-width: 200px;">${escapeHtml(att.file_name)} • ${fileSize}</small><a href="${downloadUrl}" class="btn btn-sm btn-light" download><i class="ri-download-line"></i></a></div></div>`;
                } else {
                    attachmentsHtml += `<a href="${downloadUrl}" class="message-file-document" download><div class="doc-icon"><i class="${getFileIcon(att.file_type)}"></i></div><div class="doc-info"><div class="doc-name">${escapeHtml(att.file_name)}</div><div class="doc-size">${fileSize}</div></div><i class="ri-download-cloud-line fs-5 text-muted"></i></a>`;
                }
            });
            attachmentsHtml += '</div>';
        }
        
        let html = `<div class="mb-3 ${alignment}"><div class="d-inline-block p-3 rounded ${bgColor} ctext-wrap-content"><div class="small fw-bold mb-1">${escapeHtml(senderName)}</div>${rawText ? `<div class="mb-2">${escapeHtml(rawText)}</div>` : ''}${attachmentsHtml}<div class="small mt-2 opacity-75 text-end">${timeStr}</div></div></div>`;
        $('#chat-messages').append(html);
        scrollToBottom();
    }

    window.openVideoModal = function(videoUrl, title) {
        $('#videoModalTitle').text(title);
        $('#modalVideoPlayer source').attr('src', videoUrl);
        $('#modalVideoPlayer')[0].load();
        new bootstrap.Modal(document.getElementById('videoPreviewModal')).show();
    };

    $('#videoPreviewModal').on('hidden.bs.modal', function() {
        $('#modalVideoPlayer')[0].pause();
        $('#modalVideoPlayer')[0].currentTime = 0;
    });
    
    function scrollToBottom() {
        let chatBox = document.getElementById('chat-messages');
        if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
    }
    
    $('#user-search').on('input', function() {
        let query = $(this).val().toLowerCase();
        $('.user-item').each(function() {
            $(this).toggle($(this).data('user-name').toLowerCase().includes(query));
        });
    });

    connectWebSocket();
});
</script>
@endsection