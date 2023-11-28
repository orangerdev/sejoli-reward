<?php
    $date = date('Y-m-d',strtotime('-29 days')) . ' - ' . date('Y-m-d');
    $export_link = add_query_arg(array(
                        'sejoli-nonce'  => wp_create_nonce('sejoli-reward-exchanges-export'),
                        'action'        => 'sejoli-reward-exchanges-csv-export'
                    ),admin_url('admin-ajax.php'));
?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Data penukaran poin', 'sejoli'); ?>
	</h1>
    <div class="sejoli-table-wrapper">
        <div class='sejoli-form-action-holder'>

            <div class="sejoli-form-filter box" style='float:right;'>
                <a href='<?php echo $export_link; ?>' name="button" class='export-csv button'><?php _e('Export CSV', 'sejoli'); ?></a>
                <button type="button" name="button" class='button toggle-search'><?php _e('Filter Data', 'sejoli'); ?></button>
                <div class="sejoli-form-filter-holder sejoli-form-float">
                    <input type="text" class='filter' name="date-range" value="<?php echo $date; ?>" placeholder="<?php _e('Pencarian berdasarkan tanggal', 'sejoli'); ?>">
                    <select class="autosuggest filter" name="reward_id"></select>
                    <select class="autosuggest filter" name="user_id"></select>

                    <button type="button" name="button" class='button button-primary do-search'><?php _e('Cari Data', 'sejoli'); ?></button>
                    <!-- <button type="button" name="button" class='button button-primary reset-search'><?php _e('Reset Pencarian', 'sejoli'); ?></button> -->
                </div>
            </div>
        </div>
        <div class="sejoli-table-holder">
            <table id="sejoli-users" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><?php _e('Tgl', 'sejoli'); ?></th>
                        <th><?php _e('User', 'sejoli'); ?></th>
                        <th><?php _e('Detil', 'sejoli'); ?></th>
                        <th><?php _e('Poin', 'sejoli'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php _e('Tgl', 'sejoli'); ?></th>
                        <th><?php _e('User', 'sejoli'); ?></th>
                        <th><?php _e('Detil', 'sejoli'); ?></th>
                        <th><?php _e('Poin', 'sejoli'); ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript">

let sejoli_table;

(function( $ ) {
	'use strict';
    $(document).ready(function() {

        sejoli.helper.select_2(
            "select[name='reward_id']",
            sejoli_admin.user_point.reward.ajaxurl,
            sejoli_admin.user_point.reward.placeholder
        );

        sejoli.helper.select_2(
            "select[name='user_id']",
            sejoli_admin.user.select.ajaxurl,
            sejoli_admin.user.placeholder
        );

        sejoli.helper.daterangepicker("input[name='date-range']");

        sejoli.helper.filterData();

        sejoli_table = $('#sejoli-users').DataTable({
            language: dataTableTranslation,
            searching: false,
            processing: false,
            serverSide: true,
            ajax: {
                type: 'POST',
                url: sejoli_admin.user_point.reward_table.ajaxurl,
                data: function(data) {
                    data.filter = sejoli.var.search;
                    data.action = 'sejoli-reward-table';
                    data.nonce = sejoli_admin.user_point.reward_table.nonce;
                    data.backend  = true;
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
                    targets:1,
                    render: function(data, type, full) {
                        let tmpl = $.templates('#user-detail');

                        return tmpl.render({
                            id : full.user_id,
                            display_name : full.display_name,
                            email : full.user_email,
                            detail_url: full.detail_url,
                        })
                    }
                },{
                    targets: 2,
                    data: 'detail',
                },{
                    targets: 3,
                    width: '80px',
                    data : 'point',
                    className: 'center'
                },{
                    targets: 4,
                    width:  '80px',
                    data: 'valid',
                    className: 'center',
                    render: function(data, type, full) {
                        if(1 == data) {
                            return '<a class="button update-valid-point" href="' + full.update_valid + '"><?php _e('Batalkan', 'sejoli-reward'); ?></a>';
                        } else {
                            return '<a class="button update-valid-point" href="' + full.update_valid + '"><?php _e('Setujui', 'sejoli-reward'); ?></a>';
                        }
                    }
                }
            ]
        });

        sejoli_table.on('preXhr',function(){
            sejoli.helper.blockUI('.sejoli-table-holder');
        });

        sejoli_table.on('xhr',function(){
            sejoli.helper.unblockUI('.sejoli-table-holder');
        });

        $(document).on('click', '.toggle-search', function(){
            $('.sejoli-form-filter-holder').toggle();
        });

        $(document).on('click', '.do-search', function(){
            sejoli.helper.filterData();
            sejoli_table.ajax.reload();
            $('.sejoli-form-filter-holder').hide();
        });

        $('body').on('click', '.update-valid-point', function(e){

            let ajaxurl = $(this).attr('href');

            $.ajax({
                url : ajaxurl,
                beforeSend: function() {
                    sejoli.helper.blockUI('.sejoli-table-holder');
                },
                success: function() {
                    sejoli.helper.unblockUI('.sejoli-table-holder');
                    sejoli_table.ajax.reload();
                }
            })
            return false;
        });

        /**
         * Do export csv
         */
        $(document).on('click', '.export-csv', function() {

            sejoli.helper.filterData();

            var link       = $(this).attr('href');
            var date_range = $('input[name="date-range"]').val();
            var reward_id  = $('select[name="reward_id"]').val();
            var user_id    = $('select[name="user_id"]').val();

            if ( link ) {
                if ( date_range ) {
                    link += '&date_range='+date_range;
                }
                if ( reward_id ) {
                    link += '&reward_id='+reward_id;
                }
                if ( user_id ) {
                    link += '&user_id='+user_id;
                }
            }

            window.location.replace(link);

            return false;

        });

    });
})(jQuery);
</script>
<script id='user-detail' type="text/x-jsrender">
{{:display_name}}
<div style='line-height:220%'>
    <span class="ui purple label"><i class="envelope icon"></i>{{:email}}</span>
</div>
</script>
