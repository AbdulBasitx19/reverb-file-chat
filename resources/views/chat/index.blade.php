@extends('layouts.master')

@section('title', 'Chat System')

@section('content')
<div class="chat-wrapper" style="height: calc(100vh - 190px); min-height: 450px;">    
    {{-- ===== LEFT SIDEBAR (Users List) ===== --}}
<div class="chat-leftsidebar minimal-border" style="border-right: 1px solid var(--vz-border-color) ">        <div class="chat-sidebar-header px-4 pt-4 pb-3">
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
                <h5 class="card-title mb-0 " id="chat-with-user">Select a user to start chatting</h5>
            </div>
            
            <div class="chat-conversation p-3 p-lg-4 flex-grow-1 overflow-auto" id="chat-messages" style="background: var(--vz-body-bg);"> 
                <!-- Messages will be appended here -->
            </div>
            
            <div class="chat-input-section p-3 border-top"> 
                <form id="message-form" class="d-flex align-items-center gap-2">
                    <input type="hidden" id="receiver-id" value=""> 
                    <input type="text" id="message-input" class="form-control bg-light border-light" placeholder="Type your message ..." autocomplete="off">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-send-plane-2-line"></i> Send
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-css')
<style>
/* Prevents horizontal scrolling in flex containers */
.chat-wrapper, .user-chat, .chat-conversation, .chat-list .user-chat-content {
    min-width: 0 !important;
}

/* Sirf is page par Velzon ki padding hatao */
.page-content:has(.chat-wrapper) {
    padding: 70px 0 60px 0 !important;  /* top = topbar, bottom = footer */
}

.chat-wrapper {
    display: flex !important;
    flex-direction: row !important;
    width: 100% !important;
    max-width: 100% !important;
    height: calc(100vh - 130px) !important;  /* 70px topbar + 60px footer */
    overflow: hidden !important;
    border: 0 !important;
    border-bottom: 1px solid var(--vz-border-color) !important;
    border-radius: 0 !important;
}

.chat-leftsidebar {
    display: flex !important;
    flex-direction: column !important;
    height: 100% !important;
    overflow: hidden !important;
    width: 320px !important;
    flex-shrink: 0 !important;
    border-right: 1px solid var(--vz-border-color) !important;
}

.chat-sidebar-header {
    border-bottom: 1px solid #eff2f7;
    flex-shrink: 0;
}
[data-bs-theme="dark"] .chat-sidebar-header {
    border-bottom: 1px solid var(--vz-border-color);
}

.chat-room-list {
    flex-grow: 1 !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
}

.user-chat {
    display: flex !important;
    flex-direction: column !important;
    height: 100% !important;
    overflow: hidden !important;
    flex: 1 1 0 !important;
    width: auto !important;
    min-width: 0 !important;
}

.user-chat-topbar {
    border-color: var(--vz-border-color) !important;
}

.user-chat-topbar, .chat-input-section {
    flex-shrink: 0 !important;
}

.chat-input-section {
    border-color: var(--vz-border-color) !important;
}

.chat-conversation {
    flex-grow: 1 !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
}

/* Scrollbar */
.chat-room-list::-webkit-scrollbar, .chat-conversation::-webkit-scrollbar { width: 6px; }
.chat-room-list::-webkit-scrollbar-thumb, .chat-conversation::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}
[data-bs-theme="dark"] .chat-room-list::-webkit-scrollbar-thumb,
[data-bs-theme="dark"] .chat-conversation::-webkit-scrollbar-thumb {
    background: var(--vz-border-color);
}

/* Users list */
.user-item { transition: all 0.3s; cursor: pointer; }
.user-item:hover { background-color: #f8f9fa; }
.user-item.active { background-color: #e7f3ff; border-left: 3px solid #0d6efd; }
[data-bs-theme="dark"] .user-item:hover { background-color: var(--vz-light); }
[data-bs-theme="dark"] .user-item.active {
    background-color: var(--vz-primary-bg-subtle);
    border-left: 3px solid var(--vz-primary);
}

/* Message bubble styling */
.chat-list.right { text-align: right !important; }
.chat-list.right .conversation-list {
    display: inline-flex !important;
    flex-direction: column !important;
    align-items: flex-end !important;
    width: 100% !important;
}
.chat-list.left .conversation-list {
    display: inline-flex !important;
    flex-direction: column !important;
    align-items: flex-start !important;
    width: 100% !important;
}
.chat-list .user-chat-content {
    max-width: 80% !important;
    text-align: left !important;
}

/* Long words/URLs break karo */
.ctext-wrap-content {
    max-width: 100% !important;
    width: fit-content !important;
    min-width: 120px !important;
    overflow-wrap: break-word !important;
    word-break: break-word !important;
}

#chat-messages li { list-style: none !important; }

/* ===== Default (light) look ===== */
.chat-wrapper,
.chat-leftsidebar,
.user-chat,
.user-chat-topbar,
.chat-input-section { background: #fff !important; }

.chat-conversation { background: #f8f9fa !important; }

.chat-leftsidebar .form-control,
.chat-input-section .form-control {
    background: #fff !important;
    border-color: #eff2f7 !important;
}

/* ===== Dark mode: theme ke hisab se ===== */
[data-bs-theme="dark"] .chat-wrapper,
[data-bs-theme="dark"] .chat-leftsidebar,
[data-bs-theme="dark"] .user-chat,
[data-bs-theme="dark"] .user-chat-topbar,
[data-bs-theme="dark"] .chat-input-section { background: var(--vz-card-bg) !important; }

[data-bs-theme="dark"] .chat-conversation { background: var(--vz-body-bg) !important; }

[data-bs-theme="dark"] .chat-leftsidebar .form-control,
[data-bs-theme="dark"] .chat-input-section .form-control {
    background: var(--vz-input-bg) !important;
    border-color: var(--vz-input-border) !important;
}
</style>
@endsection

@section('script-bottom')
<script>
$(document).ready(function() {
    // ✅ YEH LINE ADD KAREIN: Agar yeh console mein nahi dikha, matlab browser purana code chala raha hai
    console.log("✅ ✅ ✅ NEW CODE VERSION 3.0 LOADED SUCCESSFULLY ✅ ✅ ✅");

    let currentUserId = parseInt("{{ auth()->id() }}");
    let selectedUserId = null;
    let socket = null;
    let currentChannel = null;
    let socketId = null;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ✅ CRITICAL FIX: XSS Protection (Prevents UI breaking on special characters)
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function connectWebSocket() {
        const wsHost = '{{ env("REVERB_HOST", "127.0.0.1") }}';
        const wsPort = '{{ env("REVERB_PORT", 8080) }}'; 
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
        
        socket.onclose = function(event) {
            if (event.code !== 1000) setTimeout(connectWebSocket, 3000);
        };
    }
    
    function handleWebSocketMessage(data) {
    if (data.event === 'pusher:connection_established') {
        socketId = JSON.parse(data.data).socket_id;
    } else if (data.event === 'pusher_internal:subscription_succeeded') {
        console.log('✅ Subscribed to:', currentChannel);
    } else if (data.event === 'message.sent') {
        // ✅ CRITICAL FIX: data.data string hota hai, pehle parse karein
        let payload = typeof data.data === 'string' ? JSON.parse(data.data) : data.data;

        // Agar event ne {message: {...}} wrap kiya ho to unwrap karein
        if (payload.message && typeof payload.message === 'object') {
            payload = payload.message;
        }

        console.log("🔍 PARSED PAYLOAD:", payload);
        appendMessage(payload);
    }
    }
    
    function subscribeToChannel(userId) {
        if (!socketId) {
            setTimeout(() => subscribeToChannel(userId), 1000);
            return;
        }
        if (currentChannel) {
            socket.send(JSON.stringify({ event: 'pusher:unsubscribe', data: { channel: currentChannel } }));
        }
        const ids = [currentUserId, userId].sort((a, b) => a - b);
        currentChannel = `private-chat.${ids[0]}.${ids[1]}`;
        
        fetch('/broadcasting/auth', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ channel_name: currentChannel, socket_id: socketId })
        })
        .then(res => res.ok ? res.json() : Promise.reject('Auth failed'))
        .then(authData => {
            socket.send(JSON.stringify({
                event: 'pusher:subscribe',
                data: { auth: authData.auth, channel: currentChannel }
            }));
        })
        .catch(err => console.error('❌ Auth failed:', err));
    }
    
    $('.user-item').on('click', function() {
        selectedUserId = $(this).data('user-id');
        $('#chat-with-user').text('Chat with ' + $(this).data('user-name'));
        $('#receiver-id').val(selectedUserId);
        
        // ✅ CRITICAL FIX: Show chat area, hide empty state
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
    
    $('#message-form').on('submit', function(e) {
        e.preventDefault();
        let messageText = $('#message-input').val().trim();
        if (messageText === '') return;
        
        $.ajax({
            url: '/chat',
            method: 'POST',
            data: { message: messageText, receiver_id: selectedUserId, _token: csrfToken },
            success: function() { $('#message-input').val(''); },
            error: function(xhr) { console.error("Failed to send", xhr); }
        });
    });
    
      // ✅ CRITICAL FIX: Bulletproof Message Rendering
       // ✅ ABSOLUTE FINAL & FOOLPROOF MESSAGE RENDERING
    function appendMessage(msg) {
        // Yeh console mein dikhayega ke browser ko exactly kya data mila
        console.log("🔍 RAW MESSAGE DATA:", msg); 

        let isOwnMessage = (msg.sender_id == currentUserId);
        let alignment = isOwnMessage ? 'text-end' : 'text-start';
        let bgColor = isOwnMessage ? 'bg-primary text-white' : 'bg-light text-body';
        
        // 1. Text ko safely nikalein
        let rawText = msg.message || msg.body || '';
        
        // 2. Sender Name ko safely nikalein (Priority order ke sath)
        let senderName = 'Unknown';
        if (isOwnMessage) {
            senderName = 'You'; // Agar apna message hai toh hamesha 'You'
        } else if (msg.sender_name) {
            senderName = msg.sender_name; // Event se aaya hua naam
        } else if (msg.sender && msg.sender.name) {
            senderName = msg.sender.name; // AJAX history se aaya hua naam
        }
        
        // 3. Time ko safely parse karein (Safari/Chrome compatible)
        let timeStr = 'Just now';
        if (msg.created_at) {
            try {
                // Space ko 'T' se replace karna zaroori hai universal compatibility ke liye
                let cleanDate = String(msg.created_at).replace(' ', 'T');
                let dateObj = new Date(cleanDate);
                
                // Check karein ke date valid hai ya nahi
                if (!isNaN(dateObj.getTime())) {
                    timeStr = dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }
            } catch (e) {
                console.warn("Date parse error, using fallback", e);
            }
        }
        
        let html = `
            <div class="mb-3 ${alignment}">
                <div class="d-inline-block p-3 rounded ${bgColor} ctext-wrap-content">
                    <div class="small fw-bold mb-1">${escapeHtml(senderName)}</div>
                    <div class="mb-0">${escapeHtml(rawText)}</div>
                    <div class="small mt-1 opacity-75 text-end">${timeStr}</div>
                </div>
            </div>
        `;
        
        $('#chat-messages').append(html);
        scrollToBottom();
    }
    
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