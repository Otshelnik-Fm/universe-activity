<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class UNA_Activity_Query extends Rcl_Query {
    
    function __construct() { 
        global $wpdb;
        
        $table = array(
            'name' => $wpdb->base_prefix ."otfm_universe_activity",
            'as' => 'activity',
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
                'user_ip'
            )
        );
        
        parent::__construct($table);
    }
}


class UNA_Users_Query extends Rcl_Query {
    
    function __construct() { 
        global $wpdb;
        
        $table = array(
            'name' => $wpdb->base_prefix ."users",
            'as' => 'users',
            'cols' => array(
                'ID',
                'display_name'
            )
        );
        
        parent::__construct($table);
    }
}

