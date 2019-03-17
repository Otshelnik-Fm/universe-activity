<?php

if (!defined('ABSPATH')) exit;


/*
 * 1. Зарегистрируем в массив новые типы и привелегии
 * (если не указана привелегия - то видят все начиная от гостя)
 * подробнее в описании допа вкладка "Логика/Настройки" пункт "События и привилегии"
 * https://codeseller.ru/products/universe-activity/
*/

// $type['уникальный_экшен']['callback'] = 'имя_коллбек_функции';
function una_register_sbt_addon($type){
    $type['sbt_add_subs']['callback']   = 'una_get_sbt_subs';   // подписался
    $type['sbt_del_subs']['callback']   = 'una_del_sbt_subs';   // отписался

    $type['sbt_add_subs']['access']     = 'logged';
    $type['sbt_del_subs']['access']     = 'admin';

    return $type;
}
add_filter('una_register_type', 'una_register_sbt_addon', 10);


/*
 * 2. Пишем активность в бд:
*/


// хук: добавил подписку
function una_add_sbt_subs_action($id, $post_id, $post_type){
    $name = '';

    if($post_type == 'forum'){
        $name = pfm_get_topic_name( $post_id );
    }
    else {
        $name = get_the_title( $post_id );
    }

    $args['action'] = 'sbt_add_subs';                           // тот самый уникальный экшен
    $args['object_id'] = $post_id;
    $args['object_name'] = $name;
    $args['object_type'] = $post_type;
    $args['other_info'] = $id;

    una_insert($args);                                          // запишем в бд
}
add_action('sbt_subscribed', 'una_add_sbt_subs_action', 10, 3);


// удалил подписку
function una_del_sbt_subs_action($post_id, $post_type){
    $name = '';

    if($post_type == 'forum'){
        $name = pfm_get_topic_name( $post_id );
    }
    else {
        $name = get_the_title( $post_id );
    }

    $args['action'] = 'sbt_del_subs';
    $args['object_id'] = $post_id;
    $args['object_name'] = $name;
    $args['object_type'] = $post_type;

    una_insert($args);
}
add_action('sbt_unsubscribed', 'una_del_sbt_subs_action', 10, 2);


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
)*/

function una_get_sbt_subs($data){
    $name = 'запись';

    if($data['object_type'] == 'forum'){
        $name = 'тему форума';
    }
    else if($data['object_type'] == 'products'){
        $name = 'товар';
    }
    else if($data['object_type'] == 'post-group'){
        $name = 'запись в группе';
    }

    return '<span class="una_action">Подписался на '.$name.':</span> ' . $data['object_name'];
}


function una_del_sbt_subs($data){
    $name = 'записи';

    if($data['object_type'] == 'forum'){
        $name = 'темы форума';
    }
    else if($data['object_type'] == 'products'){
        $name = 'товара';
    }
    else if($data['object_type'] == 'post-group'){
        $name = 'записи в группе';
    }

    return '<span class="una_action">Отписался от '.$name.':</span> ' . $data['object_name'];
}
