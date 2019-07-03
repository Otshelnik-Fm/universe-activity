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
add_filter( 'una_register_type', 'una_register_pua_change', 10 );
function una_register_pua_change( $type ) {
    $type['pua_change_url']['callback'] = 'una_get_pua_change_url'; // сменил URL кабинета

    $type['pua_change_url']['access'] = 'admin';

    return $type;
}

/*
 * 2. Пишем активность в бд:
 */
// хук: сменил URL
add_action( 'pua_change_url', 'una_change_pua_actions', 10, 3 );
function una_change_pua_actions( $user_id, $old_nicename, $new_nicename ) {
    $args['action']      = 'pua_change_url';                        // тот самый уникальный экшен
    $args['object_type'] = 'user';
    $args['other_info']  = $old_nicename . '|' . $new_nicename;

    una_insert( $args );                                            // запишем в бд
}

/*
 * 3. Выводим в общую ленту
 * una_get_pua_change_url - зарегистрированная в 1-й функции в callback
 *
 */

/*
  // $data содержит:
  Array(
  [id] => 2834
  [user_id] => 1
  [action] => pua_change_url
  [act_date] => 2018-01-17 19:24:30
  [object_id] => 0
  [object_name] =>
  [object_type] => user
  [subject_id] => 0
  [other_info] => wawan|wawan2
  [user_ip] => 3.52.235.164
  [display_name] => Владимир Otshelnik-Fm
  [post_status] =>
  ) */
function una_get_pua_change_url( $data ) {
    $texts   = [ 'Сменил', 'Сменила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $url = explode( "|", $data['other_info'] );
    return '<span class="una_action">' . $decline . ' урл кабинета:</span> <span>' . $url[0] . '</span> <i class="rcli fa-long-arrow-right" aria-hidden="true"></i> <span>' . $url[1] . '</span>';
}
