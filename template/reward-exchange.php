<?php
sejoli_header();
$point = 0;
$point_response = sejoli_reward_get_user_point();
if(false !== $point_response['valid']) :
    $point = absint($point_response['point']->available_point);
endif;
?>
<h2 class="ui header"><?php _e('Tukar Poin', 'sejoli'); ?></h2>
<div id='sejoli-reward-list' class="ui three column doubling stackable cards item-holder masonry grid">
</div>
<script id='reward-template' type="text/x-js-render">
    <?php include 'jsrender/reward-item.php'; ?>
</script>
<script type="text/javascript">
(function($){

    'use strict';

    let sejoli_current_user_point = parseInt(<?php echo $point; ?>);

    $(document).ready(function(){

        $.ajax({
            url:    '<?php echo admin_url('admin-ajax.php'); ?>',
            type:   'POST',
            data: {
                action: 'sejoli-available-reward-table',
                nonce:  '<?php echo wp_create_nonce('sejoli-render-reward-table'); ?>'
            },
            dataType: 'json',
            beforeSend: function(){
                $('#sejoli-reward-list').block();
            },
            success: function(response) {
                $('#sejoli-reward-list').unblock()
                let tmpl = $.templates('#reward-template'),
                    html = tmpl.render({
                                rewards : response.data
                            });

                $('#sejoli-reward-list').html(html);
            }
        });

        $('body').on('click', '.sejoli-reward-exchange', function(){
            let reward_name = $(this).data('reward-name'),
                reward_id = $(this).data('reward-id'),
                reward_point = parseInt($(this).data('reward-point')),
                user_reward = sejoli_current_user_point,
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
