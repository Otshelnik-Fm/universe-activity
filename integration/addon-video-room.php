<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/*
 * 1. Зарегистрируем в массив новый тип
 * (если не указана привелегия - то видят все начиная от гостя)
 * подробнее в описании допа вкладка "Логика/Настройки" пункт "События и привилегии"
 * https://codeseller.ru/products/universe-activity/
 */
// $type['уникальный_экшен']['callback'] = 'имя_коллбек_функции';
add_filter( 'una_register_type', 'una_register_vroom_addon', 10 );
function una_register_vroom_addon( $type ) {
    $type['vrm_add_video']['callback'] = 'una_get_vrm_add';  // добавил видео

    return $type;
}

/*
 * 2. Пишем активность в бд:
 */
add_action( 'save_post_video', 'una_add_vrm_video', 10, 3 );
function una_add_vrm_video( $post_id, $post, $update ) {
    if ( $update )
        return;

    $args['action']      = 'vrm_add_video';  // тот самый уникальный экшен
    $args['object_id']   = $post->ID;
    $args['object_name'] = $post->post_title;
    $args['object_type'] = 'video';

    una_insert( $args );                    // запишем в бд
}

/*
 * 3. Выводим в общую ленту
 * una_get_vrm_add - зарегистрированная в 1-й функции в callback
 *
 */

/*
  // $data содержит:
  Array(
  [id] => 2834
  [user_id] => 1
  [action] => vrm_add_video
  [act_date] => 2018-01-17 19:24:30
  [object_id] => 45692
  [object_name] => Заголовок
  [object_type] => video
  [subject_id] => 0
  [other_info] =>
  [user_ip] => 3.52.235.164
  [display_name] =>
  [post_status] =>
  ) */
function una_get_vrm_add( $data ) {
    $object = '<div class="una_p_link una_media_box gallery-attachment-' . $data['object_id'] . ' gallery-attachment parent-preloader">';
    $object .= '<a class="una_p_link" href="#" onclick="rcl_preloader_show(jQuery(this));rcl_ajax({data:{action:\'vrm_call_video\',post_id:' . $data['object_id'] . '}});return false;">';
    $object .= get_the_post_thumbnail( $data['object_id'], 'medium', [ 'class' => 'una_p_link' ] );
    $object .= '</a>';
    $object .= '</div>';

    $decline = una_decline_by_sex( $data['user_id'], [ 'Добавил', 'Добавила' ] );

    return '<span class="una_action">' . $decline . ' видео:</span> ' . $data['object_name'] . $object;
}

/*
 * 4. Я добавлю также к кнопкам фильтрам
 *
 */
// к кнопке-фильтр "Публикации" добавлю
add_filter( 'una_filter_publications', 'una_add_vrm_filter_button_publications', 10 );
function una_add_vrm_filter_button_publications( $actions ) {
    array_push( $actions, 'vrm_add_video' );

    return $actions;
}

// обновим (изменим) для video room тайтл в UNA
/*
  Array\n(
 * [post_title] => Heres To Us (Halestorm)
 * [post_content] => Audio/Video by Andrei Cerbu/Robert Ciubotaru
 * [post_id] => 7741
 * [action] => vrm_save_video_data
 * [ajax_nonce] => 9d776996c2
 * )

 */
add_action( 'init', 'una_update_title_video_room', 30 );
function una_update_title_video_room() {
    if ( $_POST['action'] !== 'vrm_save_video_data' )
        return;

    $post_id = intval( $_POST['post_id'] );

    if ( ! $post_id || empty( $post_id ) )
        return;

    $post_title = sanitize_text_field( $_POST['post_title'] );

    if ( ! $post_title || empty( $post_title ) )
        return;

    global $user_ID;

    $new_data = array( 'object_name' => $post_title );
    $where    = array( 'user_id' => $user_ID, 'action' => 'vrm_add_video', 'object_id' => $post_id, 'object_type' => 'video' );

    una_update( $new_data, $where );
}
