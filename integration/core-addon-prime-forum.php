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
add_filter( 'una_register_type', 'una_register_prime_forum_addon', 8 );
function una_register_prime_forum_addon( $type ) {
    $type['pfm_add_topic'] = [
        'name'     => 'Создал тему на форуме', /// Событие. "отвечая на вопрос: Что сделал"
        'source'   => 'prime-forum', //////////////// Источник (wordpress, плагин, аддон - slug аддона или имя, как в списке допов)
        'callback' => 'una_get_user_add_topic', ////// функция вывода
    ];
    $type['pfm_del_topic'] = [
        'name'     => 'Удалил тему на форуме',
        'source'   => 'prime-forum',
        'callback' => 'una_get_user_del_topic',
        'access'   => 'admin',
    ];

    return $type;
}

/*
 * 2. Пишем активность в бд:
 */
// создал тему на primeForum
add_action( 'pfm_add_topic', 'una_user_add_topic', 10, 2 );
function una_user_add_topic( $topic_id, $argums ) {

    $args['action']      = 'pfm_add_topic';
    $args['object_id']   = $topic_id;
    $args['object_name'] = $argums['topic_name'];
    $args['object_type'] = 'prime_forum';

    una_insert( $args );
}

// удалил тему на primeForum
add_action( 'pfm_pre_delete_topic', 'una_user_del_topic' );
function una_user_del_topic( $topic_id ) {
    global $wpdb, $user_ID;

    $topic_name = $wpdb->get_var( $wpdb->prepare( "SELECT object_name FROM " . UNA_DB . " WHERE action = 'pfm_add_topic' AND object_type = 'prime_forum' AND object_id = %d", $topic_id ) );
    if ( $topic_name ) { // это значит создание топика было зафиксированно системой
        $args['object_name'] = $topic_name;
        // и поставим маркер что топик был удален:
        $wpdb->update( UNA_DB, // обновим строку
                       array( 'other_info' => 'del' ), array( 'action' => 'pfm_add_topic', 'object_type' => 'prime_forum', 'object_id' => $topic_id )
        );
    } else { // если топик не найден в системе - запрашиваю из форума его название
        $args['object_name'] = pfm_get_topic_name( $topic_id );
    }

    $topic_user_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM " . RCL_PREF . "pforum_topics WHERE topic_id = %d", $topic_id ) );

    if ( $topic_user_id != $user_ID ) { // если удаляет топик не его автор
        $args['subject_id'] = $topic_user_id;
        $args['other_info'] = una_get_username( $topic_user_id );
    }

    $args['action']      = 'pfm_del_topic';
    $args['object_id']   = $topic_id;
    $args['object_type'] = 'prime_forum';

    una_insert( $args );
}

/*
 * 3. Выводим в общую ленту
 * una_get_user_add_topic - зарегистрированная в 1-й функции в callback
 *
 */

/*
  // $data содержит:
  Array(
  [id] => 2834
  [user_id] => 1
  [action] => give_rating_notes
  [act_date] => 2018-01-17 19:24:30
  [object_id] => 45692
  [object_name] => Заголовок
  [object_type] => video
  [subject_id] => 0
  [other_info] =>
  [user_ip] => 3.52.235.164
  [display_name] =>
  [post_status] =>
  ) */
// создал тему на primeForum
function una_get_user_add_topic( $data ) {
    $texts   = [ 'Создал', 'Создала' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $del  = '';
    $link = '<a href="/?una_prime_forum_url=' . $data['object_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';
    if ( $data['other_info'] == 'del' ) { // если группа удалена - то пишется в нее del. А так колонка пустая
        $link = '"' . $data['object_name'] . '"';
        $del  = '<span class="una_post_status">(удалено)</span>';
    }

    $out = '<span class="una_action">' . $decline . ' новую тему на форуме:</span> ' . $link . $del;

    return $out;
}

// удалил топик (тему на форуме)
function una_get_user_del_topic( $data ) {
    $texts   = [ 'Удалил', 'Удалила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $link_author = '';

    if ( $data['subject_id'] ) {
        $link_author = '<span class="una_post_status una_post_author">- автор: <a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['other_info'] . '</a></span>';
    }

    $out = '<span class="una_action">' . $decline . ' тему:</span> "' . $data['object_name'] . '" с форума' . $link_author;

    return $out;
}

/*
 * 4. добавлю к кнопкам фильтрам
 *
 */

// к кнопке-фильтр "Обновления" добавлю события
add_filter( 'una_filter_updates', 'una_prime_forum_filter_button', 10 );
function una_prime_forum_filter_button( $actions ) {
    array_push( $actions, 'pfm_add_topic' );

    return $actions;
}
