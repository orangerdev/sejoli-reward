<script type="text/javascript">
(function($){

    'use strict';

    $(document).ready(function(){

        console.log('test');

        sejoli.helper.select_2(
            "#sejoli-user-options",
            sejoli_admin.user.select.ajaxurl,
            sejoli_admin.user.placeholder
        );

        $('#sejoli-add-point-data-form').submit(function(e){

            let formData = new FormData($(this)[0]),
                submitButton = $('#sejoli-add-button'),
                notice = $('.sejoli-point-form-response'),
                confirmed = confirm('<?php _e('Anda yakin akan memproses form ini?', 'sejoli'); ?>');

            formData.append('action', 'add-input-point-data');

            if( !confirmed ) {
                return false;
            }

            $.ajax({
                type: 'POST',
                data:   formData,
                url:    '<?php echo admin_url('admin-ajax.php'); ?>',
                contentType: false,
                processData: false,
                dataType:   'json',
                beforeSend: function() {
                    submitButton.attr('disabled', true);
                    notice.show()
                        .removeClass('notice-error notice-success')
                        .addClass('notice-info')
                        .html('<p><?php _e('Sedang melakukan proses penambahan data poin...', 'sejoli'); ?></p>');

                }, success: function(response) {

                    if(response.success) {
                        notice.show()
                            .removeClass('notice-info notice-error')
                            .addClass('notice-success').html('<p>' + response.message + '</p>');

                        $('#sejoli-point').val(0);

                    } else {
                        notice.show()
                            .removeClass('notice-info notice-success')
                            .addClass('notice-error').html('<p>' + response.message + '</p>');
                    }

                    submitButton.attr('disabled', false);
                }
            });

            return false;
        });

    });

})(jQuery);
</script>
