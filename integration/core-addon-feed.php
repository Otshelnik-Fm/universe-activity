<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * 1. Зарегистрируем в массив новые типы и привилегии
 * (если не указана привилегия - то видят все начиная от гостя)
 * подробнее в описании допа вкладка "Логика/Настройки" пункт "События и привилегии"
 * https://codeseller.ru/products/universe-activity/
 */
// $type['уникальный_экшен']['callback'] = 'имя_коллбек_функции';
add_filter( 'una_register_type', 'una_register_feed_addon', 7 );
function una_register_feed_addon( $type ) {
    $type['add_user_feed'] = [
        'name'     => 'Подписался на пользователя', /// Событие. "отвечая на вопрос: Что сделал"
        'source'   => 'feed', ///////////////////////// Источник (wordpress, плагин, аддон - slug аддона или имя, как в списке допов)
        'callback' => 'una_get_add_user_feed', //////// функция вывода
    ];
    $type['del_user_feed'] = [
        'name'     => 'Отписался от пользователя',
        'source'   => 'feed',
        'callback' => 'una_get_del_user_feed',
        'access'   => 'logged',
    ];

    return $type;
}

/*
 * 2. Пишем активность в бд:
 */

// подписка на юзера
add_action( 'rcl_insert_feed_data', 'una_add_user_feed', 10, 2 );
function una_add_user_feed( $feed_id, $argums ) {
    global $wpdb;

    $res = $wpdb->update( UNA_DB, // обновим строку
                          array( 'act_date' => current_time( 'mysql' ) ), array( 'user_id' => $argums['user_id'], 'action' => 'add_user_feed', 'subject_id' => $argums['object_id'] )
    );
    if ( $res > 0 ) { // были обновлены строки
        $wpdb->query( $wpdb->prepare( "DELETE FROM " . UNA_DB . " WHERE action = 'del_user_feed' AND user_id = '%d' AND subject_id = '%d'", $argums['user_id'], $argums['object_id'] ) );
    } else {
        $args['user_id']     = $argums['user_id'];
        $args['action']      = 'add_user_feed';
        $args['subject_id']  = $argums['object_id'];
        $args['object_name'] = una_get_username( $argums['object_id'] );
        $args['object_type'] = 'user';

        una_insert( $args );
    }
}

// отписался от юзера
add_action( 'rcl_pre_remove_feed', 'una_del_user_feed' );
function una_del_user_feed( $feed ) {
    $args['user_id']     = $feed->user_id;
    $args['action']      = 'del_user_feed';
    $args['subject_id']  = $feed->object_id;
    $args['object_name'] = una_get_username( $feed->object_id );
    $args['object_type'] = 'user';

    una_insert( $args );
}

/*
 * 3. Выводим в общую ленту
 * una_get_add_user_feed - зарегистрированная в 1-й функции в callback
 *
 */

/*
  // $data содержит:
  Array(
  [id] => 2834
  [user_id] => 1
  [action] => give_rating_notes
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
// Подписка на юзера
function una_get_add_user_feed( $data ) {
    $texts   = [ 'Подписался', 'Подписалась' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $link_author = '<a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';
    $out         = '<span class="una_action">' . $decline . ' на пользователя</span> ' . $link_author;
    return $out;
}

// Отписка на юзера
function una_get_del_user_feed( $data ) {
    $texts   = [ 'Отписался', 'Отписалась' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $link_author = '<a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';
    $out         = '<span class="una_action">' . $decline . ' от пользователя</span> ' . $link_author;
    return $out;
}

/*
 * 4. добавлю к кнопкам фильтрам
 *
 */

// к кнопке-фильтр "Подписки" добавлю события
add_filter( 'una_filter_subscriptions', 'una_feed_filter_button', 10 );
function una_feed_filter_button( $actions ) {
    array_push( $actions, 'add_user_feed' );

    return $actions;
}
