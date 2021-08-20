<script type="text/javascript">
(function($){

    'use strict';

    $(document).ready(function(){

        
        $("#expired-date").datepicker(
            {
                dateFormat : 'dd/mm/yy',
            }
        );


        $('#sejoli-set-expired-point-form').submit(function(e){

            e.preventDefault();

            let formData =  new FormData($(this)[0]);
            let notice = $('.sejoli-expired-point-form-response');

            formData.append('action', 'set-expired-point-data');
            
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: formData,
                contentType: false,
                processData: false,
                dataType:   'json',
                beforeSend: function() {                        
                        notice.show()
                            .removeClass('notice-error notice-success')
                            .addClass('notice notice-info')
                            .html('<p><?php _e('Sedang memproses permintaan...', 'sejoli'); ?></p>');

                    }, success: function(response) {

                        if(response.success) {
                            notice.show()
                                .removeClass('notice-info notice-error')
                                .addClass('is-dismissible notice-success')
                                .html('<p>' + response.message + '</p>');

                                notice.delay(5000).fadeOut('slow'); 

                        } else {
                            notice.show()
                                .removeClass('notice-info notice-success')
                                .addClass('notice-error')
                                .html('<p>' + response.message + '</p>');
                        }
;
                    }
            });

        });

        
    });

})(jQuery);
</script>
