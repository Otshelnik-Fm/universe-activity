<?php

if ( ! defined( 'ABSPATH' ) )
    exit;
// в этом файле все для регистрации активности Friends Recall
// отслеживать будем 2 события:
// - добавил в друзья (но тут для уведомлений 2 события - "Петя -> Машу" и "Маша -> Петю" добавили в друзья)
// - убрал из друзей. Тут будет писаться лишь инициатор этого: "Петя -> Машу" убрал из друзей.

/*
 * Работа сводится к 3-м пунктам:
 * 1. Регистрируем в системе (указываем там коллбек функцию что будет выводить и видимость)
 * 2. пишем в таблицу активности (вешаем функцию на нужный хук и внутри пишем в бд передавая в una_insert нужные аргументы)
 * 3. пишем коллбек функцию для вывода - выводим в общую ленту
 */



/*
 * 1. Зарегистрируем в массив новые типы и привелегии
 * (если не указана привелегия - то видят все начиная от гостя)
 * подробнее в описании допа вкладка "Логика/Настройки" пункт "События и привилегии"
 * https://codeseller.ru/products/universe-activity/
 */

// $type['уникальный_экшен']['callback'] = 'имя_коллбек_функции';
add_filter( 'una_register_type', 'una_register_friends', 10 );
function una_register_friends( $type ) {
    $type['frnd_add']['callback'] = 'una_get_frnd_add_friend';      // добавил в друзья
    $type['frnd_del']['callback'] = 'una_get_frnd_del_friend';      // убрал из друзей

    return $type;
}

/*
 * 2. Пишем активность в бд:
 */
// хук: добавил в друзья
add_action( 'frnd_confirm_request', 'una_add_friend_frnd', 10, 2 );
function una_add_friend_frnd( $from, $to_user ) {
    $args['user_id']     = $from;
    $args['action']      = 'frnd_add';  // тот самый уникальный экшен
    $args['object_name'] = una_get_username( $to_user );
    $args['object_type'] = 'user';
    $args['subject_id']  = $to_user;

    una_insert( $args );                // запишем в бд

    /* обратное добавление также добавим */
    $args['user_id']     = $to_user;
    $args['action']      = 'frnd_add';
    $args['object_name'] = una_get_username( $from );
    $args['object_type'] = 'user';
    $args['subject_id']  = $from;

    una_insert( $args );
}

// хук: убрал из друзей
add_action( 'frnd_delete_friend', 'una_del_friend_frnd', 10, 2 );
function una_del_friend_frnd( $from, $to_user ) {
    $args['user_id']     = $from;
    $args['action']      = 'frnd_del';
    $args['object_name'] = una_get_username( $to_user );
    $args['object_type'] = 'user';
    $args['subject_id']  = $to_user;

    una_insert( $args );
}

/*
 * 3. Выводим в общую ленту
 * una_get_frnd_add_friend - зарегистрированная в 1-й функции в callback
 *
 */

/*
  // $data содержит:
  Array(
  [id] => 43459
  [user_id] => 1
  [action] => frnd_add
  [act_date] => 2021-02-03 11:23:42
  [object_id] => 0
  [object_name] => Анжелика
  [object_type] => user
  [subject_id] => 3
  [other_info] =>
  [user_ip] => 3.52.235.164
  [display_name] => Владимир Otshelnik-Fm
  [post_status] =>
  ) */
function una_get_frnd_add_friend( $data ) {
    $decline = una_decline_by_sex( $data['user_id'], [ 'Добавил', 'Добавила' ] );

    $link_subj = '<a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';

    return '<span class="una_action">' . $decline . ' в друзья пользователя</span> ' . $link_subj;
}

function una_get_frnd_del_friend( $data ) {
    $decline = una_decline_by_sex( $data['user_id'], [ 'Убрал', 'Убрала' ] );

    $link_subj = '<a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';

    return '<span class="una_action">' . $decline . ' из друзей пользователя</span> ' . $link_subj;
}

/*
 * 4. Я добавлю также к кнопкам фильтрам
 *
 */
// к кнопке-фильтр "Подписки" добавлю пару событий
add_filter( 'una_filter_subscriptions', 'una_add_frnd_filter_button_subscriptions', 10 );
function una_add_frnd_filter_button_subscriptions( $actions ) {
    array_push( $actions, 'frnd_add', 'frnd_del' );

    return $actions;
}
