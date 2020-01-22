
window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.$ = window.jQuery = require('jquery');

    require('bootstrap-sass');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo'

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'f467c670b37f772d422e',
    cluster: "ap1",
    encrypted:true
});

// window.Echo.channel('demo')
//           .listen('.my-event', post => {
//             if (! ('Notification' in window)) {
//               alert('Web Notification is not supported');
//               return;
//             }

//             Notification.requestPermission( permission => {
//               let notification = new Notification('New post alert!', {
//                 body: post.message, // content for the alert
//                 icon: "https://pusher.com/static_logos/320x320.png" // optional image url
//               });

//               // link to page on clicking the notification
//               notification.onclick = () => {
//                 window.open(window.location.href);
//               };
//             });
//           })

// window.Echo.channel('demo')
//     .listen('.my-event', (e) => {
//         console.log(e);
//     });
