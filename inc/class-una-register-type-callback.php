<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class UNA_Register_Type_Callback {

    // массив зарегистрированных действий и привязанным к ним функций (которые и будут их вывод обрабатывать). callback.php
    public function get_type_callback(){
        $type = array(
            'add_comment' => array(                                         // добавлен комментарий
                                'callback'  => 'una_get_add_comment',
                            ),
            'add_draft' => array(                                           // добавил черновик
                                'callback'  => 'una_get_add_draft',
                                'access'    => 'logged',
                            ),
            'add_post' => array(                                            // добавлена запись
                                'callback'  => 'una_get_add_post',
                            ),
            'change_status' => array(                                       // юзер сменил свой статус
                                'callback'  => 'una_get_change_user_status',
                            ),
            'confirm_register' => array(                                    // подтвердил регистрацию
                                'callback'  => 'una_get_confirm_register',
                                'access'    => 'logged',
                        ),
            'delete_post' => array(                                         // удалил запись - в корзину
                                'callback'  => 'una_get_delete_post',
                                'access'    => 'admin',
                            ),
            'delete_post_fully' => array(                                   // удалил запись навсегда
                                'callback'  => 'una_get_delete_post',
                                'access'    => 'admin',
                            ),
            'delete_user' => array(                                         // удалил юзера
                                'callback'  => 'una_get_delete_user',
                                'access'    => 'admin',
                            ),
            'give_rating_comment' => array(                                 // рейтинг за комментарий
                                'callback'  => 'una_get_give_rating_post',
                            ),
            'give_rating_notes' => array(                                   // рейтинг за заметку
                                'callback'  => 'una_get_give_rating_post',
                            ),
            'give_rating_post' => array(                                    // рейтинг за запись - тип post
                                'callback'  => 'una_get_give_rating_post',
                            ),
            'give_rating_post-group' => array(                              // рейтинг за запись в группе - тип post-group
                                'callback'  => 'una_get_give_rating_post',
                            ),
            'give_rating_products' => array(                                // рейтинг за товар - тип products
                                'callback'  => 'una_get_give_rating_post',
                            ),
            'give_rating_forum-post' => array(                              // рейтинг за сообщение на Prime Forum
                                'callback'  => 'una_get_give_rating_post',
                            ),
            'logged_in' => array(                                           // когда он вошел на сайт
                            'callback'  => 'una_get_logged_in_out',
                            'access'    => 'author',
                        ),
            'logged_in_ulogin' => array(                                    // когда он вошел через плагин u-login
                            'callback'  => 'una_get_logged_in_out',
                            'access'    => 'author',
                        ),
            'logged_out' => array(                                          // когда он вышел с сайта
                            'callback'  => 'una_get_logged_in_out',
                            'access'    => 'author',
                        ),
            'profile_update' => array(                                      // обновил настройки профиля
                                'callback'  => 'una_get_profile_update',
                                'access'    => 'admin',
                            ),
            'register' => array(                                            // зарегистрировался
                                'callback'  => 'una_get_register',
                                'access'    => 'logged',
                            ),
            'register_failed' => array(                                     // неудачная регистрация
                                'callback'  => 'una_register_failed',
                                'access'    => 'admin',
                            ),
            'add_user_feed' => array(                                       // подписался на юзера
                                'callback'  => 'una_get_add_user_feed',
                            ),
            'del_user_feed' => array(                                       // отписался от юзера
                                'callback'  => 'una_get_del_user_feed',
                                'access'    => 'logged',
                            ),
            'add_user_blacklist' => array(                                  // добавил в черный список
                                'callback'  => 'una_get_add_user_blacklist',
                                'access'    => 'logged',
                            ),
            'del_user_blacklist' => array(                                  // удалил из черного списка
                                'callback'  => 'una_get_del_user_blacklist',
                                'access'    => 'logged',
                            ),
            'create_group' => array(                                        // создал группу
                                'callback'  => 'una_get_create_group',
                            ),
            'delete_group' => array(                                        // удалил группу
                                'callback'  => 'una_get_delete_group',
                                'access'    => 'admin',
                            ),
            'user_in_group' => array(                                       // юзер вступил в группу
                                'callback'  => 'una_get_user_in_out_group',
                            ),
            'user_out_group' => array(                                      // вышел из группы
                                'callback'  => 'una_get_user_in_out_group',
                                'access'    => 'logged',
                            ),
            'pfm_add_topic' => array(                                       // создана новая тема на Prime Forum
                                'callback'  => 'una_get_user_add_topic',
                            ),
            'pfm_del_topic' => array(                                       // удалил тему с форума (Prime Forum)
                                'callback'  => 'una_get_user_del_topic',
                                'access'    => 'admin',
                            ),
            'add_cover' => array(                                           // юзер добавил обложку в своём ЛК
                                'callback'  => 'una_get_add_cover',
                            ),
            'add_avatar' => array(                                          // юзер добавил (сменил) аватарку
                                'callback'  => 'una_get_add_avatar',
                            ),
            'del_avatar' => array(                                          // когда он удалил свой аватар
                                'callback'  => 'una_get_del_avatar',
                                'access'    => 'author',
                            ),
        );
        $types = apply_filters('una_register_type', $type); // чтобы можно было зарегистрировать тип и коллбэк функцию

        return $types;
    }

}

