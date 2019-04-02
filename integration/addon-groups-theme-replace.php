<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/*
 * 1. Зарегистрируем в массив новые типы и привелегии
 * (если не указана привелегия - то видят все начиная от гостя)
 * подробнее в описании допа вкладка "Логика/Настройки" пункт "События и привилегии"
 * https://codeseller.ru/products/universe-activity/
 */
// $type['уникальный_экшен']['callback'] = 'имя_коллбек_функции';
add_filter( 'una_register_type', 'una_register_gtr_addon', 10 );
function una_register_gtr_addon( $type ) {
    $type['add_group_avatar']['callback'] = 'una_get_gtr_add_group_avatar';     // установил аву
    $type['add_group_cover']['callback']  = 'una_get_gtr_add_group_cover';      // установил обложку
    $type['group_change_exc']['callback'] = 'una_get_group_change_exc';         // статус

    return $type;
}

/*
 * 2. Пишем активность в бд:
 */

// добавил/сменил аву
add_action( 'gtr_add_ava', 'una_add_group_avatar', 10 );
function una_add_group_avatar( $group_id ) {
    $sql = "SELECT group_id FROM " . UNA_DB . " WHERE action = 'add_group_avatar' AND object_type = 'group' AND group_id = %d";
    $ava = una_get_var( $sql, $group_id );

    // ава уже зафиксирована системой
    if ( $ava ) {
        $new_data = array( 'act_date' => current_time( 'mysql' ) );
        $where    = array( 'action' => 'add_group_avatar', 'object_type' => 'group', 'group_id' => $group_id );
        una_update( $new_data, $where );
    } else {
        $termdata = get_term( $group_id );

        $args['action']      = 'add_group_avatar';
        $args['object_id']   = $group_id;
        $args['object_name'] = $termdata->name;
        $args['object_type'] = 'group';
        $args['group_id']    = $group_id;

        una_insert( $args );
    }
}

// добавил/сменил обложку
add_action( 'gtr_add_cover', 'una_add_group_cover', 10 );
function una_add_group_cover( $group_id ) {
    $sql   = "SELECT group_id FROM " . UNA_DB . " WHERE action = 'add_group_cover' AND object_type = 'group' AND group_id = %d";
    $cover = una_get_var( $sql, $group_id );

    // обложка уже зафиксирована системой
    if ( $cover ) {
        $new_data = array( 'act_date' => current_time( 'mysql' ) );
        $where    = array( 'action' => 'add_group_cover', 'object_type' => 'group', 'group_id' => $group_id );
        una_update( $new_data, $where );
    } else {
        $termdata = get_term( $group_id );

        $args['action']      = 'add_group_cover';
        $args['object_id']   = $group_id;
        $args['object_name'] = $termdata->name;
        $args['object_type'] = 'group';
        $args['group_id']    = $group_id;

        una_insert( $args );
    }
}

// добавил/изменил статус группы
add_action( 'rcl_update_group', 'una_set_excerpt_group' );
function una_set_excerpt_group( $data ) {
    if ( ! isset( $data['gtr_excerpt'] ) || empty( $data['gtr_excerpt'] ) )
        return false;

    $input_excerpt      = sanitize_textarea_field( $data['gtr_excerpt'] );
    $input_excerpt_hash = wp_hash( $input_excerpt );

    $sql          = "SELECT other_info FROM " . UNA_DB . " WHERE action = 'group_change_exc' AND group_id = %d ORDER BY act_date DESC";
    $current_hash = una_get_var( $sql, $data['group_id'] );

    // нет еще в событиях строки. Создадим его
    if ( empty( $current_hash ) ) {
        $args['action']      = 'group_change_exc';
        $args['object_id']   = $data['group_id'];
        $args['object_name'] = $data['name'];
        $args['object_type'] = 'group';
        $args['group_id']    = $data['group_id'];
        $args['other_info']  = $input_excerpt_hash;

        una_insert( $args );
    } else {
        // данные равны с теми что пришли
        if ( $input_excerpt_hash == $current_hash )
            return false;

        $new_data = array( 'other_info' => $input_excerpt_hash, 'object_name' => $data['name'], 'act_date' => current_time( 'mysql' ) );
        $where    = array( 'action' => 'group_change_exc', 'group_id' => $data['group_id'] );
        una_update( $new_data, $where );
    }
}

/*
 * 3. Выводим в общую ленту
 * una_get_gtr_add_group_avatar - зарегистрированная в 1-й функции в callback
 *
 */

/*
  // $data содержит:
  Array(
  [id] => 3442
  [user_id] => 1
  [action] => add_group_avatar
  [act_date] => 2019-02-06 18:32:18
  [object_id] => 86
  [object_name] => Открытая 2019
  [object_type] => group
  [subject_id] => 0
  [other_info] =>
  [user_ip] => 128.70.201.125
  [hide] => 0
  [group_id] => 86
  [display_name] => Wladimir (Otshelnik-Fm)
  [post_status] =>
  ) */
function una_get_gtr_add_group_avatar( $data ) {
    $avatar_id = rcl_get_group_option( $data['group_id'], 'avatar_id' );

    $medium = image_downsize( $avatar_id );
    $full   = wp_get_attachment_url( $avatar_id );

    $name  = '<a class="una_group_name" href="/?una_group_url=' . $data['group_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';
    $cover = '<a class="mpr_image una_avatar" href="' . $full . '" title="Аватарка группы: ' . $data['object_name'] . '<br>Загружена: ' . $data['act_date'] . '"><img style="max-height: 250px;display: block;" src="' . $medium[0] . '" alt=""></a>';

    return '<span class="una_action">Установил аватарку в группе</span> ' . $name . ' ' . $cover;
}

// Установил обложку в группе
function una_get_gtr_add_group_cover( $data ) {
    $cover_id = rcl_get_group_option( $data['group_id'], 'gtr_cover_id' );

    $medium = image_downsize( $cover_id );
    $full   = wp_get_attachment_url( $cover_id );

    $name  = '<a class="una_group_name" href="/?una_group_url=' . $data['group_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';
    $cover = '<a class="mpr_image una_avatar" href="' . $full . '" title="Обложка группы: ' . $data['object_name'] . '<br>Загружена: ' . $data['act_date'] . '"><img style="max-height: 250px;display: block;" src="' . $medium[0] . '" alt=""></a>';

    return '<span class="una_action">Установил обложку в группе</span> ' . $name . ' ' . $cover;
}

// установил в группе статус, сменил его
function una_get_group_change_exc( $data ) {
    $excerpt = rcl_get_group_option( $data['group_id'], 'gtr_excerpt' );
    $name    = '<a class="una_group_name" href="/?una_group_url=' . $data['group_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';

    return '<span class="una_action">Новый статус группы:</span> ' . $name . '<div class="una_user_status"><div>' . $excerpt . '</div></div>';
}

/*
 * 4. Я добавлю также к кнопкам фильтрам
 *
 */
// к кнопке-фильтр "обновления" добавлю пару событий
add_filter( 'una_filter_updates', 'una_add_gtr_filter_button_updates', 10 );
function una_add_gtr_filter_button_updates( $actions ) {
    array_push( $actions, 'add_group_avatar', 'group_change_exc', 'add_group_cover' );

    return $actions;
}
