<div class="wrap">
    <h1><?php _e('Form Perubahan Poin', 'sejoli'); ?></h1>
    <form id='sejoli-add-point-data-form' action="" method="post">
        <table class='form-table' role='presentation'>
            <tbody>
                <tr>
                    <th scope='row'>
                        <?php _e('User', 'sejoli'); ?>
                    </th>
                    <td>
                        <select id='sejoli-user-options' name="data[user_id]" class='regular-text sejoli-point-field' required></select>
                        <p class="description" id="sejoli-user">Cari user sesuai dengan nama lengkap atau alamat email</p>
                    </td>
                </tr>
                <tr>
                    <th scope='row'>
                        <?php _e('Poin', 'sejoli'); ?>
                    </th>
                    <td>
                        <input type="number" id='sejoli-point' name="data[point]" class='regular-text sejoli-point-field' required value='0'/>
                    </td>
                </tr>
                <tr>
                    <th scope='row'>
                        <?php _e('Operasi', 'sejoli'); ?>
                    </th>
                    <td>
                        <select class='sejoli-point-field' name="data[operation]" required data-default='add'>
                            <option value="add"><?php _e('Tambah Poin', 'sejoli'); ?></option>
                            <option value="reduce"><?php _e('Kurangi Poin', 'sejoli'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope='row'>
                        <?php _e('Catatan', 'sejoli'); ?>
                    </th>
                    <td>
                        <textarea class='sejoli-point-field' name="data[note]" rows="8" cols="80"></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class='submit'>
            <button type="submit" name="button" class='button button-primary' id='sejoli-add-button'><?php _e('Proses Perubahan Poin', 'sejoli'); ?></button>
        </p>
        <?php wp_nonce_field('sejoli-add-point-data', 'noncekey'); ?>
    </form>
</div>
<script type="text/javascript">
(function($){

    'use strict';

    $(document).ready(function(){

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
