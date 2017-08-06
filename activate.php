<?php

global $wpdb;

$wpdb->hide_errors();

$collate = '';

if ( $wpdb->has_cap( 'collation' ) ) {
    if ( ! empty( $wpdb->charset ) ) {
        $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
    }
    if ( ! empty( $wpdb->collate ) ) {
        $collate .= " COLLATE $wpdb->collate";
    }
}

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
$una_tables = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "otfm_universe_activity` (
                `id` bigint(20) unsigned NOT NULL auto_increment,
                `user_id` bigint(20) NOT NULL default '0',
                `action` varchar(255) NOT NULL,
                `act_date` datetime NOT NULL default '0000-00-00 00:00:00',
                `object_id` bigint(20) NOT NULL,
                `object_name` varchar(255) NOT NULL,
                `object_type` varchar(255) NOT NULL,
                `subject_id` bigint(20) NOT NULL,
                `other_info` text NOT NULL,
                `user_ip` varchar(55) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`)
            ) $collate;
        ";

dbDelta($una_tables);
