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
add_filter( 'una_register_type', 'una_register_rating_system_addon', 9 );
function una_register_rating_system_addon( $type ) {
    $type['give_rating_comment']    = [
        'name'     => 'Рейтинг за комментарий', //// Событие. "отвечая на вопрос: Что сделал"
        'source'   => 'rating-system', ///////////// Источник (wordpress, плагин, аддон - slug аддона или имя, как в списке допов)
        'callback' => 'una_get_give_rating_post', // функция вывода
    ];
    $type['give_rating_notes']      = [
        'name'     => 'Рейтинг за заметку',
        'source'   => 'rating-system',
        'callback' => 'una_get_give_rating_post',
    ];
    $type['give_rating_post']       = [
        'name'     => 'Рейтинг за запись',
        'source'   => 'rating-system',
        'callback' => 'una_get_give_rating_post',
    ];
    $type['give_rating_post-group'] = [
        'name'     => 'Рейтинг за запись в группе',
        'source'   => 'rating-system',
        'callback' => 'una_get_give_rating_post',
    ];
    $type['give_rating_products']   = [
        'name'     => 'Рейтинг за товар',
        'source'   => 'rating-system',
        'callback' => 'una_get_give_rating_post',
    ];
    $type['give_rating_forum-post'] = [
        'name'     => 'Рейтинг за сообщение на форуме',
        'source'   => 'rating-system',
        'callback' => 'una_get_give_rating_post',
    ];

    return $type;
}

/*
 * 2. Пишем активность в бд:
 */

// Поставил рейтинг
add_action( 'rcl_insert_rating', 'una_give_rating' );
function una_give_rating( $data ) {
    // рейтинг за отзыв не фиксируем
    if ( $data['rating_type'] == 'review-content' )
        return;

    // рейтинг за smart-comment игнорим
    if ( $data['rating_type'] == 'smart-comment' )
        return;

    // рейтинг за bonus-login игнорим
    if ( $data['rating_type'] == 'bonus-login' )
        return;

    // обрабатываемые записи для вывода заголовка
    $types = array( 'post', 'products', 'post-group', 'notes' );

    if ( in_array( $data['rating_type'], $types ) ) {
        $args['object_name'] = get_the_title( $data['object_id'] );
    }

    // рейтинг за запись групп
    if ( $data['rating_type'] == 'post-group' ) {
        $group = una_get_group_by_post( $data['object_id'] );
        if ( $group ) {
            $args['group_id'] = $group->term_id;
        }
    }

    $p_id = [];
    // рейтинг за комментарий групп
    if ( $data['rating_type'] == 'comment' ) {
        $comment = get_comment( $data['object_id'] );

        $group = una_get_group_by_post( $comment->comment_post_ID );
        if ( $group ) {
            $p_id             = [ $comment->comment_post_ID ];
            $args['group_id'] = $group->term_id;
        }
    }

    // рейтинг за комментарий на prime forum
    if ( $data['rating_type'] == 'forum-post' ) {
        $post  = pfm_get_post( $data['object_id'] );
        $topic = pfm_get_topic( $post->topic_id );

        $args['object_name'] = $topic->topic_name;
    }

    // рейтинг за комментарий на Asgaros forum
    if ( $data['rating_type'] == 'forum-page' ) {
        global $wpdb;
        $topic_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM " . $wpdb->prefix . "forum_posts AS fp LEFT JOIN " . $wpdb->prefix . "forum_topics AS ft ON(fp.parent_id = ft.id) WHERE fp.id = %d ORDER BY date ASC", $data['object_id'] ) );

        $args['object_name'] = $topic_name;
    }

    $current_user = wp_get_current_user();

    $other = array( // какой оценкой
        $current_user->display_name,
        $data['rating_status'],
        $data['rating_value'],
    );

    if ( isset( $p_id ) ) {
        $other = array_merge( $other, $p_id );
    }

    $args['object_type'] = $data['rating_type'];
    $args['user_id']     = $data['user_id'];                        // кто
    $args['action']      = 'give_rating_' . $data['rating_type'];   // проголосавал за тип
    $args['object_id']   = $data['object_id'];                      // за id записи
    $args['subject_id']  = $data['object_author'];                  // id автора кому поставил
    $args['other_info']  = serialize( $other );

    una_insert( $args );
}

// снял рейтинг - удалим тогда из истории
add_action( 'rcl_delete_rating', 'una_delete_rating' );
function una_delete_rating( $data ) {
    global $wpdb;

    $wpdb->query( $wpdb->prepare( "DELETE FROM " . UNA_DB . " WHERE user_id = '%d' AND action = '%s' AND object_id = '%d' ", $data['user_id'], "give_rating_" . $data['rating_type'], $data['object_id'] ) );
}

/*
 * 3. Выводим в общую ленту
 * una_get_give_rating_post - зарегистрированная в 1-й функции в callback
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
// поставил рейтинг за
function una_get_give_rating_post( $data ) {
    $other = unserialize( $data['other_info'] );
    /* Array
      (
      [0] => Владимир Otshelnik-Fm
      [1] => plus
      [2] => 1
      [3] => 3
      ) */

    $rating = una_rating_styling( $other[1], $other[2] );

    $type = 'запись';
    $link = '<a href="/?p=' . $data['object_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';
    if ( $data['action'] == 'give_rating_notes' ) {
        $type = 'заметку';
    }
    if ( $data['action'] == 'give_rating_comment' ) {
        $type = 'комментарий';
        $name = 'гостя';
        if ( $data['subject_id'] ) {
            $name = get_comment_author( $data['object_id'] );
        }

        $link = '<a href="/?una_comment_id=' . $data['object_id'] . '" title="Перейти к комментарию" rel="nofollow">' . $name . '</a>';
    } else if ( $data['action'] == 'give_rating_products' ) {
        $type = 'товар';
    } else if ( $data['action'] == 'give_rating_forum-post' ) { // если рейтинг за комментарий на prime forum
        $type = 'комментарий на форуме';
        $link = '<a href="/?una_prime_forum_topic_url=' . $data['object_id'] . '" title="Перейти к комментарию" rel="nofollow">' . una_get_username( $data['subject_id'] ) . '</a>';
    }

    $object = '';
    if ( $data['action'] == 'give_rating_post-group' ) {
        $group = una_get_group_by_post( $data['object_id'] );

        if ( $group ) {
            $gp_info = '<a href="/?una_group_url=' . $group->term_id . '" title="Перейти" rel="nofollow">' . $group->name . '</a>';
            $object  = ', в группе ' . $gp_info;
        }
    }
    //
    else if ( $data['action'] == 'give_rating_comment' && isset( $data['group_id'] ) && isset( $other[3] ) ) {
        $group = una_get_group_by_post( $other[3] );

        if ( $group ) {
            $gp_info = '<a href="/?una_group_url=' . $group->term_id . '" title="Перейти" rel="nofollow">' . $group->name . '</a>';
            $object  = ', в группе ' . $gp_info;
        }
    }

    $texts   = [ 'Проголосовал', 'Проголосовала' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $out = '<span class="una_action">' . $decline . '</span> ' . $rating . ' за ' . $type . ': ';
    $out .= $link . $object;

    return $out;
}

// для этого есть доп Уведомления!
// получил рейтинг
/* function una_get_get_rating_post($data){
  $other = unserialize($data['other_info']);
  $simbol = '-';
  if($other[1] == 'plus') $simbol = '+';

  $out = '<a href="'.get_author_posts_url($other[3]).'" title="Перейти" rel="nofollow">' . $other[0] . '</a>';
  $out .= ' поставил оценку '.$simbol.$other[2]. ' за запись: ';
  $out .= '<a href="/?p='.$data['object_id'].'" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';

  return $out;
  } */


/*
 * 4. добавлю к кнопкам фильтрам
 *
 */
// к кнопке-фильтр "Рейтинг" добавлю события
add_filter( 'una_filter_ratings', 'una_rating_system_filter_button', 10 );
function una_rating_system_filter_button( $actions ) {
    array_push( $actions, 'give_rating_comment', 'give_rating_notes', 'give_rating_post', 'give_rating_post-group', 'give_rating_products', 'give_rating_forum-post' );

    return $actions;
}
