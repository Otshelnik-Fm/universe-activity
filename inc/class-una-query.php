<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

class UNA_Activity_Query extends Rcl_Query {
    function __construct() {
        $table = array(
            'name' => UNA_DB,
            'as'   => 'activity',
            'cols' => array(
                'id',
                'user_id',
                'action',
                'act_date',
                'object_id',
                'object_name',
                'object_type',
                'subject_id',
                'other_info',
                'user_ip',
                'hide',
                'group_id'
            )
        );

        parent::__construct( $table );
    }

}

// таблица юзеров
class UNA_Users_Query extends Rcl_Query {
    function __construct() {
        global $wpdb;

        $table = array(
            'name' => $wpdb->base_prefix . "users",
            'as'   => 'users',
            'cols' => array(
                'ID',
                'display_name'
            )
        );

        parent::__construct( $table );
    }

}

// таблица записей
class UNA_Posts_Query extends Rcl_Query {
    function __construct() {
        global $wpdb;

        $table = array(
            'name' => $wpdb->base_prefix . "posts",
            'as'   => 'posts',
            'cols' => array(
                'ID',
                'post_status'
            )
        );

        parent::__construct( $table );
    }

}
