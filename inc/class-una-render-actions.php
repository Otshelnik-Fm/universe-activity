<?php

if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * формирование списка событий для настроек
 *
 * @since 1.2
 *
 * @param array     $args['slug']               уникальный slug настроек. Обязательно
 *                  $args['exclude-access']     массив исключаемого уровня доступа пользователя (admin, author, logged)
 *                  $args['exclude-actions']    массив исключаемых действий
 *                  $args['split']              1 - чтобы группировать по источнику. По умолчанию 0
 *                  $args['column']             1 - чтобы включить отображение колонкой. По умолчанию 0
 *                  $args['save']               default: global. Also 'usermeta' or 'postmeta'
 *
 * @return string checkboxs list.
 */
class UNA_Render_Actions {

    public $slug            = false;
    public $exclude_access  = false;
    public $exclude_actions = false;
    public $split           = false;
    public $actions         = [];
    public $column          = false;
    public $save            = 'global';

    function __construct( $args ) {
        $this->slug = (isset( $args['slug'] )) ? $args['slug'] : false;

        $this->exclude_access  = (isset( $args['exclude-access'] )) ? $args['exclude-access'] : false;
        $this->exclude_actions = (isset( $args['exclude-actions'] )) ? $args['exclude-actions'] : false;

        $this->split  = (isset( $args['split'] ) && ! empty( $args['split'] )) ? true : false;
        $this->column = (isset( $args['column'] ) && ! empty( $args['column'] )) ? true : false;

        $this->save = (isset( $args['save'] )) ? $args['save'] : 'global';

        $this->actions = $this->get_allowed_types();
    }

    // вывод чекбоксов
    function render() {
        if ( ! $this->slug )
            return;

        $my_actions = [];

        if ( $this->split ) {
            $my_actions = $this->separate_datas_by_types();
        } else {
            $my_actions = $this->separate_datas();
        }

        return $this->render_controls( $my_actions );
    }

    // исключить действия или привилегии
    function get_allowed_types() {
        $actions = una_register_type_callback();

        if ( ! $this->exclude_access && ! $this->exclude_actions )
            return $actions;

        $allowed = [];

        foreach ( $actions as $k => $val ) {
            // исключение по привилегии
            if ( $this->exclude_access && isset( $val['access'] ) && in_array( $val['access'], $this->exclude_access ) )
                continue;

            // исключение по действиям
            if ( $this->exclude_actions && in_array( $k, $this->exclude_actions ) )
                continue;

            $allowed[$k] = $val;
        }

        return $allowed;
    }

    // получим массив сгруппированный по источнику:
    // 'wordpress' => [тип->название]
    function separate_datas_by_types() {
        $datas = [];

        foreach ( $this->actions as $k => $val ) {
            if ( ! isset( $val['source'] ) ) {
                $datas['other'][$k] = $k;
            } else {
                $datas[$val['source']][$k] = $val['name'];
            }
        }

        // все что без источника - в конец списка
        if ( isset( $datas['other'] ) ) {
            $last = $datas['other'];
            unset( $datas['other'] );
            array_push( $datas, $last );
        }


        return $datas;
    }

    // обычный массив - тип->название
    function separate_datas() {
        $datas = [];

        foreach ( $this->actions as $action => $v ) {
            if ( ! isset( $v['name'] ) ) {
                $v['name'] = $action;
            }

            $datas[$action] = $v['name'];
        }

        return $datas;
    }

    // генерация для настроек. Типа поля custom
    function render_controls( $datas ) {
        // опций нет
        if ( empty( $datas ) ) {
            return false;
        }


        $style = 'style="background:#fff;padding:12px;margin:12px 6px;border:1px solid #ddd;"';
        $out   = '';

        // многомерный массив - группировка по источнику действия
        if ( is_array( reset( $datas ) ) ) {
            foreach ( $datas as $type => $data ) {
                $title = '<div class="una_name_source" style="font-size:16px;font-weight:bold;margin:0 6px 9px;">' . $this->fix_title( $type ) . '<span class="colon">:</span></div>';

                $out .= '<div class="' . $this->slug . '_checkbox una_checkbox_list" ' . $style . '>' . $title . $this->get_all_checkboxes( $data ) . '</div>';
            }
        } else {
            $out .= '<div class="' . $this->slug . '_checkbox una_checkbox_list" ' . $style . '>' . $this->get_all_checkboxes( $datas ) . '</div>';
        }

        return $out;
    }

    // получим чекбоксы
    function get_all_checkboxes( $datas ) {
        $checkboxes = '';

        foreach ( $datas as $action => $name ) {
            $checkboxes .= $this->get_checkbox( $action, $name );
        }

        return $checkboxes;
    }

    // сформируем чекбокс
    function get_checkbox( $action, $name ) {
        $column = ($this->column) ? 'block' : 'inline';

        // в опции есть действие?
        $checked = ( $this->get_saved_data() ) ? in_array( $action, $this->get_saved_data() ) : false;

        $checkbox = '<span class="rcl-checkbox-box checkbox-display-' . $column . '">';
        $checkbox .= '<input id="' . $this->slug . '_' . $action . '" type="checkbox" class="checkbox-field" ' . checked( $checked, 1, false ) . ' name="rcl_global_options[' . $this->slug . '][]" value="' . $action . '">';
        $checkbox .= '<label class="block-label" for="' . $this->slug . '_' . $action . '">' . $name . '</label>';
        $checkbox .= '</span>';

        return $checkbox;
    }

    // где храним данные?
    function get_saved_data() {
        // в глобальных опциях сайта
        if ( $this->save == 'global' ) {
            return rcl_get_option( $this->slug );
        }
        // в метаполе юзера
        else if ( $this->save == 'usermeta' ) {
            global $user_ID;

            $data = get_user_meta( $user_ID, $this->slug );

            return ( empty( $data ) ) ? false : $data[0];
        }
        // в метаполе записи
        else if ( $this->save == 'postmeta' ) {
            global $post;

            $data = get_post_meta( $post->ID, $this->slug );

            return ( empty( $data ) ) ? false : $data[0];
        }
    }

    // сформируем заголовки верно
    function fix_title( $type ) {
        if ( $type == 'other' )
            return 'Другое';

        if ( $type == 'wp-recall' )
            return 'WP-Recall';

        if ( $type == 'wordpress' )
            return 'WordPress';

        $title     = str_replace( [ "-", "–" ], ' ', $type );
        $title_out = str_replace( [ "WP Recall" ], 'WP-Recall', $title );

        return ucwords( $title_out );
    }

}
