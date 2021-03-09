<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/*
 * 1. Зарегистрируем в массив новый тип
 * (если не указана привилегия - то видят все начиная от гостя)
 * подробнее в описании допа вкладка "Логика/Настройки" пункт "События и привилегии"
 * https://codeseller.ru/products/universe-activity/
 */
// $type['уникальный_экшен']['callback'] = 'имя_коллбек_функции';
add_filter( 'una_register_type', 'una_register_splay_addon', 10 );
function una_register_splay_addon( $type ) {
    $type['splay_add'] = [
        'name'     => 'Добавил аудио', /// Событие. "отвечая на вопрос: Что сделал"
        'source'   => 'soundplay', /////// Источник (wordpress, плагин, аддон - slug аддона или имя, как в списке допов)
        'callback' => 'una_get_splay', /// функция вывода
    ];

    return $type;
}

/*
 * 2. Пишем активность в бд:
 */
add_action( 'sp_new_sound_collection', 'una_add_splay', 30 );
function una_add_splay( $increm_id ) {
    $datas = una_splay_get_sound_collections( $increm_id );

    if ( ! $datas )
        return;

    $args['action']      = 'splay_add';  // тот самый уникальный экшен
    $args['object_id']   = $datas->sound_id;
    $args['object_name'] = $datas->sound_name;
    $args['object_type'] = 'audio';

    una_insert( $args );                 // запишем в бд
}

// получаем название трека
function una_splay_get_sound_collections( $sound_id ) {
    global $wpdb, $user_ID;

    return $wpdb->get_row( "SELECT * FROM " . RCL_PREF . "sp_collections WHERE ID='$sound_id' AND user_id='$user_ID'" );
}

/*
 * 3. Выводим в общую ленту
 * una_get_splay - зарегистрированная в 1-й функции в callback
 *
 */

/*
  // $data содержит:
  Array(
  [id] => 5698
  [user_id] => 1
  [action] => splay_add
  [act_date] => 2020-12-03 21:51:48
  [object_id] => 53
  [object_name] => commandos-2020
  [object_type] => audio
  [subject_id] => 0
  [other_info] =>
  [user_ip] => 78.106.77.218
  [hide] => 0
  [group_id] => 0
  [display_name] => Анжелика Red
  [post_status] =>
  ) */
function una_get_splay( $data ) {
    $sound = sp_get_sound( $data['object_id'] );

    $object = '<div class="una_p_link una_media_box single-sound">';
    $object .= do_shortcode( '[audio mp3="' . $sound->sound_guid . '"][/audio]' );
    $object .= '</div>';

    $decline = una_decline_by_sex( $data['user_id'], [ 'Добавил', 'Добавила' ] );

    return '<span class="una_action">' . $decline . ' аудио:</span> ' . $data['object_name'] . $object;
}

/*
 * 4. Я добавлю также к кнопкам фильтрам
 *
 */
// к кнопке-фильтр "Публикации" добавлю
add_filter( 'una_filter_publications', 'una_add_splay_filter_button_publications', 10 );
function una_add_splay_filter_button_publications( $actions ) {
    array_push( $actions, 'splay_add' );

    return $actions;
}

/*
 * 5. обновим (изменим) тайтл в UNA
 *
 */
add_action( 'init', 'una_update_title_splay', 30 );
function una_update_title_splay() {
    if ( ! isset( $_POST['action'] ) || $_POST['action'] !== 'sp_ajax_rename_sound' )
        return;

    $sound_id = intval( $_POST['sound_id'] );

    if ( ! $sound_id || empty( $sound_id ) )
        return;

    $post_title = sanitize_text_field( $_POST['filename'] );

    if ( ! $post_title || empty( $post_title ) )
        return;

    global $user_ID;

    $new_data = array( 'object_name' => $post_title );
    $where    = array( 'user_id' => $user_ID, 'action' => 'splay_add', 'object_id' => $sound_id, 'object_type' => 'audio' );

    una_update( $new_data, $where );
}

// при удалении аудио - удалим строку активности
add_action( 'sp_delete_sound', 'una_delete_splay' );
function una_delete_splay( $sound ) {
    if ( ! $sound && ! $sound->ID )
        return;

    global $wpdb, $user_ID;

    if ( $user_ID && $user_ID != 0 ) {
        $wpdb->query( $wpdb->prepare( "DELETE FROM " . UNA_DB . " WHERE action = 'splay_add' AND user_id = '%d'  AND object_id = '%d' ", ( int ) $user_ID, $sound->ID ) );
    }
}
