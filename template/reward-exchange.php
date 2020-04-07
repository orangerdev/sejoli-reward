<?php
sejoli_header();
$point = 0;
$point_response = sejoli_reward_get_user_point();
if(false !== $point_response['valid']) :
    $point = absint($point_response['point']->available_point);
endif;
?>
<h2 class="ui header"><?php _e('Tukar Poin', 'sejoli'); ?></h2>
<table id="sejoli-reward" class="ui striped single line table" style="width:100%;word-break: break-word;white-space: normal;">
    <thead>
        <tr>
            <th><?php _e('Reward', 'sejoli'); ?></th>
            <th><?php _e('Poin', 'sejoli'); ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    </tbody>
    <tfoot>
        <tr>
            <th><?php _e('Reward', 'sejoli'); ?></th>
            <th><?php _e('Poin', 'sejoli'); ?></th>
            <th></th>
        </tr>
    </tfoot>
</table>
<script type="text/javascript">
(function($){

    'use strict';

    let sejoli_table;

    $(document).ready(function(){

        sejoli_table = $('#sejoli-reward').DataTable({
            language: dataTableTranslation,
            searching: true,
            processing: true,
            serverSide: false,
            info: false,
            pagination: false,
            ajax: {
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: function(data) {
                    data.action = 'sejoli-available-reward-table';
                    data.nonce = '<?php echo wp_create_nonce('sejoli-render-reward-table'); ?>';
                }
            },
            order: [
                [ 1, "desc" ]
            ],
            columnDefs: [
                {
                    targets: [0, 2],
                    orderable: false
                },{
                    targets: 0,
                    data: 'id',
                    render: function(data, meta, full) {
                        let tmpl = $.templates('#reward-detail');
                        return tmpl.render(full);
                    }
                },{
                    targets: 1,
                    data: 'point',
                    width: '80px',
                    className: 'price',
                },{
                    targets: 2,
                    width:  '160px',
                    className: 'center',
                    render: function(data, meta, full) {
                        return '<button class="small ui button blue sejoli-reward-exchange" data-reward-id="' + full.id  + '" data-reward-name="' + full.title + '" data-reward-point="' + full.point + '"><?php echo _e('Tukar Poin', 'sejoli'); ?></button>'
                    }
                }
            ]
        });

        $('body').on('click', '.sejoli-reward-exchange', function(){
            let reward_name = $(this).data('reward-name'),
                reward_id = $(this).data('reward-id'),
                reward_point = parseInt($(this).data('reward-point')),
                user_reward = parseInt(<?php echo $point; ?>),
                confirmed = confirm('<?php _e('Anda yakin akan menukar point anda dengan hadiah ', 'sejoli'); ?>' + reward_name + '?'),
                button = $(this);

            if(confirmed) {
                if(user_reward < reward_point) {
                    alert('<?php _e('Maaf, poin anda tidak mencukupi untuk ditukar dengan hadiah', 'sejoli'); ?> ' + reward_name);
                } else {
                    $.ajax({
                        url : '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'sejoli-reward-exchange',
                            reward_id : reward_id,
                            nonce: '<?php echo wp_create_nonce('sejoli-reward-exchange'); ?>'
                        },
                        beforeSend: function() {
                            $('#sejoli-reward').block();
                            button.attr('disabled', true);
                        },
                        success: function(response) {
                            $('#sejoli-reward').unblock();
                            button.attr('disabled', false);
                            alert(response.message);

                            if(response.valid) {
                                location.reload();
                            }
                        }
                    });
                }
            }

            return false;
        });
    });

})(jQuery);
</script>
<script id='reward-detail' type="text/x-jsrender">
<div class="reward-detail">
    {{if image}}
    <figure>
        <img src='{{:image}}' alt='' />
    </figure>
    {{/if}}
    <div class="reward-content">
        <h3 class='reward-title'>{{:title}}</h3>
        <div class="reward-description">
            {{:content}}
        </div>
    </div>
</div>
</script>
<?php
sejoli_footer();
