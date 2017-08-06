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

    $user = get_userdata($user_LK);
    if($user){
        $out = '<div class="una_title">Журнал действий '.$user->get('display_name').':</div>';
    }

    rcl_enqueue_style('una_one_user_style',rcl_addon_url('css/una_one_user.css', __FILE__));

    $shrt = new UNA_shortcode();
    $action = $shrt->get_universe($attrs);
    if(!$action) $action = '<div class="una_data_not_found"></div>';

    echo '<div id="una_users" class="universe_userlist">'.$out.$action.'</div>';
}
add_action('uit_footer', 'una_output_content_type');



