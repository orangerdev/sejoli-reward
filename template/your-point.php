<?php sejoli_header(); ?>
<h2 class="ui header"><?php _e('Poin Anda', 'sejoli'); ?></h2>
<table id="sejoli-point" class="ui striped single line table" style="width:100%;word-break: break-word;white-space: normal;">
    <thead>
        <tr>
            <th><?php _e('Tanggal', 'sejoli'); ?></th>
            <th><?php _e('Detil', 'sejoli'); ?></th>
            <th><?php _e('Poin', 'sejoli'); ?></th>
            <th><?php _e('Tipe', 'sejoli'); ?></th>
        </tr>
    </thead>
    <tbody>
    </tbody>
    <tfoot>
        <tr>
            <th><?php _e('Tanggal', 'sejoli'); ?></th>
            <th><?php _e('Detil', 'sejoli'); ?></th>
            <th><?php _e('Poin', 'sejoli'); ?></th>
            <th><?php _e('Tipe', 'sejoli'); ?></th>
        </tr>
    </tfoot>
</table>
<script type="text/javascript">
(function($){

    'use strict';

    let sejoli_table;

    $(document).ready(function(){

        sejoli_table = $('#sejoli-point').DataTable({
            language: dataTableTranslation,
            searching: false,
            processing: false,
            serverSide: true,
            ajax: {
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: function(data) {
                    data.action = 'sejoli-single-user-point-table';
                    data.nonce = '<?php echo wp_create_nonce('sejoli-render-single-user-point-table'); ?>';
                }
            },
            pageLength : 50,
            lengthMenu : [
                [50, 100, 200],
                [50, 100, 200],
            ],
            order: [
                [ 0, "desc" ]
            ],
            columnDefs: [
                {
                    targets: [1, 2, 3],
                    orderable: false
                },{
                    targets: 0,
                    width: '80px',
                    data : 'created_at',
                    className: 'center'
                },{
                    targets: 1,
                    data: 'detail',
                },{
                    targets: 2,
                    width: '80px',
                    data : 'point',
                    className: 'center'
                },{
                    targets: 3,
                    width:  '80px',
                    data: 'type',
                    className: 'center',
                    render: function(data) {
                        if('in' === data) {
                            return '<label class="ui green label">Tambah</label>';
                        } else {
                            return '<label class="ui yellow label">Kurang</label>';
                        }
                    }
                }
            ]
        });
    });

})(jQuery);
</script>
<?php
sejoli_footer();
