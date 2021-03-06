<?php

if ( ! defined( 'ABSPATH' ) )
    exit;

/* в этом файле все для регистрации активности Asgaros Forum https://wordpress.org/plugins/asgaros-forum/ */
// отслеживать будем 2 события: создал тему на форуме и удалил тему с форума

/*
 * Работа сводится к 3-м пунктам:
 * 1. Регистрируем в системе (указываем там коллбек функцию что будет выводить и видимость)
 * 2. пишем в таблицу активности (вешаем функцию на нужный хук и внутри пишем в бд передавая в una_insert нужные аргументы)
 * 3. пишем коллбек функцию для вывода - выводим в общую ленту
 */



/*
 * 1. Зарегистрируем в массив новые типы и привилегии
 * (если не указана привилегия - то видят все начиная от гостя)
 * подробнее в описании допа вкладка "Логика/Настройки" пункт "События и привилегии"
 * https://codeseller.ru/products/universe-activity/
 */

// $type['уникальный_экшен']['callback'] = 'имя_коллбек_функции';
add_filter( 'una_register_type', 'una_register_asgaros', 10 );
function una_register_asgaros( $type ) {
    $type['asgrs_add_topic'] = [
        'name'     => 'Создал тему на форуме', //////////// Событие. "отвечая на вопрос: Что сделал"
        'source'   => 'asgaros-forum', //////////////////// Источник (wordpress, плагин, аддон - slug аддона или имя, как в списке допов)
        'callback' => 'una_get_user_add_topic_asgaros', /// функция вывода
    ];

    $type['asgrs_del_topic'] = [
        'name'     => 'Удалил тему на форуме',
        'source'   => 'asgaros-forum',
        'callback' => 'una_get_user_del_topic', // общий и для PrimeForum
        'access'   => 'admin',
    ];

    return $type;
}

/*
 * 2. Пишем активность в бд:
 */
// создал тему на Asgaros Forum
add_action( 'asgarosforum_after_add_topic_submit', 'una_user_add_topic_asgaros', 10, 2 );
function una_user_add_topic_asgaros( $asf_post, $asf_topic ) {
    global $asgarosforum;
    $myTopic = $asgarosforum->getTopic( $asf_topic );

    $args['action']      = 'asgrs_add_topic';   // тот самый уникальный экшен
    $args['object_id']   = $asf_topic;          // идентификатор темы
    $args['object_name'] = $myTopic->name;      // имя темы
    $args['object_type'] = 'asgaros_forum';     // тип

    una_insert( $args );                        // запишем в бд
}

// удалил тему на Asgaros Forum
add_action( 'asgarosforum_before_delete_topic', 'una_user_del_topic_asgaros' );
function una_user_del_topic_asgaros( $topic_id ) {
    global $wpdb, $user_ID;

    $topic_name = $wpdb->get_var( $wpdb->prepare( ""
            . "SELECT object_name "
            . "FROM " . UNA_DB . " "
            . "WHERE action = 'asgrs_add_topic' "
            . "AND object_type = 'asgaros_forum' "
            . "AND object_id = %d"
            , $topic_id ) );

    // создание топика было зафиксированно системой
    if ( $topic_name ) {
        $args['object_name'] = $topic_name;

        // и поставим маркер что топик был удален:
        $wpdb->update( UNA_DB, // обновим строку
                       array( 'other_info' => 'del' ), array( 'action' => 'asgrs_add_topic', 'object_type' => 'asgaros_forum', 'object_id' => $topic_id )
        );
    } else { // если топик не найден в системе - запрашиваю из форума его название
        global $asgarosforum;
        $myTopic             = $asgarosforum->getTopic( $topic_id );
        $args['object_name'] = $myTopic->name;
    }

    // а теперь создадим запись что топик выпилили
    $topic_user_id = $wpdb->get_var( $wpdb->prepare( "SELECT author_id FROM " . $wpdb->prefix . "forum_posts WHERE parent_id = %d ORDER BY date ASC", $topic_id ) );

    if ( $topic_user_id != $user_ID ) { // если удаляет топик не его автор
        $args['subject_id'] = $topic_user_id;
        $args['other_info'] = una_get_username( $topic_user_id );
    }

    $args['action']      = 'asgrs_del_topic';
    $args['object_id']   = $topic_id;
    $args['object_type'] = 'asgaros_forum';

    una_insert( $args );
}

/*
 * 3. Выводим в общую ленту
 * una_get_user_add_topic_asgaros - зарегистрированная в 1-й функции в callback
 *
 */

/*
  // $data содержит:
  Array(
  [id] => 2834
  [user_id] => 1
  [action] => asgrs_add_topic
  [act_date] => 2018-01-17 19:24:30
  [object_id] => 19
  [object_name] => test asgaros222
  [object_type] => asgaros_forum
  [subject_id] => 0
  [other_info] => del
  [user_ip] => 3.52.235.164
  [display_name] => Владимир Otshelnik-Fm
  [post_status] =>
  ) */
// создал тему на Asgaros Forum
function una_get_user_add_topic_asgaros( $data ) {
    $del  = '';
    $link = '<a href="/?una_asgrs_forum_url=' . $data['object_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';
    if ( $data['other_info'] == 'del' ) { // если группа удалена - то пишется в нее del. А так колонка пустая
        $link = '"' . $data['object_name'] . '"';
        $del  = '<span class="una_post_status">(удалено)</span>';
    }

    $texts   = [ 'Создал', 'Создала' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $out = '<span class="una_action">' . $decline . ' новую тему на форуме:</span> ' . $link . $del;

    return $out;
}

// выше создал короткую ссылку для асгароса
// зарегистрирую новую переменную запроса в ВП
add_filter( 'query_vars', 'una_asgaros_register_vars' );
function una_asgaros_register_vars( $vars ) {
    $vars[] = 'una_asgrs_forum_url';

    return $vars;
}

// и поймаем ёё
add_action( 'template_redirect', 'una_asgaros_catch_vars_link' );
function una_asgaros_catch_vars_link() {
    $una_asgrs_forum_topic = get_query_var( 'una_asgrs_forum_url' );

    if ( ! empty( $una_asgrs_forum_topic ) ) {
        global $asgarosforum;

        $val        = intval( $una_asgrs_forum_topic );
        $link       = $asgarosforum->get_postlink( $val, false );
        $topic_link = htmlspecialchars_decode( $link );

        wp_redirect( $topic_link );
        exit;
    }
}

/*
 * 4. добавлю к кнопкам фильтрам
 *
 */

// к кнопке-фильтр "Обновления" добавлю события
add_filter( 'una_filter_updates', 'una_asgros_filter_button', 10 );
function una_asgros_filter_button( $actions ) {
    array_push( $actions, 'asgrs_add_topic' );

    return $actions;
}
