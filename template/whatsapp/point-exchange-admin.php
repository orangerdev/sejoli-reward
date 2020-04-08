<?php
    printf(
        __('*%s* telah menukar point sebesar *%s* dengan *%s*.', 'sejoli'),
        '{{buyer-name}}',
        '{{point-exchange}}',
        '{{reward-name}}'
    );
?>

<?php
    printf(
        __('Silahkan dicek terlebih dahulu, apakah *%s* masih tersedia. Silahkan dicek terlebih dahulu, apakah *%s* masih tersedia. Jika belum tersedia, silahkan lakukan pembatalan penukaran poin melalui {{site-url}}wp-admin/edit.php?post_type=sejoli-reward&page=sejoli-reward-exchange', 'sejoli'),
        '{{reward-name}}',
        '{{reward-name}}'
    );
