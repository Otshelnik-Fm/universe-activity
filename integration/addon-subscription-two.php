<?php

if ( ! defined( 'ABSPATH' ) )
    exit;


/*
 * 1. Зарегистрируем в массив новые типы и привилегии
 * (если не указана привилегия - то видят все начиная от гостя)
 * подробнее в описании допа вкладка "Логика/Настройки" пункт "События и привилегии"
 * https://codeseller.ru/products/universe-activity/
 */
// $type['уникальный_экшен']['callback'] = 'имя_коллбек_функции';
add_filter( 'una_register_type', 'una_register_sbt_addon', 10 );
function una_register_sbt_addon( $type ) {
    $type['sbt_add_subs'] = [
        'name'     => 'Подписался на комментарии', /////// Событие. "отвечая на вопрос: Что сделал"
        'source'   => 'subscription-two', ///////// Источник (wordpress, плагин, аддон - slug аддона или имя, как в списке допов)
        'callback' => 'una_get_sbt_subs', //// функция вывода
        'access'   => 'logged', ///////////////////// доступ
    ];

    $type['sbt_del_subs'] = [
        'name'     => 'Отписался от комментариев',
        'source'   => 'subscription-two',
        'callback' => 'una_del_sbt_subs',
        'access'   => 'author',
    ];

    return $type;
}

/*
 * 2. Пишем активность в бд:
 */
// хук: добавил подписку
add_action( 'sbt_subscribed', 'una_add_sbt_subs_action', 10, 3 );
function una_add_sbt_subs_action( $id, $post_id, $post_type ) {
    $name       = '';
    $group_data = $id;

    if ( $post_type == 'forum' ) {
        $name = pfm_get_topic_name( $post_id );
    } else if ( $post_type == 'post-group' ) {
        $group = una_get_group_by_post( $post_id );
        if ( $group ) {
            $group_id   = $group->term_id;
            $group_data = array( 'subs_id' => $id, 'grn' => $group->name );
            $name       = get_the_title( $post_id );

            $args['group_id'] = $group_id;
        }
    } else {
        $name = get_the_title( $post_id );
    }

    $args['action']      = 'sbt_add_subs';  // тот самый уникальный экшен
    $args['object_id']   = $post_id;
    $args['object_name'] = $name;
    $args['object_type'] = $post_type;
    $args['other_info']  = serialize( $group_data );

    una_insert( $args );                    // запишем в бд
}

// удалил подписку
add_action( 'sbt_unsubscribed', 'una_del_sbt_subs_action', 10, 2 );
function una_del_sbt_subs_action( $post_id, $post_type ) {
    $name = '';

    if ( $post_type == 'forum' ) {
        $name = pfm_get_topic_name( $post_id );
    } else if ( $post_type == 'post-group' ) {
        $group = una_get_group_by_post( $post_id );
        if ( $group ) {
            $group_id   = $group->term_id;
            $group_data = array( 'grn' => $group->name );
            $name       = get_the_title( $post_id );

            $args['group_id']   = $group_id;
            $args['other_info'] = serialize( $group_data );
        }
    } else {
        $name = get_the_title( $post_id );
    }

    $args['action']      = 'sbt_del_subs';
    $args['object_id']   = $post_id;
    $args['object_name'] = $name;
    $args['object_type'] = $post_type;

    una_insert( $args );
}

/*
 * 3. Выводим в общую ленту
 * una_get_sbt_subs - зарегистрированная в 1-й функции в callback
 *
 */

/*
  // $data содержит:
  Array(
  [id] => 2834
  [user_id] => 1
  [action] => sbt_add_subs
  [act_date] => 2018-01-17 19:24:30
  [object_id] => 5
  [object_name] => Третий топик на форуме для тестирования
  [object_type] => forum
  [subject_id] => 0
  [other_info] => 23
  [user_ip] => 3.52.235.164
  [display_name] => Владимир Otshelnik-Fm
  [post_status] =>
  ) */
// подписался на комментарии к записи: Секретные материалы (The X-Files)(2016)(1 сезон)
// В группе "Кино", подписался на комментарии к записи: Секретные материалы (The X-Files)(2016)(1 сезон)
function una_get_sbt_subs( $data ) {
    $name = 'запись';

    $texts   = [ 'Подписался', 'Подписалась' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    if ( $data['object_type'] == 'forum' ) {
        return '<span class="una_action">' . $decline . ' на тему форума:</span> ' . $data['object_name'];
    } else if ( $data['object_type'] == 'products' ) {
        return '<span class="una_action">' . $decline . ' на товар:</span> ' . $data['object_name'];
    } else if ( $data['object_type'] == 'post-group' ) {
        if ( is_serialized( $data['other_info'] ) ) {
            $other = unserialize( $data['other_info'] );
        } else {
            $other = $data['other_info'];
        }
        // обратная совместимость пока не было интеграции с допом групп
        if ( isset( $other['grn'] ) ) {
            $group = '<a class="una_group_name" href="/?una_group_url=' . $data['group_id'] . '" title="Перейти" rel="nofollow">"' . $other['grn'] . '"</a>';
            $link  = '<a href="/?p=' . $data['object_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';

            $texts   = [ 'подписался', 'подписалась' ];
            $decline = una_decline_by_sex( $data['user_id'], $texts );

            return '<span class="una_action">В группе ' . $group . ', ' . $decline . ' на комментарии к записи:</span> ' . $link;
        } else {
            $name = 'записи в группе';
        }
    } else {
        $data['object_name'] = '<a href="/?p=' . $data['object_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';
    }

    return '<span class="una_action">' . $decline . ' на ' . $name . ':</span> ' . $data['object_name'];
}

function una_del_sbt_subs( $data ) {
    $name = 'записи';

    if ( $data['object_type'] == 'forum' ) {
        $name = 'темы форума';
    } else if ( $data['object_type'] == 'products' ) {
        $name = 'товара';
    } else if ( $data['object_type'] == 'post-group' ) {
        $other = unserialize( $data['other_info'] );

        // обратная совместимость пока не было интеграции с допом групп
        if ( isset( $other['grn'] ) ) {
            $group = '<a class="una_group_name" href="/?una_group_url=' . $data['group_id'] . '" title="Перейти" rel="nofollow">"' . $other['grn'] . '"</a>';
            $link  = '<a href="/?p=' . $data['object_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';

            $texts   = [ 'отписался', 'отписалась' ];
            $decline = una_decline_by_sex( $data['user_id'], $texts );

            return '<span class="una_action">В группе ' . $group . ', ' . $decline . ' от комментариев к записи:</span> ' . $link;
        } else {
            $name = 'записи в группе';
        }
    } else {
        $data['object_name'] = '<a href="/?p=' . $data['object_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';
    }

    $texts   = [ 'Отписался', 'Отписалась' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    return '<span class="una_action">' . $decline . ' от ' . $name . ':</span> ' . $data['object_name'];
}

/*
 * 4. Я добавлю также к кнопкам фильтрам
 *
 */
// к кнопке-фильтр "Подписки" добавлю пару событий
add_filter( 'una_filter_subscriptions', 'una_add_sbt_filter_button_subscriptions', 10 );
function una_add_sbt_filter_button_subscriptions( $actions ) {
    array_push( $actions, 'sbt_add_subs', 'sbt_del_subs' );

    return $actions;
}
