import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Initialize Laravel Echo with Reverb configuration
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: process.env.MIX_REVERB_APP_KEY,
    wsHost: process.env.MIX_REVERB_HOST,
    wsPort: process.env.MIX_REVERB_PORT,
    wssPort: process.env.MIX_REVERB_PORT,
    forceTLS: false,
    encrypted: true,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});

// Get the current user ID (this would be set by your authentication system)
const userId = document.head.querySelector('meta[name="user-id"]')?.content || 1;

// Listen for general chat messages
window.Echo.channel('chat')
    .listen('MessageSent', (e) => {
        console.log('New message:', e.message);
        // Add message to chat UI
    });

// Listen for typing indicators
window.Echo.channel('chat')
    .listen('UserTyping', (e) => {
        console.log(`${e.user.name} is typing...`);
        // Update UI to show typing indicator
    });

// Listen for message read events
window.Echo.private(`user.${userId}`)
    .listen('MessageRead', (e) => {
        console.log(`Message ${e.message.id} read by ${e.user.name}`);
        // Update message status to show as read
    });

// Listen for private messages
function listenForPrivateMessages(userId1, userId2) {
    // Generate consistent channel name (lower ID first)
    const channelId = userId1 < userId2 ? 
        `private-chat.${userId1}.${userId2}` : 
        `private-chat.${userId2}.${userId1}`;
    
    window.Echo.private(channelId)
        .listen('PrivateMessageSent', (e) => {
            console.log(`Private message from ${e.message.user.name}: ${e.message.message}`);
            // Display private message in UI
        });
}

// Listen for user presence events
window.Echo.join('chat')
    .here((users) => {
        console.log('Online users:', users);
        // Show all online users
    })
    .joining((user) => {
        console.log(`${user.name} joined`);
        // Show user joined
    })
    .leaving((user) => {
        console.log(`${user.name} left`);
        // Show user left
    });

// Connection status
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('Connected to WebSocket');
    // Update UI to show connected status
});

window.Echo.connector.pusher.connection.bind('disconnected', () => {
    console.log('Disconnected from WebSocket');
    // Update UI to show disconnected status
});

window.Echo.connector.pusher.connection.bind('error', (error) => {
    console.log('WebSocket error:', error);
    // Update UI to show error status
});

// Reconnection logic
let reconnectAttempts = 0;
const maxReconnectAttempts = 5;

window.Echo.connector.pusher.connection.bind('disconnected', () => {
    if (reconnectAttempts < maxReconnectAttempts) {
        reconnectAttempts++;
        console.log(`Reconnection attempt ${reconnectAttempts}`);
        
        // Try to reconnect after a delay
        setTimeout(() => {
            window.Echo.connector.pusher.connect();
        }, 1000 * reconnectAttempts); // Exponential backoff
    } else {
        console.log('Max reconnection attempts reached');
        // Show error to user
    }
});

window.Echo.connector.pusher.connection.bind('connected', () => {
    // Reset reconnect attempts on successful connection
    reconnectAttempts = 0;
});

// Export functions for use in other modules
export { listenForPrivateMessages };