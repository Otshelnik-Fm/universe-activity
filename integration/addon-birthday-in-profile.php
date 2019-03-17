<?php

if (!defined('ABSPATH')) exit;


/*
 * 1. Зарегистрируем в массив новые типы и привелегии
 * (если не указана привелегия - то видят все начиная от гостя)
 * подробнее в описании допа вкладка "Логика/Настройки" пункт "События и привилегии"
 * https://codeseller.ru/products/universe-activity/
*/

// $type['уникальный_экшен']['callback'] = 'имя_коллбек_функции';
function una_register_bip_addon($type){
    $type['bip_add_dob']['callback']    = 'una_get_bip_add_dob';       // указал ДР
    $type['bip_change_dob']['callback'] = 'una_get_bip_change_dob';    // сменил ДР

    $type['bip_add_dob']['access']      = 'admin';
    $type['bip_change_dob']['access']   = 'admin';

    return $type;
}
add_filter('una_register_type', 'una_register_bip_addon', 10);





/*
 * 2. Пишем активность в бд:
*/


// хук: добавил ДР
function una_add_birthday_bip($year, $month, $day){
    $args['action'] = 'bip_add_dob';                            // тот самый уникальный экшен
    $args['object_type'] = 'user';
    $args['other_info'] = $year . '-' . $month . '-' . $day;

    una_insert($args);                                          // запишем в бд
}
add_action('bip_add_birthday', 'una_add_birthday_bip', 10, 3);


// хук: сменил ДР
function una_change_birthday_bip($year, $month, $day){
    $args['action'] = 'bip_change_dob';
    $args['object_type'] = 'user';
    $args['other_info'] = $year . '-' . $month . '-' . $day;

    una_insert($args);
}
add_action('bip_change_birthday', 'una_change_birthday_bip', 10, 3);




/*
 * 3. Выводим в общую ленту
 * una_get_bip_add_dob - зарегистрированная в 1-й функции в callback
 *
*/

/*
// $data содержит:
Array(
    [id] => 2834
    [user_id] => 1
    [action] => bip_add_dob
    [act_date] => 2018-01-17 19:24:30
    [object_id] => 0
    [object_name] =>
    [object_type] => user
    [subject_id] => 0
    [other_info] => 1931-02-28
    [user_ip] => 3.52.235.164
    [display_name] => Владимир Otshelnik-Fm
    [post_status] =>
)*/

function una_get_bip_add_dob($data){
    return '<span class="una_action">Указал день рождения:</span> ' . bip_get_full_dob($data['other_info'], $no_filter=true);
}

function una_get_bip_change_dob($data){
    return '<span class="una_action">Изменил день рождения:</span> ' . bip_get_full_dob($data['other_info'], $no_filter=true);
}



/*
 * 4. Я добавлю также к кнопкам фильтрам
 *
*/

// к кнопке-фильтр "обновления" добавлю пару событий
function una_add_bip_filter_button_updates($actions){
    array_push($actions, 'bip_add_dob','bip_change_dob');

    return $actions;
}
add_filter('una_filter_updates', 'una_add_bip_filter_button_updates', 10);
