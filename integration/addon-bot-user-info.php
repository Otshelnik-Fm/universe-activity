<?php

if ( ! defined( 'ABSPATH' ) )
    exit;


/*
 * 1. Зарегистрируем в массив новые типы и привелегии
 * (если не указана привелегия - то видят все начиная от гостя)
 * подробнее в описании допа вкладка "Логика/Настройки" пункт "События и привилегии"
 * https://codeseller.ru/products/universe-activity/
 */
// $type['уникальный_экшен']['callback'] = 'имя_коллбек_функции';
add_filter( 'una_register_type', 'una_register_bui_addon', 10 );
function una_register_bui_addon( $type ) {
    $type['bui_get_info']['callback'] = 'una_get_bui_add';  // запросил в чате статистику по себе

    $type['bui_get_info']['access'] = 'admin';

    return $type;
}

/*
 * 2. Пишем активность в бд:
 */
add_action( 'bui_chat_user_info', 'una_add_bui_user_stats', 10 );
function una_add_bui_user_stats( $data ) {
    $args['action']      = 'bui_get_info';  // тот самый уникальный экшен
    $args['object_type'] = 'user';
    $args['other_info']  = $data;

    una_insert( $args );                    // запишем в бд
}

/*
 * 3. Выводим в общую ленту
 * una_get_bui_add - зарегистрированная в 1-й функции в callback
 *
 */

/*
  // $data содержит:
  Array(
  [id] => 2834
  [user_id] => 1
  [action] => bui_get_info
  [act_date] => 2018-01-17 19:24:30
  [object_id] => 0
  [object_name] =>
  [object_type] => user
  [subject_id] => 0
  [other_info] => тут его статистика
  [user_ip] => 3.52.235.164
  [display_name] => имя
  [post_status] =>
  ) */
function una_get_bui_add( $data ) {
    $texts   = [ 'запросил', 'запросила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    return '<span class="una_action">' . $decline . ' данные в чате:</span><div class="una_user_status"><div>' . $data['other_info'] . '</div></div>';
}
