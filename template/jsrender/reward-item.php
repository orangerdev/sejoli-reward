{{props rewards}}
<div class="column">
    <div class="ui fluid card">

        {{if prop.image }}
        <div class="image">
            <img src="{{:prop.image}}" alt="{{:prop.title}}">
        </div>
        {{/if}}
        <div class="content">
            <div class="header">{{:prop.title}}</div>
            <div class="description">
                {{:prop.content}}
            </div>
        </div>
        <div class="extra content">
            <i class="gift icon"></i>
            {{:prop.point}} <?php _e('Poin', 'sejoli'); ?>
        </div>
        <button class="ui blue bottom attached button sejoli-reward-exchange" data-reward-id='{{:prop.id}}' data-reward-name='{{:prop.title}}' data-reward-point='{{:prop.point}}'>
            <i class='add icon'></i>
            <?php _e('Tukar Poin', 'sejoli'); ?>
        </button>

    </div>
</div>
{{else}}
<div class="column">
    <div class="ui fluid card">
        <div class="content">
            <p><?php _e('Tidak ada data yang bisa ditampilkan','sejoli'); ?></p>
        </div>
    </div>
</div>
{{/props}}
