<template>
    <section class="message">
        <div class="row">
            <div class="col-sm-4 col-md-4">
                <router-link to="/m/compose" role="button" class="btn btn-info btn-sm btn-block">COMPOSE</router-link>
                <div class="panel">
                    <div class="panel-body pan">
                        <ul class="nav nav-pills nav-stacked">
                            <li>
                                <router-link to="/m/inbox">
                                    <span class="badge pull-right" v-if="email_count > 0">{{ email_count }}</span>
                                    <i class="fa fa-inbox fa-fw mrs"></i> Inbox
                                </router-link>
                            </li>
                            <li>
                                <router-link to="/m/sent">
                                    <!--<span class="badge pull-right">{{ sent_email_count }}</span>-->
                                    <i class="fa fa-plane fa-fw mrs"></i> Sent Mail
                                </router-link>
                            </li>
                        </ul>
                    </div>
                </div>
                <hr>
                <div class="panel">
                    <div class="panel-body pan">
                        <ul class="nav nav-pills nav-stacked compose-mail">
                            <li class="active" v-if="online">
                                Online
                            </li>
                            <li v-if="!online">
                                No Online Users
                            </li>
                            <li v-for="user in onlineUsers">
                                <a href="#" @click.prevent="">
                                    <i class="fa fa-circle text-success pull-right"></i> {{ user.full_name }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-sm-8 col-md-8">
                <router-view></router-view>
            </div>
        </div>
    </section>
</template>
<script>
export default {
    props: ['url'],

    data: function() {
        return {
            email_count: 0,
            sent_email_count: 0,
            users: [],
            users_list: [],
            online: false
        }
    },

    methods: {
        loadData: function() {
            axios.get(this.url + '/data', this.query).then(response => {
                this.email_count = response.data.email_count;
                this.sent_email_count = response.data.sent_email_count;
                this.users = response.data.users;
                this.users_list = response.data.users_list;
                this.online = this.onlineUsers.length > 0 ? true : false;
            }).catch(error => {

            });
        }
    },

    computed: {
        onlineUsers: function() {
            if(this.users_list.length){
            return this.users_list.filter(function(item) {
                if (parseInt(item.active)) {
                    return item;
                }
            });
        }
        }
    },

    filters: {
        online: function(items) {
            return items.filter(function(item) {
                return item.active;
            });
        }
    },

    mounted() {
        this.loadData();
    }
}
</script>
