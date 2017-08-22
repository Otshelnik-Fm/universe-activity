<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// под интеграции со сторонними дополнениями


// выведем под User Info Tab
function una_output_content_type(){
    global $user_LK;

    $attrs = array(
                'number' => 30,
                'include_users' => $user_LK,
                'use_name' => 0,
                'class' => 'una_one_user',
            );

    $out = '';
    $user = get_userdata($user_LK);
    if($user){
        $out = '<div class="una_title">Журнал действий '.$user->get('display_name').':</div>';
    }

    rcl_enqueue_style('una_one_user_style',rcl_addon_url('css/una_one_user.css', __FILE__));

    $shrt = new UNA_Shortcode();
    $action = $shrt->get_universe($attrs);
    if(!$action) $action = '<div class="una_data_not_found"></div>';

    echo '<div id="una_users" class="universe_userlist">'.$out.$action.'</div>';
}
add_action('uit_footer', 'una_output_content_type');



// админка. WP-Recall dashboard
function una_add_custom_metabox($screen){
    add_meta_box( 'rcl-custom-metabox', 'Последняя активность', 'una_custom_metabox', $screen->id, 'normal' );
}
add_action('rcl_add_dashboard_metabox', 'una_add_custom_metabox');

function una_custom_metabox(){
    $attrs = array(
                'number' => 30,
                'no_pagination' => 1,
            );

    wp_enqueue_style('una_style',rcl_addon_url('una-style.css', __FILE__));

    $shrt = new UNA_Shortcode();
    $action = $shrt->get_universe($attrs);
    if(!$action) $action = '<div class="una_data_not_found"></div>';

    echo '<div id="una_users" class="universe_userlist una_admin_dashboard">'.$action.'</div>';
}

