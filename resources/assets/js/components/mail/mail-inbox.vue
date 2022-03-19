<template>
    <section class="panel">
        <div class="panel-body ">
            <div class="mail-box-header" v-if="loaded">
                <form method="post" role="form" class="pull-right mail-search" @submit.prevent="loadMails">
                    <div class="input-group">
                        <input type="text" v-model="data.query" class="form-control input-sm" name="search" placeholder="Search email">
                    </div>
                </form>
                <h2>Inbox</h2>
            </div>
            <div class="mail-option">
                <div class="btn-group pull-left table-bordered paddingrightleft_10 paddingtopbottom_5px">
                    <input type="checkbox" id="checkall" v-model="selectedAll">
                    <!--<a class="dropdown-toggle" data-toggle="dropdown" href="#">-->
                        <!--<span class="caret"></span>-->
                    <!--</a>-->
                    <!--<ul class="dropdown-menu ul">-->
                        <!--<li>-->
                            <!--<a href="#" @click.prevent="selectAllRead">Read</a>-->
                        <!--</li>-->
                        <!--<li>-->
                            <!--<a href="#" @click.prevent="selectAllUnRead">UnRead</a>-->
                        <!--</li>-->
                    <!--</ul>-->
                </div>
                <div class="btn-group">
                    <a @click.prevent="loadMails()" data-original-title="Refresh" data-placement="top" data-toggle="dropdown" href="#" class="btn mini tooltips">
                        <i class=" fa fa-refresh"></i>
                    </a>
                </div>
                <div class="btn-group hidden-phone">
                    <a data-toggle="dropdown" href="#" class="btn mini blue">
                    More
                    <i class="fa fa-angle-down "></i>
                </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="#" @click.prevent="markAsRead">
                                <i class="fa fa-pencil"></i> Mark as Read
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" @click.prevent="deleteSelected">
                                <i class="fa fa-trash-o"></i> Delete
                            </a>
                        </li>
                    </ul>
                </div>
                <ul class="unstyled inbox-pagination">
                </ul>
            </div>
            <div class="mail-box">
                <div class="table-responsive">
                    <table class="table" id="inbox-check">
                        <tbody>
                            <tr data-messageid="1" class="unread" v-if="mail.sender" v-for="mail in filtered_mails" :class="{'read' : mail.read }">
                                <td class="inbox-small-cells">
                                    <div class="checker">
                                        <span>
                            <input type="checkbox" v-model="mail.selected">
                        </span>
                                    </div>
                                </td>
                                <td class="view-message hidden-xs">
                                    <router-link :to="{ name: 'inbox', params: { id: mail.id } }">
                                        {{ mail.sender.full_name }} </router-link>
                                </td>
                                <td class="view-message ">
                                    <router-link :to="{ name: 'inbox', params: { id: mail.id } }">{{ mail.subject }}</router-link>
                                </td>
                                <td class="view-message text-right">
                                    <router-link :to="{ name: 'inbox', params: { id: mail.id } }">{{ mail.created_at
                                        }}
                                    </router-link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="email_count == 0">
                        No Mails
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
export default {
    //props: ['url'],

    data: function() {
        return {
            data: {
                query: '',
                page: 1,
            },
            mails: [],
            email_count: 0,
            url: null,
            loaded: false,
            selectedAll: false,
        }
    },

    computed: {
        selectedMails: function() {
            return this.mails.filter(function(item) {
                return item.selected;
            });
        },
        filtered_mails: function() {
            var self = this;
            return self.mails.filter(function(item) {
                var regex = new RegExp(self.data.query.trim().toLowerCase());
                var res = item.subject.toLowerCase().match(regex, "i");
                if (res != null) {
                    return item;
                }
            })
        }
    },

    methods: {
        init: function(response) {
            this.mails = _.map(response.data.received, function(item) {
                item.selected = false;
                return item;
            });
            this.email_count = response.data.received_count;

            //Look for select all checkbox
            this.$watch('selectedAll', function(selected) {
                this.updateRowsSelection(selected);
            });

            this.loaded = true;
            this.selectedAll = false;
        },

        loadMails: function() {
            axios.get(this.url + '/received', this.data).then(response => {
                this.init(response);
            }, error => {

            });
        },

        deleteSelected: function() {
            var ids = _.map(this.selectedMails, function(item) {
                return item.id;
            });

            axios.post(this.url + '/delete', {
                ids: ids
            }).then(function() {
                this.loadMails();
            }.bind(this));
        },

        markAsRead: function() {
            var ids = _.map(this.selectedMails, function(item) {
                return item.id;
            });

            axios.post(this.url + '/mark-as-read', {
                ids: ids
            }).then(() => {
                this.loadMails();
            });

        },
        updateRowsSelection: function(status) {
            _.each(this.mails, function(item) {
                item.selected = status;
            });
        },

        selectAllRead: function() {
            this.updateRowsSelection(false);
            _.each(this.mails, function(item) {
                if (item.read) {
                    item.selected = true;
                }
            });
        },

        selectAllUnRead: function() {
            this.updateRowsSelection(false);
            _.each(this.mails, function(item) {
                if (!item.read) {
                    item.selected = true;
                }
            });
        },

        search: function() {
            this.loadMails();
        }
    },

    mounted: function() {
        this.url = this.$parent.url;
        this.loadMails();
    },

    filters: {
        date: function(val) {
            return moment(val).fromNow();
        }
    }
}
</script>
<style>
.read {
    background-color: whitesmoke;
    color: grey;
}
</style>
