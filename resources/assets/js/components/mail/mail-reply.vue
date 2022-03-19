<template>
    <div class="panel">
        <div class="panel-body">
            <textarea class="form-control resize_vertical" v-model="data.message" rows="10" placeholder="Enter your message here..."></textarea>
            <div class="compose-btn pull-right mailv-reply">
                <a href="" @click.prevent="submitReply" class="btn btn-sm btn-primary">
                Submit
            </a>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ['email'],

    data: function() {
        return {
            data: {
                message: ""
            }
        }
    },

    methods: {
        submitReply: function() {
            if (this.data.message != "") {
                axios.post(this.url + '/' + this.$route.params.id + '/reply', this.data).then(() => {
                    this.$router.push({
                        name: 'inbox',
                        params: {
                            id: this.$route.params.id
                        }
                    })
                });
            } else {
                toastr["error"]("Please fill all the required fields");
            }
        }
    },

    mounted: function() {
        this.url = this.$parent.$parent.url;
    }
}
</script>
