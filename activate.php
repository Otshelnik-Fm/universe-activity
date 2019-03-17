<?php

$una_db_version = '1.1.0';

global $wpdb;

$wpdb->hide_errors();

$collate = '';

if ( $wpdb->has_cap( 'collation' ) ) {
	if ( !empty( $wpdb->charset ) ) {
		$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
	}
	if ( !empty( $wpdb->collate ) ) {
		$collate .= " COLLATE $wpdb->collate";
	}
}

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

$table = $wpdb->prefix . 'otfm_universe_activity';

if ( $wpdb->get_var( "show tables like '" . $table . "'" ) != $table ) {
	$una_tables = "CREATE TABLE IF NOT EXISTS `" . $table . "` (
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
					`group_id` bigint(20) unsigned,
					`hide` tinyint unsigned NOT NULL default '0',
					PRIMARY KEY (`id`),
					KEY `user_id` (`user_id`),
					KEY `group_id` (`group_id`)
				) $collate;
			";

	dbDelta( $una_tables );

	update_option( 'universe_activity_db_ver', $una_db_version, false );
} else {
	$db_version = get_option( 'universe_activity_db_ver' );
	if ( !$db_version || $db_version == '1.0.0' ) {
		$wpdb->query( "ALTER TABLE " . $table . " ADD `group_id` bigint(20) unsigned AFTER `user_ip`" );
		$wpdb->query( "ALTER TABLE " . $table . " ADD `hide` tinyint unsigned NOT NULL default '0' AFTER `user_ip`" );
		$wpdb->query( "ALTER TABLE " . $table . " ADD KEY `group_id` (`group_id`)" );

		update_option( 'universe_activity_db_ver', $una_db_version, false );
	}
}
