<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class UNA_Get_DB {
    
    // подготовим массив (чтоб потом подсчитать результаты и вывести их)
    public function una_get_db_data($args){

        $include = array();
        $access = $this->una_include_exclude($args); // или получим массив разрешено-запрещено для текущего юзера

        if($args['include_actions']){ // если в атрибутах есть что включить или исключить
            $include_non_priv = explode(",", $args['include_actions']);         // массив атрибута include - без учета прав юзера
            $include = array_intersect($access['include'], $include_non_priv);  // учитываем права юзера на событие
        } else { // или покажем всю ленту событий
            $include_non_ex = $access['include'];  // разрешенные текущему юзеру
            $deduct = explode(",", $args['exclude_actions']); // пришли в аргументе для исключения
            $include = array_values(array_diff($include_non_ex, $deduct)); // вычтем их из массива и переиндексируем его
        }

        $argum = array(
            'action__in' => $include,
            'user_id__in' => $args['include_users'],
            'user_id__not_in' => $args['exclude_users'],
        );

        return $argum;
    }


    // получим результаты
    public function una_get_results($args){
        $una_db_query = new UNA_Activity_Query();
        $users_db_query = new UNA_Users_Query();

        $argum = $this->una_get_db_data($args);

        $argum['number'] = $args['number'];
        if( isset($args['offset']) ){
            $argum['offset'] = $args['offset'];
        }
        $argum['orderby'] = 'act_date';
        $argum['return_as'] = 'ARRAY_A';
        $argum['join_query'] = array(
                array(
                    'join' => 'LEFT',
                    'table' => $users_db_query->query['table'],
                    'on_user_id' => 'ID',
                    'fields' => array(
                        'display_name'
                    ),
                )
            );

        $result = $una_db_query->get_results($argum);

        return $result;
    }


    // сформируем массив для бд include-exclude
    private function una_include_exclude($args){
        $type = una_register_type_callback();               // зарегистрированные типы

        $priv = $this->una_current_user_privilege($args);  // какие привелегии

        foreach ($type as $k => $v){
             // не указан доступ или пусто - значит всем разрешен. И если разрешения совпадают с текущим юзером - показываем
            if(!isset($v['access']) || empty($v['access']) || in_array($v['access'], $priv) ){
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
    // в зависимости от роли юзера - его привелегии к просмотру
    private function una_current_user_privilege($args){
        global $user_ID;
        $priv = array();

         // авторизован и выборка по нему - значит это автор. Ну и админу можно
        if( (!empty($args['include_users']) && $args['include_users'] == $user_ID) || current_user_can('manage_options') ){
            $priv = array('logged', 'author');
        } else if($user_ID > 0){                    // залогинен
            $priv = array('logged');
        }
        if(current_user_can('manage_options')){     // это админ
            $priv = array('logged', 'author', 'admin');
        }

        return $priv;
    }

}

