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
add_filter( 'una_register_type', 'una_register_gnp_addon', 10 );
function una_register_gnp_addon( $type ) {
    $type['add_group_notify']['callback']    = 'una_get_gnp_add_group_notify';      // подписался на уведомления группы
    $type['change_group_notify']['callback'] = 'una_get_gnp_change_group_notify';   // изменил тип уведомлений
    $type['del_group_notify']['callback']    = 'una_get_gnp_del_group_notify';      // удалил уведомление

    $type['add_group_notify']['access']    = 'author';
    $type['change_group_notify']['access'] = 'author';
    $type['del_group_notify']['access']    = 'author';

    return $type;
}

/*
 * 2. Пишем активность в бд:
 */
// хук: добавил подписку на уведомления группы
add_action( 'gnp_add_subscribe', 'una_add_gnp_subscribe', 10, 2 );
function una_add_gnp_subscribe( $group_id, $subs_type ) {
    $termdata = get_term( $group_id );

    $args['action']      = 'add_group_notify';
    $args['object_id']   = $group_id;
    $args['object_name'] = $termdata->name;
    $args['other_info']  = $subs_type;
    $args['object_type'] = 'group';
    $args['group_id']    = $group_id;

    una_insert( $args );
}

// хук: сменил подписку на уведомления группы
add_action( 'gnp_change_subscribe', 'una_change_gnp_subscribe', 10, 2 );
function una_change_gnp_subscribe( $group_id, $subs_type ) {
    global $user_ID;

    $sql   = "SELECT other_info FROM " . UNA_DB . " WHERE user_id = %d AND action = 'change_group_notify' AND group_id = %d ORDER BY act_date DESC";
    $param = [ $user_ID, $group_id ];
    $type  = una_get_var( $sql, $param );

    // нет еще в событиях строки. Создадим его
    if ( empty( $type ) ) {
        $termdata = get_term( $group_id );

        $args['action']      = 'change_group_notify';
        $args['object_id']   = $group_id;
        $args['object_name'] = $termdata->name;
        $args['other_info']  = $subs_type;
        $args['object_type'] = 'group';
        $args['group_id']    = $group_id;

        una_insert( $args );
    }
    // есть данные
    else {
        // и пришла смена подписки
        if ( $type !== $subs_type ) {
            $termdata = get_term( $group_id );

            $new_data = array( 'other_info' => $subs_type, 'object_name' => $termdata->name, 'act_date' => current_time( 'mysql' ) );
            $where    = array( 'action' => 'change_group_notify', 'group_id' => $group_id, 'user_id' => $user_ID );
            una_update( $new_data, $where );
        }
    }
}

// удалил подписку
add_action( 'gnp_delete_subscribe', 'una_del_gnp_subscribe', 10 );
function una_del_gnp_subscribe( $group_id ) {
    $termdata = get_term( $group_id );

    $args['action']      = 'del_group_notify';
    $args['object_id']   = $group_id;
    $args['object_name'] = $termdata->name;
    $args['object_type'] = 'group';
    $args['group_id']    = $group_id;

    una_insert( $args );
}

/*
 * 3. Выводим в общую ленту
 * add_group_notify - зарегистрированная в 1-й функции в callback
 *
 */

/*
  // $data содержит:
  Array(
  [id] => 3442
  [user_id] => 1
  [action] => add_group_notify
  [act_date] => 2019-02-06 18:32:18
  [object_id] => 86
  [object_name] => Открытая 2019
  [object_type] => group
  [subject_id] => 0
  [other_info] => fast
  [user_ip] => 128.70.201.125
  [hide] => 0
  [group_id] => 86
  [display_name] => Wladimir (Otshelnik-Fm)
  [post_status] =>
  ) */
function una_get_gnp_add_group_notify( $data ) {
    $name = '<a class="una_group_name" href="/?una_group_url=' . $data['group_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';

    $type = '';
    if ( $data['other_info'] === 'fast' ) {
        $type = 'быстрых уведомлений';
    } else if ( $data['other_info'] === 'week' ) {
        $type = 'дайджеста за неделю';
    }

    return '<span class="una_action">Подписался в группе</span> ' . $name . ' на получение ' . $type;
}

// вывод смены подписки
function una_get_gnp_change_group_notify( $data ) {
    $name = '<a class="una_group_name" href="/?una_group_url=' . $data['group_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';

    $type = '';
    if ( $data['other_info'] === 'fast' ) {
        $type = 'быстрых уведомлений';
    } else if ( $data['other_info'] === 'week' ) {
        $type = 'дайджеста за неделю';
    }

    return '<span class="una_action">Изменил тип подписки в группе</span> ' . $name . ' на получение ' . $type;
}

function una_get_gnp_del_group_notify( $data ) {
    $name = '<a class="una_group_name" href="/?una_group_url=' . $data['group_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';

    return '<span class="una_action">Отменил подписку в группе</span> ' . $name;
}

/*
 * 4. Я добавлю также к кнопкам фильтрам
 *
 */
// к кнопке-фильтр "Подписки" добавлю пару событий
add_filter( 'una_filter_subscriptions', 'una_add_gnp_filter_button_subscriptions', 10 );
function una_add_gnp_filter_button_subscriptions( $actions ) {
    array_push( $actions, 'add_group_notify', 'change_group_notify', 'del_group_notify' );

    return $actions;
}
