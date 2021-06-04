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
