<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

class UNA_Get_DB {
    // подготовим массив (чтоб потом подсчитать результаты и вывести их)
    public function una_get_db_data( $args ) {

        $include          = array();
        $include_non_priv = array();

        $access = $this->una_include_exclude( $args ); // или получим массив разрешено-запрещено для текущего юзера

        if ( $args['include_actions'] ) {                                               // если в атрибутах есть что включить или исключить
            if ( ! is_array( $args['include_actions'] ) ) {
                $include_non_priv = explode( ",", $args['include_actions'] );         // массив атрибута include - без учета прав юзера
            } else {
                $include_non_priv = $args['include_actions'];
            }

            $include_non_priv_cl = array_map( 'trim', $include_non_priv );             // удалим из значений массива пробелы если есть
            $includes            = array_intersect( $access['include'], $include_non_priv_cl );  // учитываем права юзера на событие
            $include             = array_values( $includes );                                     // переиндексируем

            if ( empty( $include ) ) { // событий для текущего юзера нет - вернем "not_found" (это событие для него запрещено)
                $include = array( 'result' => 'not_found' );
                return $include;
            }
        } else { // или покажем всю ленту событий
            $include_non_ex = $access['include'];                               // разрешенные текущему юзеру
            $deduct         = explode( ",", $args['exclude_actions'] );                   // пришли в аргументе для исключения
            $deduct_cl      = array_map( 'trim', $deduct );                             // удалим из значений массива пробелы если есть
            //
            // может быть пусто: exclude_actions = доступным событиям пользователя - выводить нечего
            $include        = array_values( array_diff( $include_non_ex, $deduct_cl ) );   // вычтем их из массива и переиндексируем его
        }

        $argum = array(
            'action__in'      => $include,
            'user_id__in'     => $args['include_users'],
            'user_id__not_in' => $args['exclude_users'],
            'object_type__in' => $args['object_type__in'],
            'group_id__in'    => $args['group_id__in'],
        );

        return $argum;
    }

    // получим результаты
    public function una_get_results( $args ) {
        $una_db_query   = new UNA_Activity_Query();
        $users_db_query = new UNA_Users_Query(); // таблица юзеров из class-una-query.php
        $posts_db_query = new UNA_Posts_Query(); // таблица записей

        $argum = $this->una_get_db_data( $args );

        // exclude_actions = доступным событиям пользователя - выводить нечего
        if ( empty( $argum['action__in'] ) )
            return;

        $argum['number'] = $args['number'];
        if ( isset( $args['offset'] ) ) {
            $argum['offset'] = $args['offset'];
        }
        $argum['orderby']    = 'act_date DESC,activity.id';
        $argum['return_as']  = 'ARRAY_A';
        $argum['join_query'] = array(
            array(
                'join'       => 'LEFT',
                'table'      => $users_db_query->query['table'],
                'on_user_id' => 'ID',
                'fields'     => array(
                    'display_name'
                ),
            ),
            array(
                'join'         => 'LEFT',
                'table'        => $posts_db_query->query['table'],
                'on_object_id' => 'ID',
                'action'       => 'add_post',
                'fields'       => array(
                    'post_status'
                ),
            ),
        );

        $una_db_query->set_query( $argum );

        if ( ! empty( $args['date_1'] ) ) {
            $current_date = ( ! empty( $args['date_2'] )) ? $args['date_2'] : current_time( 'mysql' );

            $una_db_query->query['where'][] = "activity.act_date BETWEEN CAST('" . $args['date_1'] . "' AS DATE) AND CAST('" . $current_date . "' AS DATETIME) ";
        }

        $result = $una_db_query->get_data( 'get_results' );

        return $result;
    }

    // сформируем массив для бд include-exclude
    private function una_include_exclude( $args ) {
        $type = una_register_type_callback();               // зарегистрированные типы

        $priv = $this->una_current_user_privilege( $args );  // какие привилегии

        foreach ( $type as $k => $v ) {
            // не указан доступ или пусто - значит всем разрешен. И если разрешения совпадают с текущим юзером - показываем
            if ( ! isset( $v['access'] ) || empty( $v['access'] ) || in_array( $v['access'], $priv ) ) {
                $result['include'][] = $k;
            } else {
                $result['exclude'][] = $k;
            }
        }

        return $result;
    }

    /*
      Текущий пользователь - какие права

      Если не указано - значит видят все
      logged - вошедший на сайт
      author - автор кабинета (и админ)
      admin -  только админ
     */
    // в зависимости от роли юзера - его привилегии к просмотру
    private function una_current_user_privilege( $args ) {
        $priv = array();
        global $user_ID;

        // авторизован и выборка по нему - значит это автор. Ну и админу можно
        if ( ( ! empty( $args['include_users'] ) && $args['include_users'] == $user_ID) || current_user_can( 'manage_options' ) ) {
            $priv = array( 'logged', 'author' );
        } else if ( $user_ID > 0 ) {                    // залогинен
            $priv = array( 'logged' );
        }
        if ( current_user_can( 'manage_options' ) ) {     // это админ
            $priv = array( 'logged', 'author', 'admin' );
        }

        return $priv;
    }

}
