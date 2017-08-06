<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// переписать

class UNA_shortcode {
   
    function __construct() {
        require_once('class-una-get-db.php');       // получение из базы
        rcl_enqueue_style('una_style',rcl_addon_url('una-style.css', __FILE__));
    }
    
    // главная ф-ция - принимает по атрибутам и погнали
    public function get_universe($atts){
        $attrs = shortcode_atts(
                array(
                    'include_actions' => '',    // включая события
                    'exclude_actions' => '',    // или исключая их
                    'number' => 30,             // количество на страницу
                    'include_users' => '',      // включая юзеров
                    'exclude_users' => '',      // или исключая их
                    'class' => '',              // css class главного блока
                    'mini_stat' => '',          // 
                    'use_name' => 1,            // в выводе нам нужна ава и имя
                    'filter' => 0,              // фильтр над блоком
                ), $atts, 'otfm_universe');

        if($attrs['include_users'] === 'author_lk'){ // для вывода шорткодом в ЛК юзера
            global $user_LK;
            $attrs['include_users'] = $user_LK;
            $attrs['use_name'] = 0;
            $attrs['class'] = 'una_one_user';
            rcl_enqueue_style('una_one_user_style',rcl_addon_url('css/una_one_user.css', __FILE__));
        }

        $is_filter = '';
        if($attrs['filter'] == 1){ // если фильтр разрешен к выводу
            $attrs = $this->una_catch_get_attr($attrs);
            $is_filter = $this->una_filter();
        }

        $count = $this->una_count_results($attrs);

        if(!$count){
            return $is_filter.'<div class="una_data_not_found"><span>Активности нет</span></div>';
        }

        $class = sanitize_key($attrs['class']);

        $this->una_get_stylesheet_file($class); // подключим стили по переданному классу

        $encode = rcl_encode_post($attrs);

        $out = '<div id="universe_time" class="una_timeline_blk '.$class.'" data-una_total_items="'.$count.'" data-una_param="'.$encode.'">';
            $out .= $is_filter;
            $out .= '<div id="una_head" class="una_header">';
                $out .= '<span>Событий:</span>';
                $out .= '<span>'.$count.'</span>';
            $out .= '</div>';
            if(rcl_exist_addon('universe-activity-extended')){
                $out .= '<div id="universe_visible"></div>';
            }
            $out .= '<div class="una_timeline">' . $this->una_get_data($attrs, $count) . '</div>';
        $out .= '</div>';

        return $out;
    }


    // посчитаем результаты для пагинации
    private function una_count_results($args){
        $una_db_query = new UNA_Activity_Query();
        $get_data = new UNA_Get_DB();

        $argum = $get_data->una_get_db_data($args);

        $count = $una_db_query->count($argum);

        return $count;
    }


    // получим данные для вывода
    public function una_get_data($attrs, $count){
        global $una_Date;

        $navi = '';
        if($attrs['number'] > 0){
            if(rcl_exist_addon('universe-activity-extended')){
                $paging = una_extend_paging($attrs, $count);
                $attrs['offset'] = $paging['offset'];
                $navi = $paging['navi'];
            }

        }

        $get_data = new UNA_Get_DB();
        $datas = $get_data->una_get_results($attrs);   // массив из БД
        
        $type = una_register_type_callback();   // получим массив зарегистрированных экшенов и функций
        $out = '';
        $i = 1;
        foreach ($datas as $data){
            if( array_key_exists($data['action'], $type)){      // проверим что зарегистрирован экшен
                if(empty($type[$data['action']])) continue;     // если action-у не назначена функция - пропускаем

                $author = '';
                $user_name = '';
                $una_even_class = '';
                if($i%2 == 0) $una_even_class = 'una_even'; // каждый четный имеет класс

                $t_date = una_separate_date($data['act_date']); // выведем одно число за сутки как заголовок
                if($una_Date != $t_date){
                    $una_Date = una_separate_date($data['act_date']);
                    $out .= '<div class="una_date">' . una_human_days($data['act_date']) . '</div>';
                }

                if($attrs['use_name']){ // в выводе нам нужна ава и имя
                    if($data['user_id'] > 0){
                        $author = '<a href="/?author='.$data['user_id'].'" title="Перейти в кабинет" rel="nofollow">'.get_avatar($data['user_id'], 32).'</a>';
                        $user_name = get_the_author_meta('display_name', $data['user_id']);
                    } else if ($data['user_id'] == 0) {
                        $user_name = 'Гость';
                    }
                }

                $out .= '<div class="una_item_timeline '.$una_even_class.' una_'.$data['action'].'" data-una_id="'.$data['id'].'">';
                    if($attrs['use_name']){
                        $out .= '<div class="una_author">';
                            $out .= $author;
                        $out .= '</div>';
                    }
                    $out .= '<div class="una_right">';
                        $out .= una_separate_time($data['act_date']);
                        $out .= '<div class="una_content">';
                            if($attrs['use_name']) $out .= '<span class="una_author_name">' . $user_name . '</span>';
                            $out .= call_user_func($type[$data['action']]['callback'], $data); // вызовем ф-цию по ключу масива
                        $out .= '</div>';
                    $out .= '</div>';
                $out .= '</div>';

                $i++;
            }
        }

        return '<div class="una_timeline_box">'.$out.'</div>'.$navi;
    }



    // текущий класс кнопки фильтра
    private function una_current_class($filter){
        $una_filter = isset($_GET['una_filter']) ? $_GET['una_filter'] : '';
        if(!$una_filter) $una_filter = 'all';
        if($filter === $una_filter) return 'filter-active';

    }

    
    // кнопки фильтра
    private function una_filter(){
        $cur_url = $_SERVER['REQUEST_URI'];                                     // текущий урл
        if(defined( 'DOING_AJAX' ) && DOING_AJAX && isset($_POST['tab_url'])){  // если мы в лк - вкладка грузится ajax-ом
            $cur_url = $_POST['tab_url'];
        }

        $href_all = add_query_arg(array('una_filter'=>false), $cur_url);
        $href_ratings = add_query_arg('una_filter', 'ratings', $cur_url);
        $href_updates = add_query_arg('una_filter', 'updates', $cur_url);
        $href_comments = add_query_arg('una_filter', 'comments', $cur_url);
        $href_publications = add_query_arg('una_filter', 'publications', $cur_url);
        $href_subscriptions = add_query_arg('una_filter', 'subscriptions', $cur_url);

        $out = '<div id="una_filters" class="una_data_filters">';
            $out .= '<a class="recall-button una_filter_all '.$this->una_current_class('all').'" href="'.$href_all.'">Все</a>';
            $out .= '<a class="recall-button una_filter_publications '.$this->una_current_class('publications').'" href="'.$href_publications.'">Публикации</a>';
            $out .= '<a class="recall-button una_filter_comments '.$this->una_current_class('comments').'" href="'.$href_comments.'">Комментарии</a>';
            if(rcl_exist_addon('rating-system')){
                $out .= '<a class="recall-button una_filter_ratings '.$this->una_current_class('ratings').'" href="'.$href_ratings.'">Рейтинг</a>';
            }
            $out .= '<a class="recall-button una_filter_updates '.$this->una_current_class('updates').'" href="'.$href_updates.'">Обновления</a>';
            if(rcl_exist_addon('feed')){
                $out .= '<a class="recall-button una_filter_subscriptions '.$this->una_current_class('subscriptions').'" href="'.$href_subscriptions.'">Подписки</a>';
            }
        $out .= '</div>';

        return $out;
    }

    
    // по гет запросу передаем в шорткод параметры
    private function una_catch_get_attr($attrs){
        $una_filter = isset($_GET['una_filter']) ? $_GET['una_filter'] : '';
        $get_filter = sanitize_key($una_filter);

        if($get_filter == 'publications'){
            $attrs['include_actions'] = 'add_post';
        } else if($get_filter == 'comments'){
            $attrs['include_actions'] = 'add_comment';
        } else if($get_filter == 'ratings'){
            $attrs['include_actions'] = 'give_rating_comment,give_rating_notes,give_rating_post,give_rating_post-group,give_rating_products';
        } else if($get_filter == 'updates'){
            $attrs['include_actions'] = 'change_status,profile_update,create_group,user_in_group,pfm_add_topic';
        } else if($get_filter == 'subscriptions'){
            $attrs['include_actions'] = 'add_user_feed';
        }
        return $attrs;
    }
    
    
    // подключим стили по переданному классу
    private function una_get_stylesheet_file($class){
        if($class === 'una_zebra'){
            rcl_enqueue_style('una_zebra_style',rcl_addon_url('css/una_zebra.css', __FILE__));
        } else if ($class === 'una_basic'){
            rcl_enqueue_style('una_basic_style',rcl_addon_url('css/una_basic.css', __FILE__));
        } else if ($class === 'una_modern'){
            rcl_enqueue_style('una_modern_style',rcl_addon_url('css/una_modern.css', __FILE__));
        }
    }

}


