import Vue from 'vue';
import VueRouter from 'vue-router';
window.axios = require('axios');
/**
 * Add flatpickr as a framework independant date/time picker
 */
window.flatpickr = require("flatpickr").default
window.rangePlugin = require('flatpickr/dist/plugins/rangePlugin.js')

Vue.use(VueRouter);

window.axios.defaults.headers.common = {
    'X-CSRF-TOKEN': document.querySelector('#token').getAttribute('value'),
    'X-Requested-With': 'XMLHttpRequest'
};

Vue.directive('image-preview', require('./directives/image-upload-preview'));

//routing
const routes = [{
    path: '/m',
    component: require('./components/mail.vue'),
    children: [{
        path: 'inbox',
        component: require('./components/mail/mail-inbox.vue')
    }, {
        path: 'inbox/:id',
        name: 'inbox',
        component: require('./components/mail/mail-read.vue'),
        children: [{
            path: 'reply',
            name: 'reply',
            component: require('./components/mail/mail-reply.vue')
        }]
    }, {
        path: 'compose',
        component: require('./components/mail/mail-compose.vue')
    }, {
        path: 'sent',
        component: require('./components/mail/mail-sent.vue')
    }, {
        path: 'sent/:id',
        name: 'sent',
        component: require('./components/mail/mail-read-sent.vue')
    }]
}];

const router = new VueRouter({
    routes,
    linkActiveClass: "active"
});

//==routing
//=========global event bus=====
window.bus = new Vue();
//=========global event bus=====
const App = new Vue({
    router,
    components: {
        'contacts': require('./components/contacts.vue'),
        'sales-team': require('./components/sales-team.vue'),
        'customer-import': require('./components/customer-import.vue'),
        'leads-import': require('./components/leads-import.vue'),
        'category-import': require('./components/category-import.vue'),
        'product-import': require('./components/product-import.vue'),
        'backup-settings': require('./components/backup-settings'),
        'notifications': require('./components/notifications.vue'),
        'mail-notifications': require('./components/mail-notification.vue'),
        'mail': require('./components/mail.vue')
    },

    methods: {
        // initPusher: function() {
        //     /* Enable pusher logging - don't include this in production
        //        Pusher.log = function (message) {
        //        if (window.console && window.console.log) {
        //             window.console.log(message);
        //        }
        //     };*/
        //
        //     var pusherKey = document.querySelector('#pusherKey').getAttribute('value');
        //     var userId = document.querySelector('#userId').getAttribute('value');
        //     var pusher = new Pusher(pusherKey);
        //     //Channels
        //     var channel = pusher.subscribe('lcrm_channel.user_' + userId);
        //
        //     /*Events
        //
        //     channel.bind('App\\Events\\MeetingCreated', function (data) {
        //     toastr["success"]("New meeting scheduled: Subject - " + data.meeting.meeting_subject);
        //      });
        //
        //    channel.bind('App\\Events\\CallCreated', function (data) {
        //     toastr["success"]("New call logged: Subject - " + data.call.call_summary);
        //    });
        //
        //   channel.bind('App\\Events\\MailCreated', function (data) {
        //    toastr["success"]("New call logged: Subject - " + data.email.subject);
        //   });
        //  */
        //
        //     channel.bind('App\\Events\\Email\\EmailCreated', function(data) {
        //         toastr["success"]("You got a new email");
        //         bus.$emit('newMailNotification', data.email)
        //     }.bind(this));
        //
        //     channel.bind('App\\Events\\NotificationEvent', function(data) {
        //         toastr["success"](data.notification.title);
        //         bus.$emit('newNotification', data.notification)
        //     }.bind(this));
        // },

        initToastr: function() {
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "positionClass": "toast-top-right",
                "onclick": null,
                "showDuration": "1000",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            }
        }
    },


    mounted: function() {
        // if (document.querySelector('#pusherKey')) {
        //     this.initPusher();
        // }
        this.initToastr();
    }
}).$mount('#app');
