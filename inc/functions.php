<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// вставим в базу данных (события из в fires.php)
function una_insert($args){
    if( !class_exists('UNA_Insert_DB') ){
        require_once('class-una-insert-db.php'); // ядро
    }
    $query = new UNA_Insert_DB();

    return $query->insert_db($args);
}


// зарегистрированные типы событий и их доступы и обработчики
function una_register_type_callback(){
    if( !class_exists('UNA_Register_Type_Callback') ){
        require_once('class-una-register-type-callback.php');
    }
    $types = new UNA_Register_Type_Callback();

    return $types->get_type_callback();
}


// регистрируем шорткод
function una_shortcodes($atts){
    $shrt = new UNA_shortcode();
    return '<div id="una_users" class="universe_userlist">'.$shrt->get_universe($atts).'</div>';
}
add_shortcode('otfm_universe','una_shortcodes');


// отформатируем рейтинг в зависимости от его типа
function una_rating_styling($type, $value){
    $simbol = '';
    if($type == 'plus') $simbol = '+';

    $out = '<span class="una_rating_'.$type.'">'.$simbol.$value.'</span>';
    return $out;
}


// отделим время
function una_separate_time($date, $seconds = false){
    $match = array();
    $pattern = '/(\d{4}-\d{2}-\d{2}).(\d{2}:\d{2})/';
    if($seconds) $pattern = '/(\d{4}-\d{2}-\d{2}).(\d{2}:\d{2}:\d{2})/';
    preg_match($pattern, $date, $match);

    return '<div class="una_time">'.$match[2].'</div>';
}


// отделим дату
function una_separate_date($date){
    preg_match('/(\d{4}-\d{2}-\d{2}).(\d{2}:\d{2}:\d{2})/', $date, $match);

    return $match[1];
}


// человечное время
function una_human_days($date){
    $cur_date = get_date_from_gmt(date('Y-m-d H:i:s'),'Y-m-d'); //настройки локали вп (вида 2016-12-21)
    $yesterday = date('Y-m-d',strtotime("-1 days", strtotime($cur_date)));
    $before_yesterday = date('Y-m-d',strtotime("-2 days", strtotime($cur_date)));

    $action_date = una_separate_date($date);
    if ( $cur_date == $action_date ) {
        return 'Сегодня';
    } elseif ( $yesterday == $action_date ) {
        return 'Вчера';
    } elseif ( $before_yesterday == $action_date ) {
        return 'Позавчера';
    }
    //return rcl_human_time_diff($date). ' назад'; // 3ня назад
    return una_human_format($date);
}


// приведем все оставшиеся в вид: 27 мая 2017
function una_human_format($date){
    $months = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');

    $newDatetime = new Datetime($date);
    $month = $newDatetime->format('n');
    $human = $newDatetime->format('j '.$months[$month-1].' ');
    $human .= $newDatetime->format('Y');

  return $human;
}


// добавим в массив новые переменные запроса
function una_add_query_vars($vars){
	$vars[] = 'una_comment_id';
    $vars[] = 'una_group_url';
    $vars[] = 'una_prime_forum_url';
	return $vars;
}
add_filter('query_vars', 'una_add_query_vars');


// ловим ссылку на комментарий, группу, форум - вида ваш-сайт/?una_comment_id=16 (16 - id комментария)
function una_catch_type_vars_link(){
    if(!empty( get_query_var('una_comment_id') )){
        $comment_link = get_comment_link( intval(get_query_var('una_comment_id')) );

        wp_redirect($comment_link);
        exit;
    }
    else if(!empty( get_query_var('una_group_url') )){
        $group_link = rcl_get_group_permalink( intval(get_query_var('una_group_url')) );

        wp_redirect($group_link);
        exit;
    }
    else if(!empty( get_query_var('una_prime_forum_url') )){
        $forum_link = pfm_get_topic_permalink( intval(get_query_var('una_prime_forum_url')) );

        wp_redirect($forum_link);
        exit;
    }
}
add_action('template_redirect', 'una_catch_type_vars_link');


// получим id заметки (доп notes) из имени поста. Они все начинаются с zametka-id
function una_separate_id_notes($post_name){
    $matches = array();
    $pattern = '([0-9]+)'; // zametka-18 or zametka-13
    preg_match($pattern, $post_name, $matches);

    return $matches[0];
}




