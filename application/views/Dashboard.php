<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatapp</title>
    <script src="https://cdn.socket.io/4.8.1/socket.io.min.js"></script>
        <link rel="stylesheet" href="<?php echo base_url("assets/dashboardstyles.css")?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
</head>
<body>
    <div class="chat-app">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
             
                <div class="current-user-info">
                    <div class="user-avatar">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($username); ?>&background=6366f1&color=fff&bold=true" alt="Profile" id="my-avatar">
                        <div class="user-status online" id="my-status"></div>
                    </div>
                    <div class="user-details">
                        <h3 id="my-username"><?php echo htmlspecialchars($username); ?></h3>
                        <p id="my-user-id">Connecting...</p>
                    </div>
                </div>
                <a href="<?= site_url('AuthController/Logout') ?>" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
            
            <div class="search-create-container">
                <input type="text" class="search-box" placeholder="Search chats..." id="search-users">
                <button id="create-group-btn" class="create-group-btn">
                    <i class="fas fa-users"></i>
                    New Group
                </button>
            </div>
            
            <div class="chats-container">
                <!-- Online Users -->
                <div class="section-header">
                    <h3>Online Now</h3>
                    <span class="online-count" id="online-count">0</span>
                </div>
                <div class="chat-list" id="online-users-list">
                    <div class="loading">Loading online users...</div>
                </div>
                
                <!-- All Conversations -->
                <div class="section-header">
                    <h3>Recent Chats</h3>
                </div>
                <div class="chat-list" id="conversations-list">
                    <div class="loading">Loading conversations...</div>
                </div>
                
                <!-- Group Chats -->
                <div class="section-header" id="groups-header" style="display: none;">
                    <h3>Group Chats</h3>
                </div>
                <div class="chat-list" id="group-chats-list">
                    <!-- Groups will be loaded here -->
                </div>
            </div>
        </div>
        
        <!-- Main Chat Area -->
        <div class="main-chat" id="main-chat">
            <div class="welcome-screen" id="welcome-screen">
                <div class="welcome-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h2>Welcome <?php echo htmlspecialchars($username); ?>  </h2>
                <p>Select a conversation from the sidebar to start chatting. You can message individual users or create group chats.</p>
            </div>
            
            <div class="chat-header-area" id="chat-header" style="display: none;">
                <div class="chat-header-avatar">
                    <img src="https://ui-avatars.com/api/?name=User&background=94a3b8&color=fff" alt="Profile" id="current-user-avatar">
                </div>
                <div class="chat-header-info">
                    <h2 id="current-chat-title">Select a chat</h2>
                    <p id="current-chat-status">Select a user to start chatting</p>
                    <div class="typing-indicator" id="typing-indicator" style="display: none;"></div>
                </div>
              
            </div>
            
            <div class="messages-container" id="chat-messages" style="display: none;">
               
            </div>
            
            <div class="chat-input-area" id="chat-input-area" style="display: none;">
                <div class="file-attachment">
                    <input type="file" id="attachment" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt">
                    <button class="attach-btn" id="attachments">
                        <i class="fas fa-paperclip"></i>
                    </button>
                </div>
                <span id="file-name" class="file-name"></span>
                
                <input type="text" class="message-input" id="message-input" placeholder="Type your message here..." disabled>
                <button class="send-button" id="send-button" disabled>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Group Creation Modal -->
    <div class="modal-overlay" id="group-modal" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h3>Create New Group</h3>
                <button class="close-modal" id="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="text" class="modal-input" id="group-name" placeholder="Enter group name (e.g., 'Project Team')">
                <div class="selected-users" id="selected-users-list">
                    <div class="hint">No users selected yet</div>
                </div>
                <div class="users-list" id="available-users-list">
                    
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancel-group">Cancel</button>
                <button class="btn btn-primary" id="create-group-final">Create Group</button>
            </div>
        </div>
    </div>

    <script>
    // Socket.IO connection
    const socket = io("http://10.10.15.140:7360", {
        withCredentials: true
    });
    
    // DOM elements
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const chatMessages = document.getElementById('chat-messages');
    const onlineUsersList = document.getElementById('online-users-list');
    const conversationsList = document.getElementById('conversations-list');
    const groupChatsList = document.getElementById('group-chats-list');
    const currentChatTitle = document.getElementById('current-chat-title');
    const currentChatStatus = document.getElementById('current-chat-status');
    const currentUserAvatar = document.getElementById('current-user-avatar');
    const typingIndicator = document.getElementById('typing-indicator');
    const onlineCount = document.getElementById('online-count');
    const myUsername = document.getElementById('my-username');
    const myUserId = document.getElementById('my-user-id');
    const myAvatar = document.getElementById('my-avatar');
    const myStatus = document.getElementById('my-status');
    const searchUsers = document.getElementById('search-users');
    const createGroupBtn = document.getElementById('create-group-btn');
    const groupModal = document.getElementById('group-modal');
    const closeModal = document.getElementById('close-modal');
    const cancelGroupBtn = document.getElementById('cancel-group');
    const createGroupFinalBtn = document.getElementById('create-group-final');
    const welcomeScreen = document.getElementById('welcome-screen');
    const chatHeader = document.getElementById('chat-header');
    const chatInputArea = document.getElementById('chat-input-area');
    const groupNameInput = document.getElementById('group-name');
    const selectedUsersList = document.getElementById('selected-users-list');
    const availableUsersList = document.getElementById('available-users-list');
    
    // Current user and chat state
    let currentUser = null;
    let currentRoom = null;
    let currentConversation = null;
    let currentChatType = null; // 'private' or 'group'
    
    // Get username from PHP
    const myRealUsername = <?php echo json_encode($username); ?>;
    const myRealId = <?php echo json_encode($userId); ?>;
    
    let myUserData = {
        id: myRealId,
        username: myRealUsername,
        socketId: null
    };
    
    // Users and conversations data
    let onlineUsers = [];
    let allConversations = [];
    let allUsers = [];
    let typingUsers = new Set();
    let typingTimer = null;
    
    // Group creation state
    let selectedUsersForGroup = [];
    
    // Initialize the app
    function initApp() {
        // Set up search functionality
        searchUsers.addEventListener('input', filterChats);
        
        // Authenticate with socket
        socket.emit('authenticate', {
            userId: myUserData.id,
            username: myUserData.username
        });
        
        // Load initial data
        loadConversations();
        loadAllUsers();
        
        // Set up event listeners
        setupEventListeners();
        
        console.log("App initialized for user:", myUserData.username);
    }
    
    // Set up event listeners
    function setupEventListeners() {
        // Message sending
        sendButton.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
        messageInput.addEventListener('input', handleTyping);
        
        // File attachment
        document.getElementById('attachments').addEventListener('click', () => {
            document.getElementById('attachment').click();
        });
        document.getElementById('attachment').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('file-name').textContent = file.name;
            } else {
                document.getElementById('file-name').textContent = '';
            }
        });
        
        // Group creation modal
        createGroupBtn.addEventListener('click', showGroupModal);
        closeModal.addEventListener('click', () => groupModal.style.display = 'none');
        cancelGroupBtn.addEventListener('click', () => groupModal.style.display = 'none');
        createGroupFinalBtn.addEventListener('click', createGroup);
        
        // Socket event listeners
        setupSocketListeners();
    }
    
    // Set up socket listeners
    function setupSocketListeners() {
        socket.on('connect', () => {
            myUserData.socketId = socket.id;
            myUserId.textContent = `ID: ${myUserData.id.substring(0, 8)}...`;
            myStatus.className = 'user-status online';
            
            console.log('Connected to socket with ID:', socket.id);
        });
        
        socket.on('userListUpdate', (users) => {
            console.log('Online users updated:', users);
            onlineUsers = users;
            updateOnlineUsersDisplay();
            updateUserStatusInLists();
        });
        
        socket.on('chatRoom', (data) => {
            console.log('New message received:', data);
            if (data.room === currentRoom && data.senderId !== myUserData.id) {
                displayMessage(data.message, false);
                chatMessages.scrollTop = chatMessages.scrollHeight;
                
                // Update conversation in sidebar
                updateConversationLastMessage(currentConversation._id, data.message);
            }
        });
        
        socket.on('userTyping', (data) => {
            console.log("typing socket",data)
            if (data.room === currentRoom) {
                if (data.isTyping) {
                    typingUsers.add(data.username);
                } else {
                    typingUsers.delete(data.username);
                }
                updateTypingIndicator();
            }
        });
    }
    
    // Load all conversations
    async function loadConversations() {
        try {
            const response = await fetch(`http://10.10.15.140:7360/api/${myUserData.id}/conversations`);
            const data = await response.json();
            
            if (data.conversations && data.conversations.length > 0) {
                allConversations = data.conversations;
                displayConversations(data.conversations);
                
                // Load groups separately
                loadGroups();
            } else {
                conversationsList.innerHTML = '<div class="no-chats">No conversations yet</div>';
            }
        } catch (err) {
            console.error("Error loading conversations:", err);
            conversationsList.innerHTML = '<div class="error">Error loading conversations</div>';
        }
    }
    
    // Load groups
    async function loadGroups() {
        try {
            const response = await fetch(`http://10.10.15.140:7360/api/${myUserData.id}/groups`);
            const data = await response.json();
            
            if (data.groups && data.groups.length > 0) {
                displayGroups(data.groups);
                document.getElementById('groups-header').style.display = 'block';
            }
        } catch (err) {
            console.error("Error loading groups:", err);
        }
    }
    
    // Load all users for group creation
    async function loadAllUsers() {
  try {
    // FIXED URL
    const response = await fetch(`http://10.10.15.140:7360/api/users/chat/${myUserData.id}`);
    const data = await response.json();
    
    if (data.users && data.users.length > 0) {
      allUsers = data.users;
    }
  } catch (err) {
    console.error("Error loading all users:", err);
  }
}
    // Display conversations
    function displayConversations(conversations) {
        conversationsList.innerHTML = '';
        
        conversations.forEach(conv => {
            if (!conv.isGroup) {
                // Private conversation
                const otherParticipant = conv.participants?.find(p => p._id !== myUserData.id);
                if (otherParticipant) {
                    const isOnline = onlineUsers.some(u => u.userId === otherParticipant._id);
                    
                    const conversationItem = createConversationItem({
                        id: conv._id,
                        name: otherParticipant.username,
                        avatar: `https://ui-avatars.com/api/?name=${encodeURIComponent(otherParticipant.username)}&background=random&color=fff`,
                        lastMessage: conv.lastMessage?.text || 'Start chatting',
                        time: formatTime(conv.updatedAt),
                        type: 'private',
                        userId: otherParticipant._id,
                        isOnline: isOnline,
                        unread: 0
                    });
                    
                    conversationsList.appendChild(conversationItem);
                }
            }
        });
    }
    
    // Display groups
    function displayGroups(groups) {
        groupChatsList.innerHTML = '';
        
        groups.forEach(group => {
            const groupItem = createConversationItem({
                id: group._id,
                name: group.groupName,
                avatar: group.groupImage || `https://ui-avatars.com/api/?name=${encodeURIComponent(group.groupName)}&background=6366f1&color=fff`,
                lastMessage: group.lastMessage?.text || 'No messages yet',
                time: formatTime(group.updatedAt),
                type: 'group',
                participantsCount: group.participants?.length || 0,
                unread: 0
            });
            
            groupChatsList.appendChild(groupItem);
        });
    }
    
    // Create conversation list item
    function createConversationItem(data) {
        const item = document.createElement('div');
        item.className = `chat-item ${data.unread > 0 ? 'unread' : ''}`;
        item.dataset.id = data.id;
        item.dataset.type = data.type;
        item.dataset.userId = data.userId || '';
        
        item.innerHTML = `
            <div class="chat-avatar ${data.type === 'group' ? 'group' : ''}">
                <img src="${data.avatar}" alt="${data.name}">
                ${data.type === 'private' ? `<div class="chat-status ${data.isOnline ? 'online' : 'offline'}"></div>` : ''}
            </div>
            <div class="chat-info">
                <div class="chat-header">
                    <div class="chat-name">${data.name}</div>
                    <div class="chat-time">${data.time}</div>
                </div>
                <div class="last-message">${data.lastMessage}</div>
                ${data.type === 'group' ? `<div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">${data.participantsCount} members</div>` : ''}
            </div>
            ${data.unread > 0 ? `<div class="unread-badge">${data.unread}</div>` : ''}
        `;
        
        item.addEventListener('click', () => {
            selectConversation(data);
        });
        
        return item;
    }
    
    // Select conversation (private or group)
    async function selectConversation(data) {
        // Remove active class from all items
        document.querySelectorAll('.chat-item').forEach(item => item.classList.remove('active'));
        event.currentTarget.classList.add('active');
        
        // Hide welcome screen, show chat
        welcomeScreen.style.display = 'none';
        chatHeader.style.display = 'flex';
        chatMessages.style.display = 'block';
        chatInputArea.style.display = 'flex';
        
        // Set current conversation data
        currentChatType = data.type;
        
        if (data.type === 'private') {
            // Private chat
            currentUser = {
                id: data.userId,
                username: data.name,
                avatar: data.avatar
            };
            
            currentChatTitle.textContent = data.name;
            currentChatStatus.textContent = data.isOnline ? 'Online' : 'Offline';
            currentChatStatus.innerHTML = data.isOnline ? 
                '<i class="fas fa-circle" style="color: #10b981; font-size: 10px;"></i> Online' : 
                '<i class="fas fa-circle" style="color: #94a3b8; font-size: 10px;"></i> Offline';
            
            // Get or create conversation
            try {
                const response = await fetch('http://10.10.15.140:7360/api/conversation', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        userId1: myUserData.id,
                        userId2: data.userId
                    })
                });
                
                const convData = await response.json();
                currentConversation = convData.conversation;
                currentRoom = currentConversation.roomName;
            } catch (err) {
                console.error('Error getting conversation:', err);
                return;
            }
        } else {
            // Group chat
            currentUser = {
                id: data.id,
                username: data.name,
                avatar: data.avatar,
                isGroup: true
            };
            
            currentChatTitle.textContent = data.name;
            currentChatStatus.textContent = `${data.participantsCount} members`;
            currentChatStatus.innerHTML = `<i class="fas fa-users" style="color: #6366f1;"></i> ${data.participantsCount} members`;
            
            currentConversation = { _id: data.id, roomName: `group_${data.id}` };
            currentRoom = `group_${data.id}`;
        }
        
        // Update avatar
        currentUserAvatar.src = data.avatar;
        
        // Join socket room
        socket.emit('joinRoom', {
            roomName: currentRoom,
            username: myUserData.username,
            id: myUserData.id
        });
        
        // Enable message input
        messageInput.disabled = false;
        sendButton.disabled = false;
        messageInput.focus();
        
        // Load chat history
        loadChatHistory(currentConversation._id);
    }
    
    // Load chat history
    async function loadChatHistory(conversationId) {
        chatMessages.innerHTML = '';
        
        try {
            const response = await fetch(`http://10.10.15.140:7360/api/messages/${conversationId}`);
            const messages = await response.json();
            
            if (messages.length === 0) {
                const welcomeMsg = document.createElement('div');
                welcomeMsg.className = 'message received';
                welcomeMsg.innerHTML = `
                    <div class="message-content">
                        Start your conversation with ${currentUser.username}! Send your first message.
                    </div>
                    <div class="message-time">Just now</div>
                `;
                chatMessages.appendChild(welcomeMsg);
            } else {
                messages.forEach(msg => {
                    displayMessage(msg, msg.sender._id === myUserData.id);
                });
            }
            
            chatMessages.scrollTop = chatMessages.scrollHeight;
        } catch (err) {
            console.error('Error loading chat history:', err);
        }
    }
    
    // Display a message
    function displayMessage(msg, isSent) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isSent ? 'sent' : 'received'}`;
        
        let content = `<div class="message-content">${msg.text || ''}</div>`;
        
        // Add attachments if present
        if (msg.attachments && msg.attachments.length > 0) {
            msg.attachments.forEach(att => {
                const secureUrl = `http://10.10.15.140:7360/api/messages/file/${msg._id}/${att.fileUrl}?userId=${myUserData.id}`;
                
                if (att.fileType.startsWith('image/')) {
                    content += `<div class="attachment"><img src="${secureUrl}" alt="${att.fileName}"></div>`;
                } else if (att.fileType.startsWith('video/')) {
                    content += `<div class="attachment"><video controls><source src="${secureUrl}" type="${att.fileType}"></video></div>`;
                } else {
                    content += `<a href="${secureUrl}" target="_blank" class="attachment-file">
                        <i class="fas fa-file"></i>
                        <span>${att.fileName}</span>
                    </a>`;
                }
            });
        }
        
        const time = new Date(msg.createdAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        content += `<div class="message-time">${time}</div>`;
        
        messageDiv.innerHTML = content;
        chatMessages.appendChild(messageDiv);
    }
    
    // Send message
    async function sendMessage() {
        const message = messageInput.value.trim();
        const file = document.getElementById('attachment').files[0];
        
        if (!message && !file) return;
        
        if (!currentConversation) {
            alert('Please select a conversation first');
            return;
        }
        
        const formData = new FormData();
        formData.append('text', message);
        formData.append('senderId', myUserData.id);
        formData.append('conversationId', currentConversation._id);
        
        if (file) {
            formData.append('file', file);
        }
        
        try {
            const response = await fetch('http://10.10.15.140:7360/api/messages', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const savedMessage = await response.json();
            
            // Display the sent message immediately
            displayMessage(savedMessage, true);
            
            // Emit socket event
            socket.emit('chatRoom', {
                room: currentRoom,
                senderId: myUserData.id,
                message: savedMessage
            });
            
            // Clear inputs
            messageInput.value = '';
            document.getElementById('attachment').value = '';
            document.getElementById('file-name').textContent = '';
            
            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            // Update conversation list
            updateConversationLastMessage(currentConversation._id, savedMessage);
            
        } catch (err) {
            console.error('Error sending message:', err);
            alert('Error sending message');
        }
    }
    
    // Update conversation last message in sidebar
    function updateConversationLastMessage(conversationId, message) {
        const conversationItem = document.querySelector(`.chat-item[data-id="${conversationId}"]`);
        if (conversationItem) {
            const lastMessageEl = conversationItem.querySelector('.last-message');
            const timeEl = conversationItem.querySelector('.chat-time');
            
            if (lastMessageEl) {
                lastMessageEl.textContent = message.text || 'Attachment';
            }
            if (timeEl) {
                timeEl.textContent = formatTime(message.createdAt);
            }
        }
    }
    
    // Update online users display
    function updateOnlineUsersDisplay() {
        onlineUsersList.innerHTML = '';
        onlineCount.textContent = onlineUsers.length;
        
        if (onlineUsers.length === 0) {
            onlineUsersList.innerHTML = '<div class="no-users">No users online</div>';
            return;
        }
        
        // Filter out current user
        const otherUsers = onlineUsers.filter(u => u.userId !== myUserData.id);
        
        if (otherUsers.length === 0) {
            onlineUsersList.innerHTML = '<div class="no-users">No other users online</div>';
            return;
        }
        
        otherUsers.forEach(user => {
            const userItem = createConversationItem({
                id: `online_${user.userId}`,
                name: user.username,
                avatar: `https://ui-avatars.com/api/?name=${encodeURIComponent(user.username)}&background=10b981&color=fff`,
                lastMessage: 'Click to chat',
                time: 'Online',
                type: 'private',
                userId: user.userId,
                isOnline: true,
                unread: 0
            });
            
            onlineUsersList.appendChild(userItem);
        });
    }
    
    // Update user status in conversation lists
    function updateUserStatusInLists() {
        document.querySelectorAll('.chat-item[data-type="private"]').forEach(item => {
            const userId = item.dataset.userId;
            const isOnline = onlineUsers.some(u => u.userId === userId);
            const statusEl = item.querySelector('.chat-status');
            
            if (statusEl) {
                statusEl.className = `chat-status ${isOnline ? 'online' : 'offline'}`;
            }
            
            // Update time display for online users
            const timeEl = item.querySelector('.chat-time');
            if (timeEl && isOnline) {
                timeEl.textContent = 'Online';
            }
        });
    }
    
    // Handle typing
    function handleTyping() {
            console.log("current socket",currentRoom)

        if (currentRoom) {
            socket.emit('typing', {
                room: currentRoom,
                username: myUserData.username,
                isTyping: true
            });
            
            if (typingTimer) clearTimeout(typingTimer);
            
            typingTimer = setTimeout(() => {
                socket.emit('typing', {
                    room: currentRoom,
                    username: myUserData.username,
                    isTyping: false
                });
            }, 1000);
        }
    }
    
    // Update typing indicator
    function updateTypingIndicator() {
        if (typingUsers.size > 0) {
            const names = Array.from(typingUsers);
            console.log("updatetypingindicator",names)
            const text = names.length === 1 ? 
                `${names[0]} is typing...` : 
                `${names.length} people are typing...`;
                console.log(text)
            typingIndicator.textContent = text;
            typingIndicator.style.display = 'block';
        } else {
            typingIndicator.style.display = 'none';
        }
    }
    
    // Show group creation modal
  async function showGroupModal() {
  selectedUsersForGroup = [];
  groupNameInput.value = '';
  selectedUsersList.innerHTML = '<div class="hint">No users selected yet</div>';
  availableUsersList.innerHTML = '';
  
  // Load available users - FIXED URL
  try {
    const response = await fetch(`http://10.10.15.140:7360/api/users/all/${myUserData.id}`);
    const data = await response.json();
    
    if (data.users && data.users.length > 0) {
      data.users.forEach(user => {
        if (user._id !== myUserData.id) {
          const userItem = createUserSelectItem(user);
          availableUsersList.appendChild(userItem);
        }
      });
    }
  } catch (err) {
    console.error('Error loading users for group:', err);
    availableUsersList.innerHTML = '<div class="error">Error loading users</div>';
  }
  
  groupModal.style.display = 'flex';
}
    
    // Create user select item for group creation
    function createUserSelectItem(user) {
        const item = document.createElement('div');
        item.className = 'user-select-item';
        item.dataset.userId = user._id;
        
        item.innerHTML = `
            <img src="${user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.username)}&background=random&color=fff`}" alt="${user.username}">
            <div class="user-select-info">
                <h4>${user.username}</h4>
                <p>${user.isOnline ? 'Online' : 'Offline'}</p>
            </div>
            <div class="user-checkbox">
                <i class="fas fa-check"></i>
            </div>
        `;
        
        item.addEventListener('click', () => {
            toggleUserSelection(user, item);
        });
        
        return item;
    }
    
    // Toggle user selection for group
    function toggleUserSelection(user, item) {
        const userId = user._id;
        const index = selectedUsersForGroup.indexOf(userId);
        
        if (index === -1) {
            // Select user
            selectedUsersForGroup.push(userId);
            item.classList.add('selected');
            
            // Add to selected users list
            const selectedUserEl = document.createElement('div');
            selectedUserEl.className = 'selected-user';
            selectedUserEl.dataset.userId = userId;
            selectedUserEl.innerHTML = `
                ${user.username}
                <span class="remove-user" onclick="removeUserFromGroup('${userId}')">&times;</span>
            `;
            
            // Remove hint if present
            const hint = selectedUsersList.querySelector('.hint');
            if (hint) hint.remove()
            
            selectedUsersList.appendChild(selectedUserEl);
        } else {
            // Deselect user
            selectedUsersForGroup.splice(index, 1);
            item.classList.remove('selected');
            
            // Remove from selected users list
            const selectedUserEl = selectedUsersList.querySelector(`[data-user-id="${userId}"]`);
            if (selectedUserEl) selectedUserEl.remove();
            
            // Add hint if no users selected
            if (selectedUsersForGroup.length === 0) {
                selectedUsersList.innerHTML = '<div class="hint">No users selected yet</div>';
            }
        }
    }
    
    // Remove user from group selection (called from inline onclick)
    window.removeUserFromGroup = function(userId) {
        selectedUsersForGroup = selectedUsersForGroup.filter(id => id !== userId);
        
        // Update selected users list
        const selectedUserEl = selectedUsersList.querySelector(`[data-user-id="${userId}"]`);
        if (selectedUserEl) selectedUserEl.remove();
        
        // Update user select item
        const userItem = availableUsersList.querySelector(`[data-user-id="${userId}"]`);
        if (userItem) userItem.classList.remove('selected');
        
        // Add hint if no users selected
        if (selectedUsersForGroup.length === 0) {
            selectedUsersList.innerHTML = '<div class="hint">No users selected yet</div>';
        }
    };
    
   async function createGroup() {
  const groupName = groupNameInput.value.trim();
  
  if (!groupName) {
    alert('Please enter a group name');
    return;
  }
  
  if (selectedUsersForGroup.length < 1) {
    alert('Please select at least one user for the group');
    return;
  }
  
  try {
    // FIXED: Make sure this endpoint exists
    const response = await fetch('http://10.10.15.140:7360/api/group', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: groupName,
        participants: selectedUsersForGroup,
        admin: myUserData.id
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Close modal
      groupModal.style.display = 'none';
      
      // Reload groups
      loadGroups();
      
      // Show success message
      alert('Group created successfully!');
      
      // Optionally, automatically select the new group
      if (data.conversation && data.conversation._id) {
        // Add a small delay to ensure the group is loaded
        setTimeout(() => {
          loadGroups();
        }, 1000);
      }
    } else {
      alert('Error creating group: ' + (data.error || 'Unknown error'));
    }
  } catch (err) {
    console.error('Error creating group:', err);
    alert('Error creating group: ' + err.message);
  }
}
    
    // Filter chats based on search
    function filterChats() {
        const searchTerm = searchUsers.value.toLowerCase();
        const allChatItems = document.querySelectorAll('.chat-item');
        
        allChatItems.forEach(item => {
            const chatName = item.querySelector('.chat-name').textContent.toLowerCase();
            if (chatName.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    // Format time
    function formatTime(dateString) {
        if (!dateString) return '';
        
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / (1000 * 60));
        
        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        
        const diffHours = Math.floor(diffMins / 60);
        if (diffHours < 24) return `${diffHours}h ago`;
        
        const diffDays = Math.floor(diffHours / 24);
        if (diffDays === 1) return 'Yesterday';
        if (diffDays < 7) return `${diffDays}d ago`;
        
        return date.toLocaleDateString();
    }
    
    // Initialize the app when page loads
    document.addEventListener('DOMContentLoaded', initApp);
    </script>
</body>
</html>