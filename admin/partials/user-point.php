<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Data Poin User', 'sejoli'); ?>
	</h1>
    <div class="sejoli-table-wrapper">
        <div class='sejoli-form-action-holder'>

            <div class="sejoli-form-filter box" style='float:right;'>
                <button type="button" name="button" class='export-csv button'><?php _e('Export CSV', 'sejoli'); ?></button>
                <button type="button" name="button" class='button toggle-search'><?php _e('Filter Data', 'sejoli'); ?></button>
                <div class="sejoli-form-filter-holder sejoli-form-float">
                    <select class="autosuggest filter" name="user_id"></select>
                    <?php wp_nonce_field('search-user', 'sejoli-nonce'); ?>
                    <button type="button" name="button" class='button button-primary do-search'><?php _e('Cari Data', 'sejoli'); ?></button>
                    <!-- <button type="button" name="button" class='button button-primary reset-search'><?php _e('Reset Pencarian', 'sejoli'); ?></button> -->
                </div>
            </div>
        </div>
        <div class="sejoli-table-holder">
            <table id="sejoli-users" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><?php _e('User', 'sejoli'); ?></th>
                        <th><?php _e('Poin Masuk', 'sejoli'); ?></th>
                        <th><?php _e('Poin Keluar', 'sejoli'); ?></th>
                        <th><?php _e('Sisa Poin', 'sejoli'); ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php _e('User', 'sejoli'); ?></th>
                        <th><?php _e('Poin Masuk', 'sejoli'); ?></th>
                        <th><?php _e('Poin Keluar', 'sejoli'); ?></th>
                        <th><?php _e('Sisa Poin', 'sejoli'); ?></th>
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
            "select[name='user_id']",
            sejoli_admin.user.select.ajaxurl,
            sejoli_admin.user.placeholder
        );

        sejoli.helper.filterData();

        sejoli_table = $('#sejoli-users').DataTable({
            language: dataTableTranslation,
            searching: false,
            processing: true,
            serverSide: false,
            info: false,
            paging: false,
            ajax: {
                type: 'POST',
                url: sejoli_admin.user_point.table.ajaxurl,
                data: function(data) {
                    data.filter = sejoli.var.search;
                    data.action = 'sejoli-user-point-table';
                    data.nonce = sejoli_admin.user_point.table.nonce
                    data.backend  = true;
                }
            },
            pageLength : 50,
            lengthMenu : [
                [10, 50, 100, 200],
                [10, 50, 100, 200],
            ],
            order: [
                [ 3, "desc" ]
            ],
            columnDefs: [
                {
                    targets: [0],
                    orderable: false
                },{
                    targets: 0,
                    data : 'display_name',
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
                    targets: 1,
                    width: '80px',
                    data: 'added_point',
                    className: 'center'
                },{
                    targets: 2,
                    width: '80px',
                    data : 'reduce_point',
                    className: 'center'
                },{
                    targets: 3,
                    width:  '80px',
                    data: 'available_point',
                    className: 'center'
                }
            ],
            initComplete: function(settings, json) {
                // $('.sejoli-full-widget .orange .content.value').html(sejoli_admin.text.currency + sejoli.helper.formatPrice(json.info.pending_commission));
                // $('.sejoli-full-widget .green .content.value').html(sejoli_admin.text.currency + sejoli.helper.formatPrice(json.info.unpaid_commission));
                // $('.sejoli-full-widget .blue .content.value').html(sejoli_admin.text.currency + sejoli.helper.formatPrice(json.info.paid_commission));
            }
        });

        sejoli_table.on('preXhr',function(){
            sejoli.helper.blockUI('.sejoli-table-holder');
        });

        sejoli_table.on('xhr',function(){
            sejoli.helper.unblockUI('.sejoli-table-holder');
        });

    });
})(jQuery);
</script>
<script id='user-detail' type="text/x-jsrender">
<a type='button' class='ui mini button' href='{{:detail_url}}' target='_blank'>DETAIL</a> {{:display_name}}
<div style='line-height:220%'>
    <span class="ui purple label"><i class="envelope icon"></i>{{:email}}</span>
</div>
</script>
