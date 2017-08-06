<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class UNA_Register_Type_Callback {
   
    // массив зарегистрированных действий и привязанным к ним функций (которые и будут их вывод обрабатывать). callback.php
    public function get_type_callback(){
        $type = array(
            'add_comment' => array(
                                'callback'  => 'una_get_add_comment',
                            ),
            'add_draft' => array(
                                'callback'  => 'una_get_add_draft',
                                'access'    => 'logged',
                            ),
            'add_post' => array(
                                'callback'  => 'una_get_add_post',
                            ),
            'change_status' => array(
                                'callback'  => 'una_get_change_user_status',
                            ),
            'confirm_register' => array(
                                'callback'  => 'una_get_confirm_register',
                                'access'    => 'logged',
                        ),
            'delete_post' => array(
                                'callback'  => 'una_get_delete_post',
                                'access'    => 'admin',
                            ),
            'delete_post_fully' => array(
                                'callback'  => 'una_get_delete_post',
                                'access'    => 'admin',
                            ),
            'delete_user' => array(
                                'callback'  => 'una_get_delete_user',
                                'access'    => 'admin',
                            ),
            'give_rating_comment' => array(
                                'callback'  => 'una_get_give_rating_post',
                            ),
            'give_rating_notes' => array(
                                'callback'  => 'una_get_give_rating_post',
                            ),
            'give_rating_post' => array(
                                'callback'  => 'una_get_give_rating_post',
                            ),
            'give_rating_post-group' => array(
                                'callback'  => 'una_get_give_rating_post',
                            ),
            'give_rating_products' => array(
                                'callback'  => 'una_get_give_rating_post',
                            ),
            'logged_in' => array(
                            'callback'  => 'una_get_logged_in_out',
                            'access'    => 'author',
                        ),
            'logged_in_ulogin' => array(
                            'callback'  => 'una_get_logged_in_out',
                            'access'    => 'author',
                        ),
            'logged_out' => array(
                            'callback'  => 'una_get_logged_in_out',
                            'access'    => 'author',
                        ),
            'profile_update' => array(
                                'callback'  => 'una_get_profile_update',
                                'access'    => 'admin',
                            ),
            'register' => array(
                                'callback'  => 'una_get_register',
                                'access'    => 'logged',
                            ),
            'register_failed' => array(
                                'callback'  => 'una_register_failed',
                                'access'    => 'admin',
                            ),
            'add_user_feed' => array(
                                'callback'  => 'una_get_add_user_feed',
                            ),
            'del_user_feed' => array(
                                'callback'  => 'una_get_del_user_feed',
                                'access'    => 'logged',
                            ),
            'add_user_blacklist' => array(
                                'callback'  => 'una_get_add_user_blacklist',
                                'access'    => 'logged',
                            ),
            'del_user_blacklist' => array(
                                'callback'  => 'una_get_del_user_blacklist',
                                'access'    => 'logged',
                            ),
            'create_group' => array(
                                'callback'  => 'una_get_create_group',
                            ),
            'delete_group' => array(
                                'callback'  => 'una_get_delete_group',
                                'access'    => 'admin',
                            ),
            'user_in_group' => array(
                                'callback'  => 'una_get_user_in_out_group',
                            ),
            'user_out_group' => array(
                                'callback'  => 'una_get_user_in_out_group',
                                'access'    => 'logged',
                            ),
            'pfm_add_topic' => array(
                                'callback'  => 'una_get_user_add_topic',
                            ),
            'pfm_del_topic' => array(
                                'callback'  => 'una_get_user_del_topic',
                                'access'    => 'admin',
                            ),
        );
        $types = apply_filters('una_register_type', $type); // чтобы можно было зарегистрировать тип и коллбэк функцию

        return $types;
    }
   
}

