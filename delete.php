<?php

global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS `".$wpdb->prefix."otfm_universe_activity`");

delete_option('universe_activity_db_ver');
