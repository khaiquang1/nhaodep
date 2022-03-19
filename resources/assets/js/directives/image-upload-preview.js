module.exports = {

    bind: function(el) {
        $(el).find('#image-preview').hide();
        $(el).find('input').change(function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                var source = $(this).parent().find('#image-preview');
                source.show();

                reader.onload = function(e) {
                    source.attr('src', e.target.result);
                };

                reader.readAsDataURL(this.files[0]);
            }
        });
    },

    update: function(value) {

    }
};
