@extends('backend.app')

@section('title', 'Chat')


@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Chat</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Apps</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Chat</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- Chat Container -->
                <div class="chat-container">
                    <!-- Sidebar -->
                    <div class="chat-sidebar">
                        <div class="sidebar-header">
                            <h3><i class="bi bi-chat-dots"></i> Messages</h3>
                            <div class="search-container">
                                <input name="keyword" type="text" id="keyword" class="search-input"
                                    placeholder="Search conversations...">
                                <div class="search-actions">
                                    <button type="button" class="search-btn" onclick="userSearch();">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                    <button type="button" class="refresh-btn" onclick="userList();">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="user-list" id="userList">
                            <!-- Users will be populated here -->
                        </div>
                    </div>

                    <!-- Main Chat Area -->
                    <div class="main-chat">
                        <!-- Welcome Screen -->
                        <div class="welcome-screen" id="welcomeScreen">
                            <div class="welcome-icon">
                                <i class="bi bi-chat-heart"></i>
                            </div>
                            <div class="welcome-text">Welcome to Chat</div>
                            <div class="welcome-subtext">Select a conversation to start messaging</div>
                        </div>

                        <!-- Chat Box -->
                        <div class="main-content-body main-content-body-chat d-none" id="ChatBox">
                            <!-- Chat Header -->
                            <div class="chat-header">
                                <div class="chat-header-avatar" id="ReceiverImage">
                                    <img src="{{ asset('default/default_image.jpg') }}" alt="User">
                                </div>
                                <div class="chat-header-info">
                                    <h3 id="ReceiverName" onclick="userChat($('#ReceiverId').val());"
                                        style="cursor: pointer;">User</h3>
                                    <p id="ReceiverRoll">Roll</p>
                                </div>
                                <div class="chat-actions">
                                    <button class="action-btn" onclick="formClear()">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>

                                    <div class="tooltip-container">
                                        <button class="action-btn" id="deleteBtn">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <span class="tooltip-text" id="deleteTooltip"
                                            onclick="confirmDeleteConversation($('#ReceiverId').val());">Delete
                                            Conversation</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Chat Messages -->
                            <div class="chat-messages" id="ChatContent">
                                <!-- Messages will be populated here -->
                            </div>

                            <!-- Chat Input -->
                            <div class="chat-input">
                                <div class="input-container">
                                    <input class="message-input" placeholder="Type your message here..." type="text"
                                        id="Text">
                                    <label for="File" id="FileLabel" class="file-input-label">
                                        <i class="bi bi-image"></i>
                                    </label>
                                    <input type="file" id="File" style="display: none;"
                                        accept=".jpg,.jpeg,.png,.gif">
                                    <input type="text" style="display: none;" id="ReceiverId" />
                                    <input type="text" style="display: none;" id="RoomId" />
                                </div>
                                <button type="button" class="send-btn" onclick="sendMessage($('#ReceiverId').val())">
                                    <i class="bi bi-send"></i>
                                </button>
                                <button type="button" class="clear-btn" onclick="formClear()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/dayjs/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs/plugin/relativeTime.js"></script>
    <script>
        // Initialize dayjs relativeTime plugin
        if (typeof dayjs !== 'undefined' && dayjs.extend) {
            dayjs.extend(window.dayjs_plugin_relativeTime);
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>

    {{-- Global Variables --}}
    <script>
        let currentReceiverId = null;
        let currentReceiverType = null;
        let currentRoomId = null;
        let typingTimeout = null;
        let selectedMessages = new Set();
        const USER_ID = {{ auth('web')->user()->id }};
        const CSRF_TOKEN = "{{ csrf_token() }}";
    </script>

    <script>
        // ============================================
        // User List Functions
        // ============================================
        function userList() {
            console.log('Loading user list...');
            NProgress.start();

            $.ajax({
                url: `{{ route('chat.list') }}`,
                type: "GET",
                success: function(response) {
                    console.log('User list loaded:', response);
                    NProgress.done();
                    $('#userList').empty();

                    if (!response.data || !response.data.users || response.data.users.length === 0) {
                        $('#userList').append(
                            '<div style="padding: 20px; text-align: center; color: #999;">No conversations yet</div>'
                        );
                        return;
                    }

                    $.each(response.data.users, function(index, user) {
                        let avatar = user.display_avatar || user.avatar ||
                            "{{ asset('default/profile.jpg') }}";
                        let guestTag = user.is_guest ? '<span class="guest-tag">Guest</span>' : '';
                        let userName = user.display_name || user.name || 'Guest User';
                        let userType = user.user_type || 'App\\Models\\User';

                        let escapedUserType = userType.replace(/\\/g, '\\\\');

                        let lastMessage = 'No messages yet';
                        if (user.last_chat && user.last_chat.text) {
                            lastMessage = user.last_chat.text.substring(0, 30);
                            if (user.last_chat.text.length > 30) {
                                lastMessage += '...';
                            }
                        }

                        let timeAgo = '';
                        if (user.last_chat && user.last_chat.created_at) {
                            timeAgo = dayjs(user.last_chat.created_at).fromNow();
                        }

                        $('#userList').append(`
                            <a class="user-item" href="javascript:void(0)"
                               onclick="userChat(${user.id}, '${escapedUserType}')"
                               id="selectUser${user.id}"
                               data-user-type="${userType}">
                                <div class="user-avatar">
                                    <img alt="avatar" src="${avatar}">
                                </div>
                                <div class="user-info">
                                    <div class="user-name">
                                        ${userName} ${guestTag}
                                    </div>
                                    <div class="user-message">
                                        ${lastMessage}
                                    </div>
                                </div>
                                <div class="user-meta">
                                    <div class="user-time">
                                        ${timeAgo}
                                    </div>
                                    ${user.unread_count > 0 ? `<span class="unread-badge">${user.unread_count}</span>` : ''}
                                </div>
                            </a>
                        `);
                    });

                    console.log('User list rendered:', response.data.users.length, 'users');
                },
                error: function(xhr) {
                    console.error('Error loading users:', xhr);
                    NProgress.done();
                    toastr.error('Failed to load conversations');
                }
            });
        }

        // ============================================
        // Search Function
        // ============================================
        function userSearch() {
            let keyword = $('#keyword').val().trim();

            if (!keyword) {
                userList();
                return;
            }

            NProgress.start();
            $.ajax({
                url: `{{ route('chat.search') }}?keyword=${keyword}`,
                type: "GET",
                success: function(response) {
                    NProgress.done();
                    $('#userList').empty();

                    $.each(response.data.users, function(index, user) {
                        let avatar = user.avatar || "{{ asset('default/profile.jpg') }}";
                        let guestTag = user.is_guest ? '<span class="guest-tag">👤 Guest</span>' : '';
                        let userType = user.is_guest ? 'App\\\\Models\\\\GuestUser' :
                            'App\\\\Models\\\\User';

                        $('#userList').append(`
                            <a class="user-item" href="javascript:void(0)"
                               onclick="userChat(${user.id}, '${userType}')"
                               id="selectUser${user.id}">
                                <div class="user-avatar">
                                    <img alt="avatar" src="${avatar}">
                                </div>
                                <div class="user-info">
                                    <div class="user-name">${user.name} ${guestTag}</div>
                                    <div class="user-message">${user.email}</div>
                                </div>
                            </a>
                        `);
                    });
                },
                error: function(xhr) {
                    console.error('Error searching:', xhr);
                    NProgress.done();
                }
            });
        }

        // ============================================
        // Load Conversation
        // ============================================
        function userChat(receiver_id, receiver_type) {
            console.log('Loading conversation:', receiver_id, receiver_type);
            NProgress.start();
            currentReceiverId = receiver_id;
            currentReceiverType = receiver_type;
            selectedMessages.clear();

            $.ajax({
                url: `{{ url('admin/chat/conversation') }}/${receiver_id}`,
                type: "GET",
                data: {
                    receiver_type: receiver_type
                },
                success: function(response) {
                    console.log('Conversation loaded:', response);
                    NProgress.done();
                    renderConversation(response.data);
                    markAllAsSeen(receiver_id, receiver_type);
                },
                error: function(xhr) {
                    console.error('Error loading conversation:', xhr);
                    NProgress.done();
                    toastr.error('Failed to load conversation');
                }
            });
        }

        // ============================================
        // Render Conversation
        // ============================================
        function renderConversation(data) {
            $('#ChatContent').empty();
            $('#ReceiverId').val(data.receiver.id);
            $('#ReceiverName').text(data.receiver.name);
            $('#ReceiverRoll').text(data.receiver.role || 'Guest');
            $('#RoomId').val(data.room.id);
            currentRoomId = data.room.id;
            currentReceiverType = data.receiver.user_type;

            window.sessionStorage.setItem('room_id', data.room.id);

            $('#welcomeScreen').hide();
            $('#ChatBox').removeClass('d-none');

            $('.user-item').removeClass('selected');
            $('#selectUser' + data.receiver.id).addClass('selected');

            let receiverAvatar = data.receiver.avatar || "{{ asset('default/profile.jpg') }}";
            let senderAvatar = data.sender.avatar || "{{ asset('default/profile.jpg') }}";

            $('#ReceiverImage').html(`<img alt="avatar" src="${receiverAvatar}">`);

            if (data.chat && data.chat.length > 0) {
                data.chat.forEach(chat => {
                    appendMessage(chat, senderAvatar, receiverAvatar);
                });
            } else {
                $('#ChatContent').append(
                    '<div style="text-align: center; padding: 20px; color: #999;">No messages yet. Start the conversation!</div>'
                );
            }

            scrollToBottom();
        }

        // ============================================
        // Append Message
        // ============================================
        function appendMessage(chat, senderAvatar, receiverAvatar) {
            let isSender = chat.sender_id == USER_ID && chat.sender_type == 'App\\Models\\User';
            let chatClass = isSender ? 'message chat-right' : 'message chat-left';
            let avatar = isSender ? senderAvatar : receiverAvatar;

            let statusIcon = '';
            if (isSender) {
                if (chat.status === 'read') {
                    statusIcon = '<i class="bi bi-check-all text-primary"></i>';
                } else if (chat.status === 'delivered') {
                    statusIcon = '<i class="bi bi-check-all"></i>';
                } else {
                    statusIcon = '<i class="bi bi-check"></i>';
                }
            }

            let editedTag = chat.is_edited ? '<span class="edited-tag">(edited)</span>' : '';
            let messageContent = chat.text ? `<div class="message-bubble">${chat.text} ${editedTag}</div>` : '';

            let contextMenu = isSender ? `
                <div class="message-context" onclick="showMessageMenu(${chat.id}, event)">
                    <i class="bi bi-three-dots-vertical"></i>
                </div>
            ` : '';

            $('#ChatContent').append(`
                <div class="${chatClass}" data-message-id="${chat.id}">
                    <div class="message-avatar">
                        <img alt="avatar" src="${avatar}">
                    </div>
                    <div class="message-content">
                        ${messageContent}
                        <div class="message-time">
                            ${chat.humanize_date} ${statusIcon}
                        </div>
                    </div>
                    ${contextMenu}
                </div>
            `);
        }

        // ============================================
        // Send Message
        // ============================================
        function sendMessage(receiver_id) {
            let text = $('#Text').val().trim();

            if (!text) {
                toastr.warning('Please enter a message');
                return;
            }

            if (!currentReceiverType) {
                toastr.error('Receiver type not found. Please select a conversation first.');
                return;
            }

            NProgress.start();

            $.ajax({
                url: `{{ url('admin/chat/send') }}/${receiver_id}`,
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                data: {
                    text: text,
                    receiver_type: currentReceiverType
                },
                success: function(response) {
                    NProgress.done();
                    $('#Text').val('');
                    userChat(receiver_id, currentReceiverType);
                    userList();
                    toastr.success('Message sent successfully!');
                },
                error: function(xhr) {
                    console.error('Error sending message:', xhr);
                    NProgress.done();
                    toastr.error(xhr.responseJSON?.message || 'Failed to send message');
                }
            });
        }

        // ============================================
        // Typing Indicator
        // ============================================
        $('#Text').on('input', function() {
            if (!currentRoomId) return;
            clearTimeout(typingTimeout);
            sendTypingStatus(true);
            typingTimeout = setTimeout(() => {
                sendTypingStatus(false);
            }, 3000);
        });

        function sendTypingStatus(isTyping) {
            if (!currentRoomId) return;

            $.ajax({
                url: `{{ route('chat.typing') }}`,
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                data: {
                    room_id: currentRoomId,
                    is_typing: isTyping
                }
            });
        }

        // ============================================
        // Mark as Seen
        // ============================================
        function markAllAsSeen(receiverId, receiverType) {
            $.ajax({
                url: `{{ url('admin/chat/seen/all') }}/${receiverId}`,
                type: "GET",
                data: {
                    receiver_type: receiverType
                },
                success: function() {
                    userList();
                }
            });
        }

        // ============================================
        // Helper Functions
        // ============================================
        function scrollToBottom() {
            let chatContent = $('#ChatContent');
            chatContent.scrollTop(chatContent[0].scrollHeight);
        }

        function formClear() {
            $('#Text').val('');
            NProgress.done();
        }

        // ============================================
        // Event Listeners
        // ============================================
        $(document).on('keypress', '#Text', function(e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                if (currentReceiverId) {
                    sendMessage(currentReceiverId);
                }
            }
        });

        $(document).on('keypress', '#keyword', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                userSearch();
            }
        });

        function updateMessageStatus(messageId, status) {
            let messageDiv = $(`.message[data-message-id="${messageId}"]`);
            let statusIcon = messageDiv.find('.message-time i');

            if (status === 'read') {
                statusIcon.removeClass().addClass('bi bi-check-all text-primary');
            } else if (status === 'delivered') {
                statusIcon.removeClass().addClass('bi bi-check-all');
            }
        }

        // ============================================
        // CRITICAL: Multiple initialization methods
        // ============================================
        $(document).ready(function() {
            console.log('Initializing user list...');
            userList();
        });

        // Method 2: Window load (backup)
        $(window).on('load', function() {
            console.log('Chat initialized (window load). User ID:', USER_ID);
            // Only call if not already called
            if ($('#userList').children().length === 0) {
                userList();
            }
        });

        // Method 3: Immediate execution (last resort)
        setTimeout(function() {
            console.log('Chat initialized (timeout). User ID:', USER_ID);
            // Only call if not already called
            if ($('#userList').children().length === 0) {
                userList();
            }
        }, 500);

        // Auto-refresh every 5 minutes
        // setInterval(() => {
        //     console.log('Auto-refreshing user list...');
        //     userList();
        // }, 300000);

        // ============================================
        // Real-time Updates (Laravel Echo)
        // ============================================
        // if (USER_ID && typeof Echo !== 'undefined') {
        //     Echo.private(`chat-receiver.${USER_ID}`)
        //         .listen('MessageSendEvent', function(e) {
        //             console.log('New message received:', e);
        //             toastr.info('New message received');

        //             if (currentReceiverId == e.data.sender_id && currentReceiverType == e.data.sender_type) {
        //                 userChat(currentReceiverId, currentReceiverType);
        //             }

        //             userList();
        //         });

        //     Echo.private(`chat-sender.${USER_ID}`)
        //         .listen('MessageStatusEvent', function(e) {
        //             console.log('Status update:', e);
        //             updateMessageStatus(e.message_id, e.status);
        //         });
        // }



        var user_id = `{{ auth('web')->check() ? auth('web')->user()->id : null }}`;
        console.log('User ID:', user_id);

        if (user_id) {
            document.addEventListener('DOMContentLoaded', function() {
                Echo.private(`chat-receiver.${user_id}`)
                    .listen('MessageSendEvent', function(e) {
                        console.log('Received event:', e); // Debugging
                        toastr.success(e.data.text ?? "New file received");
                        let receiver_id = document.getElementById('ReceiverId').value;
                        if (receiver_id) {
                            userChat(receiver_id);
                            userList();
                        }
                    });
            });
        }
    </script>
@endpush


@push('styles')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .chat-container {
            display: flex;
            height: 85vh;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
        }

        /* Sidebar */
        .chat-sidebar {
            width: 350px;
            background: #fff;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header {
            padding: 20px;
            background: #521aac;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }


        .sidebar-header h3 {
            color: white;
            font-size: 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-container {
            position: relative;
            margin-bottom: 10px;
        }

        .search-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #fff;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 14px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
        }

        .search-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .search-btn,
        .refresh-btn {
            flex: 1;
            padding: 8px 15px;
            border: none;
            border-radius: 20px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
        }

        .search-btn {
            background: linear-gradient(45deg, #55c7d9, #55c7d9);
        }

        .refresh-btn {
            background: linear-gradient(45deg, #95a5a6, #7f8c8d);
        }

        .search-btn:hover,
        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* User List */
        .user-list {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
            /* scrollbar-color: rgba(14, 7, 7, 0.966) transparent; */
            scrollbar-color: #3498db4d transparent;
            max-height: calc(100vh - 150px);
            padding-right: 5px;
        }

        .user-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
            text-decoration: none;
            color: white;
        }

        .user-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
            color: white;
            text-decoration: none;
        }

        .user-item.selected {
            background: linear-gradient(90deg, rgba(52, 152, 219, 0.3), rgba(41, 128, 185, 0.3));
            border-left: 4px solid #55c7d9;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .online-indicator {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid white;
        }

        .online-indicator.online {
            background: #27ae60;
            animation: pulse 2s infinite;
        }

        .online-indicator.offline {
            background: #e74c3c;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(39, 174, 96, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(39, 174, 96, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(39, 174, 96, 0);
            }
        }

        .user-info {
            flex: 1;
            color: rgb(26, 24, 24);
        }

        .user-name {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .user-message {
            font-size: 13px;
            color: rgb(26, 24, 24);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
        }

        .user-time {
            font-size: 12px;
            color: rgba(26, 25, 25, 0.5);
            position: absolute;
            top: 15px;
            right: 15px;
        }

        /* Main Chat Area */
        .main-chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header {
            padding: 20px 25px;
            background: linear-gradient(90deg, #f8f9fa, #e9ecef);
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .chat-header-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #55c7d9;
        }

        .chat-header-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .chat-header-info h3 {
            margin: 0;
            color: #2c3e50;
            font-size: 18px;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .chat-header-info h3:hover {
            color: #55c7d9;
        }

        .chat-header-info p {
            margin: 0;
            color: #7f8c8d;
            font-size: 14px;
        }

        .chat-actions {
            margin-left: auto;
            display: flex;
            gap: 10px;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            background: linear-gradient(45deg, #55c7d9, #55c7d9);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        /* Tooltrip */
        .tooltip-container {
            position: relative;
            display: inline-block;
        }

        .tooltip-text {
            display: none;
            /* hidden by default */
            position: absolute;
            top: 120%;
            /* show under button */
            left: 50%;
            transform: translateX(-80%);
            background: #333;
            color: #fff;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 999;
            cursor: pointer;
        }

        .tooltip-text::after {
            content: "";
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%);
            border-width: 5px;
            border-style: solid;
            border-color: transparent transparent #333 transparent;
        }

        .tooltip-text.show {
            display: block;
        }


        .main-content-body-chat {
            display: flex;
            flex-direction: column;
            height: 100%;
        }


        /* Chat Messages */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            scrollbar-width: thin;
            scrollbar-color: #3498db4d transparent;
            max-height: 635px;
        }

        .message {
            display: flex;
            margin-bottom: 20px;
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.chat-right {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin: 0 10px;
            overflow: hidden;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .message-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .message-content {
            max-width: 70%;
            display: flex;
            flex-direction: column;
        }

        .message-bubble {
            padding: 12px 18px;
            border-radius: 20px;
            margin-bottom: 5px;
            position: relative;
            word-wrap: break-word;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .message.chat-left .message-bubble {
            background: white;
            color: #2c3e50;
            border-bottom-left-radius: 5px;
        }

        .message.chat-right .message-bubble {
            background: linear-gradient(45deg, #55c7d9, #55c7d9);
            color: white;
            border-bottom-right-radius: 5px;
        }

        .message-time {
            font-size: 11px;
            color: #95a5a6;
            align-self: flex-end;
            margin-top: 2px;
        }

        .message.chat-right .message-time {
            align-self: flex-start;
        }

        .message-image {
            max-width: 250px;
            border-radius: 10px;
            margin-top: 8px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .message-image:hover {
            transform: scale(1.05);
        }

        /* Chat Input */
        .chat-input {
            position: sticky;
            padding: 20px 25px;
            background: white;
            border-top: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            gap: 15px;
            border-left: 1px solid #3498db4d !important;
            bottom: 0;
            z-index: 10;
        }

        .input-container {
            flex: 1;
            position: relative;
        }

        .message-input {
            width: 100%;
            padding: 12px 50px 12px 15px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .message-input:focus {
            border-color: #55c7d9;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            background: white;
        }

        .file-input-label {
            position: absolute;
            right: 50px;
            top: 50%;
            transform: translateY(-50%);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #55c7d9;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .file-input-label:hover {
            background: #55c7d9;
            transform: translateY(-50%) scale(1.1);
        }

        .file-input-label.has-file {
            background: #27ae60;
        }

        .send-btn,
        .clear-btn {
            width: 45px;
            height: 45px;
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .send-btn {
            background: linear-gradient(45deg, #55c7d9, #55c7d9);
        }

        .clear-btn {
            background: linear-gradient(45deg, #95a5a6, #7f8c8d);
        }

        .send-btn:hover,
        .clear-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Welcome Screen */
        .welcome-screen {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #521aac28 100%);
            color: #7f8c8d;
        }

        .welcome-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .welcome-text {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .welcome-subtext {
            font-size: 16px;
            opacity: 0.7;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .chat-container {
                flex-direction: column;
                height: 100vh;
                border-radius: 0;
            }

            .chat-sidebar {
                width: 100%;
                height: 40%;
            }

            .main-chat {
                height: 60%;
            }

            .message-content {
                max-width: 85%;
            }
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(52, 152, 219, 0.3);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(52, 152, 219, 0.5);
        }


        /* Message Context Menu */
        .message-context {
            position: absolute;
            right: 10px;
            top: 10px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .message:hover .message-context {
            opacity: 1;
        }

        .message-context:hover {
            background: rgba(0, 0, 0, 0.1);
        }

        .message-menu {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            min-width: 150px;
        }

        .menu-item {
            padding: 12px 16px;
            cursor: pointer;
            transition: background 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .menu-item:hover {
            background: #f8f9fa;
        }

        .menu-item.text-danger:hover {
            background: #fee;
            color: #dc3545;
        }

        /* Message Status Icons */
        .message-time i {
            font-size: 12px;
            margin-left: 5px;
        }

        .message-time .bi-check {
            color: #95a5a6;
        }

        .message-time .bi-check-all {
            color: #95a5a6;
        }

        .message-time .bi-check-all.text-primary {
            color: #3498db !important;
        }

        /* Edited Tag */
        .edited-tag {
            font-size: 11px;
            color: #95a5a6;
            font-style: italic;
            margin-left: 5px;
        }

        /* Unread Badge */
        .unread-badge {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: bold;
            min-width: 20px;
            text-align: center;
            display: inline-block;
        }

        /* Guest Tag */
        .guest-tag {
            background: linear-gradient(45deg, #f39c12, #e67e22);
            color: white;
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 600;
            margin-left: 5px;
            display: inline-block;
        }

        /* User Meta */
        .user-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 5px;
            position: absolute;
            top: 15px;
            right: 15px;
        }

        /* Typing Indicator */
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 8px 12px;
            background: rgba(52, 152, 219, 0.1);
            border-radius: 15px;
            width: fit-content;
        }

        .typing-indicator span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #55c7d9;
            animation: typing 1.4s infinite;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {

            0%,
            60%,
            100% {
                transform: translateY(0);
                opacity: 0.7;
            }

            30% {
                transform: translateY(-10px);
                opacity: 1;
            }
        }

        /* Message Selection */
        .message.selected {
            background: rgba(52, 152, 219, 0.1);
            border-left: 3px solid #55c7d9;
        }

        /* Enhanced Message Bubble */
        .message-bubble {
            position: relative;
            animation: messageSlide 0.3s ease-out;
        }

        @keyframes messageSlide {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loading State */
        .message-loading {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            background: rgba(52, 152, 219, 0.1);
            border-radius: 15px;
            width: fit-content;
        }

        .message-loading .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(52, 152, 219, 0.3);
            border-top-color: #55c7d9;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Enhanced User Item */
        .user-item {
            position: relative;
            padding-right: 60px;
            /* Make room for meta info */
        }

        /* Connection Status */
        .connection-status {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 8px 16px;
            border-radius: 20px;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            z-index: 1000;
        }

        .connection-status.online {
            color: #27ae60;
        }

        .connection-status.offline {
            color: #e74c3c;
        }

        .connection-status .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .message-context {
                opacity: 1;
                /* Always visible on mobile */
            }

            .user-meta {
                position: static;
                margin-top: 5px;
            }

            .user-item {
                padding-right: 20px;
            }
        }
    </style>
@endpush
