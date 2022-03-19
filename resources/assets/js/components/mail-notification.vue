<template>
    <div>
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <i class="fa fa-fw fa-envelope-o black"></i>
            <span v-if="total > 0" class="label label-success">{{ total }}</span>
        </a>
        <ul class="dropdown-menu dropdown-messages table-striped">
            <li class="dropdown-title">You have {{ total }} new emails.</li>
            <li v-for="item in notifications">
                <a :href="getUrl(item)" class="message striped-col">
                    <div class="message-body">
                        <strong>{{ item.sender.full_name }}</strong>
                        <br> {{ item.subject }}
                        <br>
                        <small>{{ item.created_at | date }}</small>
                        <span class="label label-success label-mini">New</span>
                    </div>
                </a>
            </li>
            <li class="dropdown-footer"><a :href="inboxurl">View Messages</a></li>
        </ul>
    </div>
</template>
<script>
export default {
    props: ['url','prefix'],

    data: function() {
        return {
            total: null,
            notifications: []
        }
    },
    computed: {
        inboxurl: function() {
            return this.url + "/mailbox#/m/inbox";
        }
    },
    methods: {
        loadNotifications: function() {
            axios.get(this.url + this.prefix + '/mailbox/all')
                .then(response => {
                    this.total = response.data.total;
                    this.notifications = response.data.emails;
                })
                .catch(error => error);
        },
        getUrl: function(item) {
            return this.url + this.prefix + '/mailbox#/m/inbox/' + item.id;
        }
    },

    mounted() {
        this.loadNotifications();
    },
    created() {
        bus.$on('newMailNotification', email => {
            this.loadNotifications();
        })

    },

    filters: {
        date: function(val) {
            return moment(val).fromNow();
        }
    }
}
</script>
