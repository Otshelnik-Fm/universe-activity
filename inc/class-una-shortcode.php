<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

// переписать

class UNA_Shortcode {

    public $una_Date;

    function __construct() {
        do_action( 'una_start_shortcode' );     // маяк - шорткод в работе
        require_once('class-una-get-db.php');   // получение из базы
        rcl_enqueue_style( 'una_style', rcl_addon_url( 'css/una_core_style.css', __FILE__ ) );
    }

    // главная ф-ция - принимает по атрибутам и погнали
    public function get_universe( $atts ) {
        $attrs = shortcode_atts(
            array(
            'include_actions' => '', // включая события
            'exclude_actions' => '', // или исключая их
            'number'          => 30, // количество на страницу
            'include_users'   => '', // включая юзеров
            'exclude_users'   => '', // или исключая их
            'class'           => '', // css class главного блока
            'events_count'    => 1, // показать счетчик событий
            'use_name'        => 1, // в выводе нам нужна ава и имя
            'filter'          => 0, // фильтр над блоком
            'no_pagination'   => '', // отключить пагинацию
            'object_type__in' => '', // запрос по типу объекта
            'object_id__in'   => '', // запрос по id объекта
            'group_id__in'    => '',
            ), $atts, 'otfm_universe' );

        if ( $attrs['include_users'] === 'author_lk' ) {    // для вывода шорткодом в ЛК юзера
            global $user_LK;
            $attrs['include_users'] = $user_LK;
            $attrs['use_name']      = 0;
            $attrs['class']         = 'una_one_user';
            rcl_enqueue_style( 'una_one_user_style', rcl_addon_url( 'css/una_one_user.css', __FILE__ ) );
        }
        if ( $attrs['include_users'] === 'current' ) {      // если нужен вывод статы текущего юзера, но не админа
            global $user_ID;
            $attrs['include_users'] = $user_ID;
        }

        $is_filter = '';
        if ( $attrs['filter'] == 1 ) { // если фильтр разрешен к выводу
            $attrs     = $this->una_catch_get_attr( $attrs );
            $is_filter = $this->una_filter();
        }

        $count = $this->una_count_results( $attrs );

        if ( ! $count ) {
            return $is_filter . '<div class="una_data_not_found"><span>Активности нет</span></div>';
        }

        $class = sanitize_text_field( $attrs['class'] );

        $this->una_get_stylesheet_file( $class ); // подключим стили по переданному классу

        $encode = rcl_encode_post( $attrs );

        $out = '<div id="universe_time" class="una_timeline_blk ' . $class . '" data-una_total_items="' . $count . '" data-una_param="' . $encode . '">';
        $out .= $is_filter;
        $out .= $this->get_count_events( $attrs, $count );   // счетчик событий - если разрешен
        if ( rcl_exist_addon( 'universe-activity-extended' ) ) {
            $out .= '<div id="universe_visible"></div>';
        }
        $out .= '<div class="una_timeline">' . $this->una_get_data( $attrs, $count ) . '</div>';
        $out .= '</div>';

        return $out;
    }

    // посчитаем результаты для пагинации
    private function una_count_results( $args ) {
        $una_db_query = new UNA_Activity_Query();
        $get_data     = new UNA_Get_DB();

        $argum = $get_data->una_get_db_data( $args );

        // для текущего пользователя событий нет. Чтоб не дергать бд - вернем "0" и остановим
        if ( isset( $argum['result'] ) && $argum['result'] === 'not_found' ) {
            $count = 0;
            return $count;
        }

        $count = $una_db_query->count( $argum );

        return $count;
    }

    // получим данные для вывода
    public function una_get_data( $attrs, $count ) {               // внутри дохера напичкано. Потом перепишу
        global $current_screen;

        $navi = '';
        if ( $attrs['number'] > 0 && empty( $attrs['no_pagination'] ) ) {   // подключаем пагинацию
            if ( rcl_exist_addon( 'universe-activity-extended' ) ) {
                $paging          = una_extend_paging( $attrs, $count );
                $attrs['offset'] = $paging['offset'];
                $navi            = $paging['navi'];
            }
        }

        $get_data     = new UNA_Get_DB();
        $data_results = $get_data->una_get_results( $attrs );         // массив из БД

        $datas = apply_filters( 'una_get_data_db', $data_results );    // фильтр массива полученных данных. Можно применять для дополнения массива своими данными

        $type = una_register_type_callback();                       // получим массив зарегистрированных экшенов и функций
        $out  = '';
        $i    = 1;

        foreach ( $datas as $data ) {
            if ( array_key_exists( $data['action'], $type ) ) {          // проверим что зарегистрирован экшен
                if ( empty( $type[$data['action']] ) )
                    continue;         // если action-у не назначена функция - пропускаем

                if ( rcl_exist_addon( 'universe-activity-comments' ) ) { // покажем спам комментарии и на утверждении только админу
                    if ( $data['action'] == 'add_comment' && ! current_user_can( 'manage_options' ) ) {
                        if ( $data['comment_approved'] != 1 )
                            continue; // в массиве доступны новые данные
                    }
                }

                $author         = '';
                $user_name      = '';
                $una_even_class = '';
                if ( $i % 2 == 0 )
                    $una_even_class = 'una_even';     // каждый четный имеет класс

                $t_date = una_separate_date( $data['act_date'] ); // выведем одно число за сутки как заголовок
                if ( $this->una_Date != $t_date ) {
                    $this->una_Date = una_separate_date( $data['act_date'] );
                    $out            .= '<div class="una_date">' . una_human_days( $data['act_date'] ) . '</div>';
                }

                if ( $attrs['use_name'] ) { // в выводе нам нужна ава и имя
                    if ( $data['user_id'] > 0 ) {
                        $author    = '<a href="/?una_author=' . $data['user_id'] . '" title="Перейти в кабинет" rel="nofollow">' . get_avatar( $data['user_id'], 36 ) . '</a>';
                        $user_name = get_the_author_meta( 'display_name', $data['user_id'] );
                    } else if ( $data['user_id'] == 0 ) {
                        $user_name = 'Гость';
                    }
                    // wp cron
                    else if ( $data['user_id'] == "-1" ) {
                        $author = '<img alt="" src="' . rcl_addon_url( 'img/wp-cron.png?ver=1.0', __FILE__ ) . '" class="avatar avatar-wp-cron">';
                    }
                }

                if ( rcl_exist_addon( 'universe-activity-extended' ) ) {
                    if ( ! isset( $current_screen ) ) { // мы не в админке (это не ajax вызов)
                        $out .= unae_dop_hook( $i );
                    }
                }

                $attr_val = array( 'modal_class' => '', 'data_attr' => '', );
                if ( rcl_exist_addon( 'universe-activity-modal' ) && $data['action'] == 'add_post' ) {  // интересуют только записи
                    if ( ! isset( $current_screen ) ) {                                                 // мы не в админке (это не ajax вызов)
                        $status = $data['post_status'];
                        if ( $status == 'publish' ) {                                                   // и опубликованные
                            $attr_val = unam_set_modal_attr( $data );
                        }
                    }
                }


                $out .= '<div class="una_item_timeline ' . $una_even_class . ' una_' . $data['action'] . ' ' . $attr_val['modal_class'] . ' una_id_' . $data['id'] . '" data-unam_data="' . $attr_val['data_attr'] . '">';
                if ( $attrs['use_name'] ) {
                    $out .= '<div class="una_author">';
                    $out .= $author;
                    $out .= '</div>';
                }
                $out .= '<div class="una_right">';
                $out .= una_separate_time( $data['act_date'] );
                $out .= '<div class="una_content">';
                if ( $attrs['use_name'] )
                    $out .= '<span class="una_author_name">' . $user_name . '</span>';
                $out .= call_user_func( $type[$data['action']]['callback'], $data ); // вызовем ф-цию по ключу масива
                $out .= '</div>';
                $out .= '</div>';
                $out .= '</div>';

                $i ++;
            }
        }

        return '<div class="una_timeline_box">' . $out . '</div>' . $navi;
    }

    // текущий класс кнопки фильтра
    private function una_current_class( $filter ) {
        $una_filter = isset( $_GET['una_filter'] ) ? $_GET['una_filter'] : '';
        if ( ! $una_filter )
            $una_filter = 'all';
        if ( $filter === $una_filter )
            return 'filter-active';
    }

    // кнопки фильтра
    private function una_filter() {
        // текущий урл
        $cur_url = $_SERVER['REQUEST_URI'];
        // если мы в лк - вкладка грузится ajax-ом
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_POST['tab_url'] ) ) {
            $cur_url = $_POST['tab_url'];
        }

        $href_all           = add_query_arg( 'una_filter', 'all', $cur_url );
        $href_ratings       = add_query_arg( 'una_filter', 'ratings', $cur_url );
        $href_updates       = add_query_arg( 'una_filter', 'updates', $cur_url );
        $href_comments      = add_query_arg( 'una_filter', 'comments', $cur_url );
        $href_publications  = add_query_arg( 'una_filter', 'publications', $cur_url );
        $href_subscriptions = add_query_arg( 'una_filter', 'subscriptions', $cur_url );

        $out = '<div id="una_filters" class="una_data_filters">';
        $out .= '<a class="recall-button una_filter_all ' . $this->una_current_class( 'all' ) . '" href="' . $href_all . '">Все</a>';
        $out .= '<a class="recall-button una_filter_publications ' . $this->una_current_class( 'publications' ) . '" href="' . $href_publications . '">Публикации</a>';
        $out .= '<a class="recall-button una_filter_comments ' . $this->una_current_class( 'comments' ) . '" href="' . $href_comments . '">Комментарии</a>';
        if ( rcl_exist_addon( 'rating-system' ) ) {
            $out .= '<a class="recall-button una_filter_ratings ' . $this->una_current_class( 'ratings' ) . '" href="' . $href_ratings . '">Рейтинг</a>';
        }
        $out .= '<a class="recall-button una_filter_updates ' . $this->una_current_class( 'updates' ) . '" href="' . $href_updates . '">Обновления</a>';
        if ( rcl_exist_addon( 'feed' ) ) {
            $out .= '<a class="recall-button una_filter_subscriptions ' . $this->una_current_class( 'subscriptions' ) . '" href="' . $href_subscriptions . '">Подписки</a>';
        }
        $out .= '</div>';

        return $out;
    }

    // по гет запросу передаем в шорткод параметры
    private function una_catch_get_attr( $attrs ) {
        $una_filter = isset( $_GET['una_filter'] ) ? $_GET['una_filter'] : '';
        $get_filter = sanitize_key( $una_filter );

        if ( $get_filter == 'publications' ) {
            $attrs['include_actions'] = apply_filters( 'una_filter_publications', array( 'add_post', ) );
        } else if ( $get_filter == 'comments' ) {
            $attrs['include_actions'] = apply_filters( 'una_filter_comments', array( 'add_comment', ) );
        } else if ( $get_filter == 'ratings' ) {
            $attrs['include_actions'] = apply_filters( 'una_filter_ratings', array( 'give_rating_comment,give_rating_notes,give_rating_post,give_rating_forum-page,give_rating_post-group,give_rating_products,give_rating_forum-post', ) );
        } else if ( $get_filter == 'updates' ) {
            $attrs['include_actions'] = apply_filters( 'una_filter_updates', array( 'change_status', 'profile_update', 'create_group', 'user_in_group', 'pfm_add_topic', 'asgrs_add_topic', 'add_cover', 'add_avatar', ) );
        } else if ( $get_filter == 'subscriptions' ) {
            $attrs['include_actions'] = apply_filters( 'una_filter_subscriptions', array( 'add_user_feed', ) );
        }

        if ( ! empty( $get_filter ) ) {
            $attrs['class'] .= ' una_wrapper_' . $get_filter . ' ';
        } else {
            $attrs['class'] .= ' una_wrapper_all ';
        }

        return $attrs;
    }

    // подключим стили по переданному классу
    private function una_get_stylesheet_file( $class ) {
        if ( strpos( $class, 'una_zebra' ) !== false ) {
            rcl_enqueue_style( 'una_zebra_style', rcl_addon_url( 'css/una_zebra.css', __FILE__ ) );
        } else if ( strpos( $class, 'una_basic' ) !== false ) {
            rcl_enqueue_style( 'una_basic_style', rcl_addon_url( 'css/una_basic.css', __FILE__ ) );
        } else if ( strpos( $class, 'una_modern' ) !== false ) {
            rcl_enqueue_style( 'una_modern_style', rcl_addon_url( 'css/una_modern.css', __FILE__ ) );
        } else if ( strpos( $class, 'una_card' ) !== false ) {
            rcl_enqueue_style( 'una_card_style', rcl_addon_url( 'css/una_card.css', __FILE__ ) );
        }
    }

    // подключим счетчик событий
    private function get_count_events( $attrs, $count ) {
        if ( $attrs['events_count'] == 0 )
            return false;

        $out = '<div id="una_head" class="una_header">';
        $out .= '<span>Событий:</span>';
        $out .= '<span>' . $count . '</span>';
        $out .= '</div>';

        return $out;
    }

}
