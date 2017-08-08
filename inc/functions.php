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
    $shrt = new UNA_Shortcode();
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
    $match = array();
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




// доп стили для цвета от реколл кнопки
function una_inline_css(){
    if(!rcl_get_option('una_rcl_color')) return false;

    $num = did_action('una_start_shortcode');
    if($num === 0) return false; // на этой странице не используется этот шорткод

    global $rcl_options;
    $lca_hex = $rcl_options['primary-color'];
    list($r, $g, $b) = sscanf($lca_hex, "#%02x%02x%02x");
    $color = $r.','.$g.','.$b;

        echo '<style>
#universe_time #universe_visible{
    background: #fff;
    box-shadow: 0 0 25px 25px rgba('.$color.',0.4) inset;
}
.una_one_user .una_timeline .una_date::before,
.una_timeline_blk.una_modern .una_date::after,
.una_timeline_blk.una_modern .una_date::before,
.una_timeline_blk.una_basic .una_header::after,
.una_timeline_blk.una_modern .una_header::after,
.una_one_user .una_timeline .una_timeline_box::before,
.una_timeline_blk.una_modern .una_item_timeline::after,
.una_timeline_blk.una_modern .una_item_timeline::before{
    background-color: rgba('.$color.',0.8);
}
.una_timeline_blk.una_modern .una_date,
.una_timeline_blk .una_timeline::before,
.una_timeline_blk.una_basic .una_author,
.una_timeline_blk.una_basic .una_timeline_box::before,
.una_one_user .una_timeline .una_item_timeline::before{
    border-color: rgba('.$color.',0.8);
}
.una_one_user .una_timeline .una_date{
    background-color: rgba('.$color.',0.25);
}
.una_timeline_blk.una_basic .una_date{
    border-top-color: rgba('.$color.',0.8);
}
.una_timeline_blk.una_modern .una_item_timeline {
    background-color: #fff;
    border-left-color: rgba('.$color.',0.8);
    box-shadow: 0 0 100px 999px rgba('.$color.',0.2) inset;
}
</style>';
}
add_action('wp_footer','una_inline_css');




// стили для админки настроек
function una_admin_styles(){
    $chr_page = get_current_screen();
    if($chr_page->base != 'toplevel_page_manage-wprecall' ) return;

$out = '<style>
#options-universe-activity {
    box-shadow: 5px 5px 10px #ccc;
}
</style>';

    echo $out;
}
add_filter('admin_footer', 'una_admin_styles');


