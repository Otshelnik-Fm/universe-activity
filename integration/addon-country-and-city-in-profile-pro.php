<?php

if (!defined('ABSPATH')) exit;


// в этом файле все для регистрации активности Country and City in Profile PRO
// отслеживать будем 3 события: Впервые указал город, Сменил город, Удалил город

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
function una_register_cpp_pro($type){
    $type['cpp_add_city']['callback']       = 'una_get_cpp_add_city';       // указал город
    $type['cpp_change_city']['callback']    = 'una_get_cpp_change_city';    // сменил город
    $type['cpp_del_city']['callback']       = 'una_get_cpp_del_city';       // удалил город

    $type['cpp_add_city']['access']     = 'logged';
    $type['cpp_change_city']['access']  = 'logged';
    $type['cpp_del_city']['access']     = 'admin';

    return $type;
}
add_filter('una_register_type', 'una_register_cpp_pro', 10);



/*
 * 2. Пишем активность в бд:
*/


// хук: добавил город
function una_add_city_cpp($geo){
    $args['action'] = 'cpp_add_city';   // тот самый уникальный экшен
    $args['object_type'] = 'user';
    $args['other_info'] = $geo['city'];

    una_insert($args);                  // запишем в бд
}
add_action('cpp_add_city', 'una_add_city_cpp');


// хук: сменил город
function una_change_city_cpp($geo){
    $args['action'] = 'cpp_change_city';
    $args['object_type'] = 'user';
    $args['other_info'] = $geo['city'].'|'.$geo['old_sity'];

    una_insert($args);
}
add_action('cpp_change_city', 'una_change_city_cpp');


// хук: очистил город
function una_delete_city_cpp($geo){
    $args['action'] = 'cpp_del_city';
    $args['object_type'] = 'user';
    $args['other_info'] = $geo['old_sity'];

    una_insert($args);
}
add_action('cpp_clear_city', 'una_delete_city_cpp');


/*
 * 3. Выводим в общую ленту
 * una_get_cpp_add_city - зарегистрированная в 1-й функции в callback
 *
*/

/*
// $data содержит:
Array(
    [id] => 2834
    [user_id] => 1
    [action] => cpp_add_city
    [act_date] => 2018-01-17 19:24:30
    [object_id] => 0
    [object_name] =>
    [object_type] => user
    [subject_id] => 0
    [other_info] => Саратов
    [user_ip] => 3.52.235.164
    [display_name] => Владимир Otshelnik-Fm
    [post_status] =>
)*/

function una_get_cpp_add_city($data){
    $out = '<span class="una_action">Указал свой город:</span> '.$data['other_info'];

    return $out;
}

function una_get_cpp_change_city($data){
    $city = explode("|", $data['other_info']);
    $out = '<span class="una_action">Сменил свой город:</span> '.$city[1].' на '.$city[0];

    return $out;
}

function una_get_cpp_del_city($data){
    $out = '<span class="una_action">Удалил свой город. Был:</span> '.$data['other_info'];

    return $out;
}



/*
 * 4. Я добавлю также к кнопкам фильтрам
 *
*/

// к кнопке-фильтр "обновления" добавлю пару событий
function una_add_cpp_filter_button_updates($actions){
    array_push($actions, 'cpp_add_city','cpp_change_city');

    return $actions;
}
add_filter('una_filter_updates', 'una_add_cpp_filter_button_updates', 10);