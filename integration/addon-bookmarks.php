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
add_filter( 'una_register_type', 'una_register_bkmrk_addon', 10 );
function una_register_bkmrk_addon( $type ) {
    $type['bkmrk_add']['callback'] = 'una_get_bkmrk_add';   // добавил в закладки

    return $type;
}

/*
 * 2. Пишем активность в бд:
 */
// хук: добавил в закладки
add_action( 'rcl_add_bookmark', 'bkmrk_add_action', 10 );
function bkmrk_add_action( $post_id ) {
    $post      = get_post( $post_id );
    $name      = $post->post_title;
    $id_author = $post->post_author;

    if ( $post->post_type == 'post-group' ) {
        $group = una_get_group_by_post( $post_id );

        $group_data = array( 'grn' => $group->name );

        $args['other_info'] = serialize( $group_data );
        $args['group_id']   = $group->term_id;
    }

    $args['action']      = 'bkmrk_add'; // тот самый уникальный экшен
    $args['object_id']   = $post_id;
    $args['object_name'] = $name;
    $args['object_type'] = $post->post_type;
    $args['subject_id']  = $id_author;

    una_insert( $args );                // запишем в бд
}

/*
 * 3. Выводим в общую ленту
 * una_get_bkmrk_add - зарегистрированная в 1-й функции в callback
 *
 */

/*
  // $data содержит:
  Array(
  [id] => 2834
  [user_id] => 1
  [action] => bkmrk_add
  [act_date] => 2018-01-17 19:24:30
  [object_id] => 5
  [object_name] => Третья тема
  [object_type] => post-group
  [subject_id] => 3
  [other_info] => 23
  [user_ip] => 3.52.235.164
  [group_id] => 97
  [display_name] => Владимир Otshelnik-Fm
  [post_status] =>
  ) */
// Добавил в закладки запись: Секретные материалы (The X-Files)(2016)(1 сезон)
// В группе "Кино", добавил в закладки к запись: Секретные материалы (The X-Files)(2016)(1 сезон)
function una_get_bkmrk_add( $data ) {
    if ( $data['object_type'] == 'post-group' ) {
        $other = '';
        if ( is_serialized( $data['other_info'] ) ) {
            $other = unserialize( $data['other_info'] );
        }

        $group = '<a class="una_group_name" href="/?una_group_url=' . $data['group_id'] . '" title="Перейти" rel="nofollow">"' . $other['grn'] . '"</a>';
        $link  = '<a href="/?p=' . $data['object_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';

        return '<span class="una_action">В группе ' . $group . ', добавил в закладки к запись:</span> ' . $link;
    } else {
        $link = '<a href="/?p=' . $data['object_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';

        return '<span class="una_action">Добавил в закладки к запись:</span> ' . $link;
    }
}
