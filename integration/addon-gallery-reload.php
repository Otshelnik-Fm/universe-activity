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
add_filter( 'una_register_type', 'una_register_grcl_addon', 10 );
function una_register_grcl_addon( $type ) {
    $type['grcl_add_pic']['callback'] = 'una_get_grcl_add';  // добавил в галерею картинку

    return $type;
}

/*
 * 2. Пишем активность в бд:
 */
add_action( 'add_attachment', 'una_add_grcl_add_picture' );
function una_add_grcl_add_picture( $post_id ) {
    $data = get_post( $post_id );

    // это не картинки (аудио галерея имеет тоже только тип audio/mpeg)
    if ( ! in_array( $data->post_mime_type, [ 'image/jpeg', 'image/jpg', 'image/gif', 'image/png' ] ) )
        return;

    // не наше медиа
    if ( ! in_array( $data->post_excerpt, [ 'rcl-uploader:gallery_reload', 'rcl-uploader:gallery_reload_multiple' ] ) )
        return;

    $args['action']      = 'grcl_add_pic';  // тот самый уникальный экшен
    $args['object_id']   = $data->ID;
    $args['object_name'] = $data->post_title;
    $args['object_type'] = 'gallery';

    una_insert( $args );                    // запишем в бд
}

/*
 * 3. Выводим в общую ленту
 * una_get_grcl_add - зарегистрированная в 1-й функции в callback
 *
 */

/*
  // $data содержит:
  Array(
  [id] => 2834
  [user_id] => 1
  [action] => grcl_add_pic
  [act_date] => 2018-01-17 19:24:30
  [object_id] => 45865
  [object_name] => Название
  [object_type] => gallery
  [subject_id] => 0
  [other_info] =>
  [user_ip] => 3.52.235.164
  [display_name] =>
  [post_status] =>
  ) */
function una_get_grcl_add( $data ) {
    $object = '<div class="una_p_link una_media_box gallery-attachment-' . $data['object_id'] . ' gallery-attachment parent-preloader">';
    $object .= '<a class="una_p_link" href="#" onclick="rcl_preloader_show(jQuery(this));rcl_ajax({data:{action:\'glrd_call_attachment\',post_id:' . $data['object_id'] . '}});return false;">';
    $object .= wp_get_attachment_image( $data['object_id'], 'medium', [ 'class' => 'una_p_link' ] );
    $object .= '</a>';
    $object .= '</div>';

    $decline = una_decline_by_sex( $data['user_id'], [ 'Добавил', 'Добавила' ] );

    return '<span class="una_action">' . $decline . ' изображение:</span> ' . $data['object_name'] . $object;
}

/*
 * 4. Я добавлю также к кнопкам фильтрам
 *
 */
// к кнопке-фильтр "Публикации" добавлю
add_filter( 'una_filter_publications', 'una_add_grcl_filter_button_publications', 10 );
function una_add_grcl_filter_button_publications( $actions ) {
    array_push( $actions, 'grcl_add_pic' );

    return $actions;
}

/*
 * 5. обновим (изменим) для галереи тайтл в UNA
 */
/*
  Array(
 * [media_title] => Heres To Us (Halestorm)
 * [media_content] => some
 * [media_id] => 7741
 * [action] => glrd_save_media_data
 * )

 */
add_action( 'init', 'una_update_title_gallery_reload', 30 );
function una_update_title_gallery_reload() {
    if ( $_POST['action'] !== 'glrd_save_media_data' )
        return;

    $post_id = intval( $_POST['media_id'] );

    if ( ! $post_id || empty( $post_id ) )
        return;

    $post_title = sanitize_text_field( $_POST['media_title'] );

    if ( ! $post_title || empty( $post_title ) )
        return;

    global $user_ID;

    $new_data = array( 'object_name' => $post_title );
    $where    = array( 'user_id' => $user_ID, 'action' => 'grcl_add_pic', 'object_id' => $post_id, 'object_type' => 'gallery' );

    una_update( $new_data, $where );
}

// при удалении картинки - удалим строку активности
add_action( 'delete_attachment', 'una_delete_gallery_reload' );
function una_delete_gallery_reload( $post_id ) {
    $data = get_post( $post_id );

    if ( ! $data )
        return;

    // не наше медиа
    if ( $data->post_excerpt != 'rcl-user-media' )
        return;

    // это не картинки (аудио галерея имеет тоже только тип audio/mpeg)
    if ( ! in_array( $data->post_mime_type, [ 'image/jpeg', 'image/jpg', 'image/gif', 'image/png' ] ) )
        return;


//    if ( ! in_array( $data->post_excerpt, [ 'rcl-uploader:gallery_reload', 'rcl-uploader:gallery_reload_multiple' ] ) )
//        return;



    global $wpdb;

    $wpdb->query( $wpdb->prepare( "DELETE FROM " . UNA_DB . " WHERE action = 'grcl_add_pic' AND object_id = '%d' ", $post_id ) );
}
