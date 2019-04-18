<?php

/*

  ╔═╗╔╦╗╔═╗╔╦╗
  ║ ║ ║ ╠╣ ║║║ https://otshelnik-fm.ru
  ╚═╝ ╩ ╚  ╩ ╩


 * ***   Возможности:   ****
  в Readme файле

  @todo разобраться с login_failed их может быть нес-ко тысяч. Пока вырубил

 */



// БД
add_action( 'init', 'una_define_constant', 5 );
function una_define_constant() {
    if ( defined( 'UNA_DB' ) )
        return false;

    global $wpdb;

    define( 'UNA_DB', $wpdb->base_prefix . 'otfm_universe_activity' );
}

// подключим файлы
require_once 'inc/fires.php';                   // хуки
require_once 'inc/callbacks.php';               // колбэки
require_once 'inc/functions.php';               // все функции
require_once 'inc/integration.php';             // интеграции
require_once 'inc/addon-settings.php';          // настройки
require_once 'inc/class-una-query.php';         // класс регистрирущий нашу таблицу
require_once 'inc/class-una-shortcode.php';     // шорткод


/*
 * Интеграции
 *
 * пока набиваю так, как накопится критическая масса перепишу 💩
 */


// плагин "Asgaros Forum" https://wordpress.org/plugins/asgaros-forum/
if ( class_exists( 'AsgarosForum' ) ) {
    require_once 'integration/plugin-asgaros.php';
}

// доп "Asgaros Forum + WP-Recall" https://codeseller.ru/?p=13693
if ( rcl_exist_addon( 'rcl-asgaros' ) ) {
    require_once 'integration/addon-asgaros-forum-to-wp-recall.php';
}

//
if ( rcl_exist_addon( 'country-and-city-in-profile-pro' ) ) {
    require_once 'integration/addon-country-and-city-in-profile-pro.php';
}

// доп "Birthday in Profile" https://codeseller.ru/?p=13377
if ( rcl_exist_addon( 'birthday-in-profile' ) ) {
    require_once 'integration/addon-birthday-in-profile.php';
}

// доп "Bot User Info" https://codeseller.ru/?p=17458
if ( rcl_exist_addon( 'bot-user-info' ) ) {
    require_once 'integration/addon-bot-user-info.php';
}

// доп "Subscription Two" https://codeseller.ru/?p=16774
if ( rcl_exist_addon( 'subscription-two' ) ) {
    require_once 'integration/addon-subscription-two.php';
}

// доп "Pretty URL Author" https://codeseller.ru/?p=13784
if ( rcl_exist_addon( 'pretty-url-author' ) ) {
    require_once 'integration/addon-pretty-url-author.php';
}

// доп "Groups Theme RePlace"
if ( rcl_exist_addon( 'groups-theme-replace' ) ) {
    require_once 'integration/addon-groups-theme-replace.php';
}

// доп "Group New Post Notify"
if ( rcl_exist_addon( 'group-new-post-notify' ) ) {
    require_once 'integration/addon-group-new-post-notify.php';
}


// доп "Group New Post Notify"
if ( rcl_exist_addon( 'bookmarks' ) ) {
    require_once 'integration/addon-bookmarks.php';
}

