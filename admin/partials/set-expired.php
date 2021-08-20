<div class="wrap">
    <h1><?php _e('Pengaturan Expired Poin', 'sejoli'); ?></h1>    
    <form id='sejoli-set-expired-point-form' action="" method="post">
        
        <?php 
            $get_current_date = date('Y-m-d'); 
        ?>

        <div class="sejoli-expired-point-form-response">
        </div>

        <p>Ketika anda klik "Proses Sekarang",  Maka poin yang didapat customer sebelum tanggal yang anda pilih akan hangus.</p>

        <table class='form-table' role='presentation'>
            <tbody>
                <tr>
                    <th scope='row'>
                        <?php _e('Pilih Tanggal Expired Point', 'sejoli'); ?>
                    </th>
                    <td>                        
                        <input type="text" id="expired-date" name="expired-date" data-toggle="datepicker">
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class='submit'>
            <button type="submit" name="button" class='button button-primary' id='sejoli-set-expired-button'><?php _e('Proses Sekarang', 'sejoli'); ?></button>
        </p>
        <?php wp_nonce_field('sejoli-set-expired-point', 'noncekey'); ?>
    </form>
</div>