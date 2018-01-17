<?php

if (!defined('ABSPATH')) exit;

// доп регистрирует свой рейтинг - его активность и отследим


// зарегистрируем функцию
// $type['уникальный_экшен']['callback'] = 'имя_коллбек_функции';
function una_register_af_rating($type){
    $type['give_rating_forum-page']['callback'] = 'una_get_give_rating_post';

    return $type;
}
add_filter('una_register_type', 'una_register_af_rating', 10);


//
// пишет в логи общая функция рейтинга - una_give_rating() - в файле fires.php
//


//
// выводит в ленту общая ф-ция - una_get_give_rating_post() - в файле callback.php
//


// короткую ссылку на пост асгарос сообщения надо обработать
// зарегистрирую новую переменную запроса в ВП
function una_af_rating_register_vars($vars){
    $vars[] = 'una_asgrs_forum_post_url';

    return $vars;
}
add_filter('query_vars', 'una_af_rating_register_vars');

// и поймаем ёё
function una_af_rating_catch_vars_link(){
    $una_asgrs_forum_post_id = get_query_var('una_asgrs_forum_post_url');

    if(!empty( $una_asgrs_forum_post_id )){
        global $asgarosforum,$wpdb;

        $as_postid = intval($una_asgrs_forum_post_id);
        $topic_id = $wpdb->get_var($wpdb->prepare(""
                    . "SELECT fp.parent_id "
                    . "FROM ".$wpdb->prefix."forum_posts AS fp "
                    . "LEFT JOIN ".$wpdb->prefix."forum_topics AS ft "
                    . "ON(fp.parent_id = ft.id) "
                    . "WHERE fp.id = %d "
                    . "ORDER BY date ASC"
                , $as_postid));

        $link = $asgarosforum->get_postlink($topic_id,$as_postid);
        $topic_link = htmlspecialchars_decode($link);

        wp_redirect($topic_link);
        exit;
    }
}
add_action('template_redirect', 'una_af_rating_catch_vars_link');
