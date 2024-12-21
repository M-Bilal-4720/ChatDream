import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Pusher configuration
window.Pusher = Pusher;

// Get the CSRF token from the meta tag
const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;

const echo = new Echo({
    broadcaster: 'pusher',
    key: 'socketkey', // Ensure these values match your .env file
    cluster: 'mt1', // Your Pusher app cluster
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-Token': csrfToken, // Include the CSRF token here
            'Authorization': `Bearer ${localStorage.getItem('token')}`, // Assuming you're using tokens for authentication
        },
    },
});

export default echo;
