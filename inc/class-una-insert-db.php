<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// переписать

class UNA_Insert_DB {

    // функция вставляет строку в БД (в fires.php)
    public function insert_db($argum){
        // предустановим некоторые аргументы
        $args = wp_parse_args( $argum, array(
			'action'		 => '',
			'act_date'		 => current_time( 'mysql' ),
			'object_id'		 => '',
			'object_name'	 => '',
			'object_type'	 => '',
			'subject_id'	 => '',
			'other_info'	 => '',
			'user_ip'		 => $this->una_get_ip(),
			'hide'			 => '',
			'group_id'		 => '',
			)
		);
		// чтобы в каждой функции не передавать ID юзера:
        if ( empty($args['user_id']) ){
            $cur_user_id = get_current_user_id();
            if ( empty($cur_user_id) ){
                $args['user_id'] = 0;
            } else {
                $args['user_id'] = $cur_user_id;
            }
        }

        // исключаем дубликаты
        if ( $this->una_check_duplicate($args) ) return false;

        global $wpdb;

        // всё ок, вставляем
        $wpdb->insert(
			$wpdb->prefix . "otfm_universe_activity", array(
			'user_id'		 => $args['user_id'],
			'action'		 => $args['action'],
			'act_date'		 => $args['act_date'],
			'object_id'		 => $args['object_id'],
			'object_name'	 => $args['object_name'],
			'object_type'	 => $args['object_type'],
			'subject_id'	 => $args['subject_id'],
			'other_info'	 => $args['other_info'],
			'user_ip'		 => $args['user_ip'],
			'hide'			 => $args['hide'],
			'group_id'		 => $args['group_id'],
			), array( '%d', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%d', '%d' )
		);
	}


    // получим ip
    private function una_get_ip(){
        $some_var = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        );

        foreach ($some_var as $var) {
            if ( isset($_SERVER[$var]) && filter_var($_SERVER[$var], FILTER_VALIDATE_IP) ){
                $ip = trim($_SERVER[$var]);
                return $ip;
            }
        }

        return '127.0.0.1'; // или локальный
    }


    // проверим на дубликаты
    private function una_check_duplicate($args){
        global $wpdb;

        $duplicate = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT `id` FROM ".$wpdb->prefix."otfm_universe_activity
                    WHERE `user_id` = '%d'
                        AND `action` = '%s'
                        AND `act_date` = '%s'
                        AND `object_id` = '%d'
                        AND `object_name` = '%s'
                        AND `object_type` = '%s'
                        AND `subject_id` = '%d'
                        AND `other_info` = '%s'
                        AND `user_ip` = '%s'
						AND `hide` = '%d'
						AND `group_id` = '%d'
                ;",
                $args['user_id'],
                $args['action'],
                $args['act_date'],
                $args['object_id'],
                $args['object_name'],
                $args['object_type'],
                $args['subject_id'],
                $args['other_info'],
                $args['user_ip'],
				$args['hide'],
				$args['group_id']
            )
        );
        
        return $duplicate;
    }


}


