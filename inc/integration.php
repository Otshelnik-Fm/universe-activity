<?php

if ( ! defined( 'ABSPATH' ) )
    exit;

/*
 * под интеграции со сторонними дополнениями
 */

// когда кеширование включено вкладки под user info tab
add_action( 'rcl_construct_user-info_tab', 'una_style_one_user_load' );
function una_style_one_user_load() {
    if ( ! rcl_is_office() )
        return;

    if ( ! rcl_exist_addon( 'user-info-tab' ) )
        return;

    if ( rcl_get_option( 'use_cache', 0 ) == 1 ) {
        una_manual_start( $class = 'author_lk' );
    }
}

// выведем под User Info Tab
add_action( 'uit_footer', 'una_output_content_type' );
function una_output_content_type() {
    global $user_LK;

    $attrs = array(
        'number'        => 30,
        'include_users' => $user_LK,
        'use_name'      => 0,
        'class'         => 'una_one_user',
    );

    $out  = '';
    $user = una_get_username( $user_LK );
    if ( $user ) {
        $out = '<div class="una_title">Журнал действий ' . $user . ':</div>';
    }

    rcl_enqueue_style( 'una_one_user_style', rcl_addon_url( 'css/una_one_user.css', __FILE__ ) );

    $shrt   = new UNA_Shortcode();
    $action = $shrt->get_universe( $attrs );
    if ( ! $action )
        $action = '<div class="una_data_not_found"></div>';

    echo '<div id="una_users" class="universe_userlist">' . $out . $action . '</div>';
}

// админка. WP-Recall dashboard
add_action( 'rcl_add_dashboard_metabox', 'una_add_custom_metabox' );
function una_add_custom_metabox( $screen ) {
    add_meta_box( 'rcl-custom-metabox', 'Последняя активность', 'una_custom_metabox', $screen->id, 'normal' );
}

function una_custom_metabox() {
    $attrs = array(
        'number'        => 30,
        'no_pagination' => 1,
    );

    wp_enqueue_style( 'una_style', rcl_addon_url( 'css/una_core_style.css', __FILE__ ) );

    $shrt   = new UNA_Shortcode();
    $action = $shrt->get_universe( $attrs );
    if ( ! $action )
        $action = '<div class="una_data_not_found"></div>';

    echo '<div id="una_users" class="universe_userlist una_admin_dashboard">' . $action . '</div>';
}
