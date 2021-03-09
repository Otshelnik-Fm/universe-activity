<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

class UNA_Register_Type_Callback {
    // массив зарегистрированных действий и привязанным к ним функций (которые и будут их вывод обрабатывать в inc/callbacks.php).
    public function get_type_callback() {
        $type = array(
            'register_failed'    => [
                'name'     => 'Неудачная регистрация', // Событие. "отвечая на вопрос: Что сделал? или Что?"
                'source'   => 'wordpress', ////////////// Источник (wordpress, плагин, аддон - slug аддона или имя, как в списке допов)
                'callback' => 'una_register_failed', //// Функция обработчик (вывод)
                'access'   => 'admin', ////////////////// Доступ. Если не указано - доступ всем. logged, author, admin
            ],
            'register'           => [
                'name'     => 'Регистрация пользователя',
                'source'   => 'wordpress',
                'callback' => 'una_get_register',
                'access'   => 'logged',
            ],
            'confirm_register'   => [
                'name'     => 'Подтвердил свою регистрацию',
                'source'   => 'wp-recall',
                'callback' => 'una_get_confirm_register',
                'access'   => 'logged',
            ],
            'add_comment'        => [
                'name'     => 'Оставил комментарий',
                'source'   => 'wordpress',
                'callback' => 'una_get_add_comment',
            ],
            'add_post'           => [
                'name'     => 'Опубликовал новую запись',
                'source'   => 'wordpress',
                'callback' => 'una_get_add_post',
            ],
            'add_draft'          => [
                'name'     => 'Убрал запись в черновики',
                'source'   => 'wordpress',
                'callback' => 'una_get_add_draft',
                'access'   => 'logged',
            ],
            'delete_post'        => [
                'name'     => 'Удалил запись в корзину',
                'source'   => 'wordpress',
                'callback' => 'una_get_delete_post',
                'access'   => 'admin',
            ],
            'delete_post_fully'  => [
                'name'     => 'Удалил запись навсегда',
                'source'   => 'wordpress',
                'callback' => 'una_get_delete_post',
                'access'   => 'admin',
            ],
            'pass_reset_mail'    => [
                'name'     => 'Успешная отправка письма с ссылкой сброса пароля',
                'source'   => 'wordpress',
                'callback' => 'una_get_pass_reset_mail',
                'access'   => 'admin',
            ],
            'pass_reset_fail'    => [
                'name'     => 'Неверные попытки сброса пароля',
                'source'   => 'wordpress',
                'callback' => 'una_get_pass_reset_fail',
                'access'   => 'admin',
            ],
            'pass_reset_confirm' => [
                'name'     => 'Подтверждение изменения пароля через почту',
                'source'   => 'wordpress',
                'callback' => 'una_get_pass_reset_confirm',
                'access'   => 'author',
            ],
            'pass_change'        => [
                'name'     => 'Изменение пароля через ЛК',
                'source'   => 'wordpress',
                'callback' => 'una_get_pass_change',
                'access'   => 'author',
            ],
            'logged_in'          => [
                'name'     => 'Вошел на сайт',
                'source'   => 'wordpress',
                'callback' => 'una_get_logged_in_out',
            ],
            'logged_out'         => [
                'name'     => 'Вышел с сайта',
                'source'   => 'wordpress',
                'callback' => 'una_get_logged_in_out',
                'access'   => 'author',
            ],
            'profile_update'     => [
                'name'     => 'Обновил настройки профиля',
                'source'   => 'wordpress',
                'callback' => 'una_get_profile_update',
                'access'   => 'admin',
            ],
            'change_status'      => [
                'name'     => 'Сменил статус профиля',
                'source'   => 'wordpress',
                'callback' => 'una_get_change_user_status',
            ],
            'add_cover'          => [
                'name'     => 'Добавил обложку в ЛК',
                'source'   => 'wp-recall',
                'callback' => 'una_get_add_cover',
            ],
            'add_avatar'         => [
                'name'     => 'Добавил (сменил) аватарку',
                'source'   => 'wp-recall',
                'callback' => 'una_get_add_avatar',
            ],
            'del_avatar'         => [
                'name'     => 'Удалил свой аватар',
                'source'   => 'wp-recall',
                'callback' => 'una_get_del_avatar',
                'access'   => 'author',
            ],
            'delete_user'        => [
                'name'     => 'Удалил пользователя',
                'source'   => 'wordpress',
                'callback' => 'una_get_delete_user',
                'access'   => 'admin',
            ],
            'add_user_blacklist' => [
                'name'     => 'Добавил пользователя в чёрный список',
                'source'   => 'wp-recall',
                'callback' => 'una_get_add_user_blacklist',
                'access'   => 'logged',
            ],
            'del_user_blacklist' => [
                'name'     => 'Удалил пользователя из чёрного списка',
                'source'   => 'wp-recall',
                'callback' => 'una_get_del_user_blacklist',
                'access'   => 'logged',
            ],
        );

        // чтобы можно было зарегистрировать тип и коллбэк функцию
        $types = apply_filters( 'una_register_type', $type );

        return $types;
    }

}
