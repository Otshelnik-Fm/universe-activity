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
add_filter( 'una_register_type', 'una_register_gnp_addon', 10 );
function una_register_gnp_addon( $type ) {
    $type['add_group_notify'] = [
        'name'     => 'Подписался на уведомления группы', // Событие. "отвечая на вопрос: Что сделал"
        'source'   => 'group-new-post-notify', /////////// Источник (wordpress, плагин, аддон - slug аддона или имя, как в списке допов)
        'callback' => 'una_get_gnp_add_group_notify', //// функция вывода
    ];

    $type['change_group_notify'] = [
        'name'     => 'Изменил тип уведомлений',
        'source'   => 'group-new-post-notify',
        'callback' => 'una_get_gnp_change_group_notify',
        'access'   => 'author',
    ];

    $type['del_group_notify'] = [
        'name'     => 'Отписался от уведомлений',
        'source'   => 'group-new-post-notify',
        'callback' => 'una_get_gnp_del_group_notify',
        'access'   => 'author',
    ];

    $type['unsub_group_notify'] = [
        'name'     => 'Кто-то удалил подписку',
        'source'   => 'group-new-post-notify',
        'callback' => 'una_get_gnp_unsub_group_notify',
        'access'   => 'admin',
    ];

    $type['verify_group_notify'] = [
        'name'     => 'Напоминание от админа о продолжении подписки',
        'source'   => 'group-new-post-notify',
        'callback' => 'una_get_gnp_verify_group_notify',
        'access'   => 'admin',
    ];

    $type['send_group_digest'] = [
        'name'     => 'Успешная отправка недельного дайджеста',
        'source'   => 'group-new-post-notify',
        'callback' => 'una_get_gnp_send_group_digest',
        'access'   => 'admin',
    ];

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

// кто-то удалил подписку
add_action( 'gnp_remove_subscribtion', 'una_unsub_gnp_subscribe', 10, 3 );
function una_unsub_gnp_subscribe( $group_id, $user_id, $unsub_user_id ) {
    $termdata = get_term( $group_id );

    $args['action']      = 'unsub_group_notify';
    $args['object_id']   = $group_id;
    $args['object_name'] = $termdata->name;
    $args['object_type'] = 'user';
    $args['subject_id']  = $unsub_user_id;
    $args['group_id']    = $group_id;

    una_insert( $args );
}

// админ отправил письмо с напоминанием о продолжении подписки
add_action( 'gnp_mail_verify', 'una_gnp_verify_subscribe', 10, 5 );
function una_gnp_verify_subscribe( $group_id, $user_id, $mail, $userdata, $term ) {
    $args['action']      = 'verify_group_notify';
    $args['object_id']   = $group_id;
    $args['object_name'] = $term->name;
    $args['object_type'] = 'user';
    $args['subject_id']  = $user_id;
    $args['group_id']    = $group_id;

    una_insert( $args );
}

// успешная отправка недельного дайджеста
add_action( 'gnp_send_digest', 'una_send_gnp_digest' );
function una_send_gnp_digest() {
    $args['action'] = 'send_group_digest';

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

    $texts   = [ 'Подписался', 'Подписалась' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    return '<span class="una_action">' . $decline . ' в группе</span> ' . $name . ' на получение ' . $type;
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

    $texts   = [ 'Изменил', 'Изменила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    return '<span class="una_action">' . $decline . ' тип подписки в группе</span> ' . $name . ' на получение ' . $type;
}

// удалил подписку
function una_get_gnp_del_group_notify( $data ) {
    $name = '<a class="una_group_name" href="/?una_group_url=' . $data['group_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';

    $texts   = [ 'Отменил', 'Отменила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    return '<span class="una_action">' . $decline . ' подписку в группе</span> ' . $name;
}

// кто-то удалил подписку
function una_get_gnp_unsub_group_notify( $data ) {
    $texts   = [ 'Отменил', 'Отменила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $name = '<a class="una_group_name" href="/?una_group_url=' . $data['group_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';

    return '<span class="una_action">' . $decline . ' подписку пользователю ' . una_get_username( $data['subject_id'], 1 ) . ' в группе</span> ' . $name;
}

// админ отправил письмо с напоминанием о продолжении подписки
function una_get_gnp_verify_group_notify( $data ) {
    $texts   = [ 'Отправил', 'Отправила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    return '<span class="una_action">' . $decline . ' письмо (напоминание) - о продолжении подписки на рассылку из групп</span>, пользователю ' . una_get_username( $data['subject_id'], 1 );
}

// успешная отправка недельного дайджеста
function una_get_gnp_send_group_digest( $data ) {
    return '<span class="una_action">Недельный дайджест был успешно отправлен</span>';
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
