<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


// логин через вордпресс
add_action( 'set_auth_cookie', 'una_login', 20, 4 );
function una_login( $auth_cookie, $expire, $expiration, $user_id ) {
    $args['user_id']     = $user_id;
    $args['action']      = 'logged_in';
    $args['object_type'] = 'user';

    una_insert( $args );
}

// вышел с сайта
add_action( 'clear_auth_cookie', 'una_logout', 5 );
function una_logout() {
    $user = wp_get_current_user();

    $args['user_id']     = $user->ID;
    $args['action']      = 'logged_out';
    $args['object_type'] = 'user';

    una_insert( $args );
}

// фейковый логин через доп Fake Online
add_action( 'fknl_daily', 'una_fake_login', 10, 2 );
function una_fake_login( $user_id, $time ) {
    $args['user_id']     = $user_id;
    $args['act_date']    = $time;
    $args['action']      = 'logged_in';
    $args['object_type'] = 'user';

    una_insert( $args );
}

// регистрация пользователя
add_action( 'user_register', 'una_register', 10 );
function una_register( $user_id ) {
    // del in v0.56 - посмотрим, понаблюдаем
//    $date_time = current_time( 'mysql' );
//    $time      = new DateTime( $date_time );
//
//    $time->modify( '-1 second' ); // хак. Чтобы регистрация была раньше логина
//
//    $args['act_date']    = $time->format( 'Y-m-d H:i:s' );
    $args['action']      = 'register';
    $args['object_type'] = 'user';
    $args['user_id']     = $user_id;

    una_insert( $args );
}

// подтвердил регистрацию
add_action( 'rcl_confirm_registration', 'una_confirm_registration', 100 );
function una_confirm_registration( $user_id ) {
    $args['action']      = 'confirm_register';
    $args['object_type'] = 'user';
    $args['user_id']     = $user_id;

    una_insert( $args );
}

// deprecated
// неверный вход (логин или пароль)
/* function una_failed_login($username){
  $args['action'] = 'login_failed';
  $args['object_name'] = $username;
  $args['object_type'] = 'user';

  una_insert($args);
  }
  add_action('wp_login_failed', 'una_failed_login'); */


// неверная регистрация (имя или емейл уже используются)
add_filter( 'registration_errors', 'una_failed_registration', 10, 3 );
function una_failed_registration( $errors, $sanitized_user_login, $user_email ) {
    $reason = '';
    if ( isset( $errors->errors['username_exists'] ) && $errors->errors['username_exists'] ) { // логин такой есть
        $reason = 'name';
    }
    if ( isset( $errors->errors['email_exists'] ) && $errors->errors['email_exists'] ) {    // мейл такой уже есть
        $reason .= 'email';
    }
    if ( ! $reason )
        return $errors; // даже при регистрации есть объект $errors, но пустой или другая причина ошибки (капчи и прочие 3-и плагины)

    $args['object_name'] = $sanitized_user_login . ',' . $user_email;
    $args['action']      = 'register_failed';
    $args['other_info']  = $reason;
    $args['object_type'] = 'user';

    una_insert( $args );

    return $errors;
}

// при удалении юзера - запишем кто удалил его и очистим историю удаленного юзера
add_action( 'delete_user', 'una_delete_userdata_activity' );
function una_delete_userdata_activity( $user_id ) {
    $user = get_userdata( $user_id );
    if ( $user ) {
        $args['object_name'] = $user->get( 'display_name' );
        $args['other_info']  = $user->get( 'user_email' );
    }

    $args['action']      = 'delete_user';
    $args['subject_id']  = $user_id;
    $args['object_type'] = 'user';

    una_insert( $args );

    global $wpdb;
    $wpdb->query( $wpdb->prepare( "DELETE FROM " . UNA_DB . " WHERE user_id = '%d'", $user_id ) );
}

// Юзер обновил настройки профиля.
add_action( 'personal_options_update', 'una_update_profile' );
function una_update_profile( $user_id ) {
    $args['action']      = 'profile_update';
    $args['user_id']     = $user_id;
    $args['object_type'] = 'user';

    una_insert( $args );
}

// Ловим смену статуса юзера (description)
add_action( 'personal_options_update', 'una_change_user_status' );
function una_change_user_status( $user_id ) {
    // нет запроса
    if ( ! isset( $_POST['description'] ) )
        return false;

    // или пустой
    if ( empty( $_POST['description'] ) )
        return false;

    global $wpdb;
    $input_description      = sanitize_textarea_field( $_POST['description'] );
    $input_description_hash = wp_hash( $input_description );

    $current_hash = $wpdb->get_var( $wpdb->prepare( "SELECT other_info FROM " . UNA_DB . " WHERE action = 'change_status' AND user_id = %d ORDER BY act_date DESC", $user_id ) );

    // нет еще в событиях строки. Создадим его
    if ( empty( $current_hash ) ) {
        $args['action']      = 'change_status';
        $args['user_id']     = $user_id;
        $args['object_type'] = 'user';
        $args['other_info']  = $input_description_hash;

        una_insert( $args );
    } else {
        // данные равны с теми что пришли
        if ( $input_description_hash == $current_hash )
            return false;

        // обновим строку
        $wpdb->update(
            UNA_DB, array( 'other_info' => $input_description_hash, 'act_date' => current_time( 'mysql' ) ), array( 'user_id' => $user_id, 'action' => 'change_status' )
        );
    }
}

// оставил комментарий
add_action( 'wp_insert_comment', 'una_add_comment', 10, 2 );
function una_add_comment( $id, $comment ) {
    $group_data   = array();
    $has_parent   = array();
    $subject_name = array();
    $post_id      = $comment->comment_post_ID;
    $comm_parent  = $comment->comment_parent;

    // ответ на комментарий - другой массив данных
    if ( $comm_parent ) {
        $has_parent = array( 'par' => 1 ); // добавим в other_info ключ par=1
        $parent     = get_comment( $comm_parent );

        // комментатор не отвечает сам себе
        if ( $comment->user_id != $parent->user_id ) {
            $subject_name = array( 'sbj_nm' => $parent->comment_author );

            $args['subject_id'] = $parent->user_id;
        }
    }

    // если это не ответ на комментарий - то пишем к какой записи комментарий и какого автора
    else {
        $id_author_post = get_post_field( 'post_author', $post_id );

        if ( $comment->user_id != $id_author_post ) { // комментатор не автор записи
            $args['subject_id'] = $id_author_post;
        }
    }

    $post_type = get_post_type( $post_id );
    if ( $post_type == 'post-group' ) {
        $group = una_get_group_by_post( $post_id );
        if ( $group ) {
            $group_id   = $group->term_id;
            $group_name = $group->name;
            $group_data = array( 'grid' => $group_id, 'grn' => $group_name ); // массив данных группы

            $args['group_id'] = $group_id;
        }
    }

    $post_data = array( 'st' => $comment->comment_approved, 'pt' => $post_type ); // массив данных записи
    $in_other  = array_merge( $post_data, $group_data, $has_parent, $subject_name );

    $args['action']      = 'add_comment';
    $args['object_id']   = $id;
    $args['object_name'] = get_the_title( $post_id );
    $args['object_type'] = 'comment';
    $args['other_info']  = serialize( $in_other );

    una_insert( $args );
}

// комментарий одобряют/не одобряют, в спам, в корзину... Сменим в нашей таблице ему статус
add_action( 'transition_comment_status', 'una_approved_comment', 10, 3 );
function una_approved_comment( $new_status, $old_status, $comment ) {
    // стопим. ф-ция: una_delete_comment дальше работает.
    if ( $new_status == 'delete' )
        return false;

    global $wpdb;
    // получим значение колонки other_info:
    $status = $wpdb->get_var( $wpdb->prepare( "SELECT other_info FROM " . UNA_DB . " WHERE action = 'add_comment' AND object_type = 'comment' AND object_id = %d", $comment->comment_ID ) );

    $other = unserialize( $status );

    // нас интересует там статус
    if ( isset( $other['st'] ) ) {
        if ( $new_status == 'approved' ) {
            $new_status = 1;                            // чтобы у нас с функцией выше una_add_comment логика одна была
        } else if ( $new_status == 'unapproved' ) {
            $new_status = 0;
        }

        $other['st']    = $new_status;                  // заменим его на новый
        $serializedData = serialize( $other );          // упакуем

        $wpdb->update( UNA_DB, // и обновим данные в колонке
                       array( 'other_info' => $serializedData ), array( 'action' => 'add_comment', 'object_type' => 'comment', 'object_id' => $comment->comment_ID )
        );
    }
}

// если админ удалит камент - вычистить из таблицы
add_action( 'delete_comment', 'una_delete_comment' );
function una_delete_comment( $id ) {
    global $wpdb;

    $wpdb->query( $wpdb->prepare( "DELETE FROM " . UNA_DB . " WHERE (action = 'add_comment' OR action = 'reply_comment') AND object_id = '%d' ", $id ) );
}

/*
  Пока самая геморная ф-ция.
  Ловим событие записи - новая запись, убирание в черновик и т.д
  Если админ удаляет запись - то пишется как его действие по отношению к автору записи (subject)
  Если сам юзер удаляет запись - то действие принадлежит ему
  Полное удаление записи (из корзины) это ф-ция ниже
  Если админ одобряет запись - то действие публикации записи принадлежит автору записи, а не админу

 */
// Статусы записей: новая, черновик
add_action( 'transition_post_status', 'una_post_status', 10, 3 );
function una_post_status( $new_status, $old_status, $post ) {

    // что игнорим: автосохранение, или прикрепления или ревизии
    if ( $new_status == 'auto-draft' || ($old_status == 'new' && $new_status == 'inherit') )
        return false;

    // автосохранение первой записи в админке
    /* if($old_status == 'auto-draft' && $new_status == 'draft') return false; */

    // гонять из черновика в черновик не дадим
    if ( $old_status == 'draft' && $new_status == 'draft' )
        return false;

    // ревизии не нужны
    if ( wp_is_post_revision( $post->ID ) )
        return false;

    $exclude_post_type = [
        // меню - нам не надо
        'nav_menu_item',
        // кастомайзер - нам не надо
        'customize_changeset',
        // кеш оэмбеда - если в теле линк на ютуб например
        'oembed_cache',
        // кастомные стили
        'custom_css',
        // импортирован или создан гутенберг блок
        'wp_block',
        // доп video room - у него свой обработчик а integrations папке
        'video',
    ];

    $exclude_posts_type = apply_filters( 'una_exclude_post_types', $exclude_post_type );

    if ( in_array( $post->post_type, $exclude_posts_type ) )
        return false;

    $post_author = $post->post_author;

    // опубликовал новую запись
    if ( $old_status == 'new' || $old_status == 'auto-draft' && $new_status == 'draft' ) {
        if ( $post->post_date == $post->post_modified ) { // дата публикации и изменения должны совпадать
            $args['action'] = 'add_post';
        } else {
            return false;
        }
    }

    // сохранил опубликованную запись в черновик
    else if ( $old_status == 'publish' && $new_status == 'draft' || $old_status == 'pending' && $new_status == 'draft' ) {
        $args['action'] = 'add_draft';
    }

    // запись из черновика на модерацию
    else if ( $old_status == 'draft' && $new_status == 'pending' || $old_status == 'draft' && $new_status == 'publish' ) {
        global $wpdb;

        // добавим флаг pending и сменим на add_post
        $res = $wpdb->delete( UNA_DB, array( 'user_id' => $post_author, 'object_id' => $post->ID, 'action' => 'add_draft' ) );

        if ( isset( $res ) ) { // ответ 0 строк или > 0
            return true; // все прошло хорошо - обновили данные. Остановим скрипт
        }
    } else if ( $new_status == 'trash' ) { // удалил
        $args['action'] = 'delete_post';
    } else {
        return false; // любые другие перемены нам не нужны
    }

    $args['user_id'] = $post_author;

    global $user_ID;

    // если действие не автора записи
    if ( $user_ID != $post_author ) {
        if ( $args['action'] == 'delete_post' ) { // запись удаляет не автор записи (а например админ сайта)
            $args['user_id']    = $user_ID;
            $args['subject_id'] = $post_author;
            $args['other_info'] = una_get_username( $post_author );
        } else { // все другие действия (например одобрил запись)
            $args['other_info'] = $user_ID;
        }
    }

    if ( $post->post_type === 'post-group' ) {
        $term_id = '';
        if ( isset( $_POST['term_id'] ) ) {
            $term_id = intval( base64_decode( $_POST['term_id'] ) );
        } else {
            $group   = una_get_group_by_post( $post->ID );
            $term_id = $group->term_id;
        }

        if ( ! empty( $term_id ) ) {
            $args['group_id'] = $term_id;
        }
    }

    $args['object_id'] = $post->ID;
    if ( $post->post_type === 'notes' ) { // если это заметка то получаем ее id (т.к. у всех заметок заголовок "Заметка")
        $args['object_name'] = una_separate_id_notes( $post->post_name );
    } else {
        $args['object_name'] = $post->post_title;
    }
    $args['object_type'] = $post->post_type;

    una_insert( $args );
}

// удаляем запись навсегда (запись удаляется из корзины или если корзина отключена, т.е. когда запись удаляется безвозвратно)
add_action( 'before_delete_post', 'una_delete_post' );
function una_delete_post( $postid ) {
    $post_type = get_post_field( 'post_type', $postid );

    if ( $post_type === 'revision' || $post_type === 'video' || $post_type === 'customize_changeset' )
        return false; // ревизии, видео из галереи и кастомайзер мы не фиксируем

    global $user_ID;

    if ( $post_type === 'post-group' ) {
        $group = una_get_group_by_post( $postid );
        if ( $group ) {
            $args['group_id'] = $group->term_id;
        }
    }

    $args['user_id'] = $user_ID;
    if ( defined( 'DOING_CRON' ) && DOING_CRON && ! $user_ID ) { // это крон очистка
        $args['user_id'] = -1;
    }
    $args['action']      = 'delete_post_fully';
    $args['object_id']   = $postid;
    $args['object_name'] = get_the_title( $postid );
    $args['object_type'] = $post_type;

    $post_author = get_post_field( 'post_author', $postid );

    if ( $user_ID != $post_author ) { // не сам автор удаляет запись, а скорее всего редакторы или админ
        $args['subject_id'] = $post_author;
        $args['other_info'] = una_get_username( $post_author );
    }

    una_insert( $args );
}

// если админ удаляет запись окончательно - вычистить из таблицы всю историю по этому id записи
add_action( 'before_delete_post', 'una_delete_post_in_table' );
function una_delete_post_in_table( $postid ) {
    global $wpdb;

    $post_type = get_post_field( 'post_type', $postid );

    $wpdb->query( $wpdb->prepare( "DELETE FROM " . UNA_DB . " WHERE object_type = '%s' AND object_id = '%d' AND action != 'delete_post_fully' ", $post_type, $postid ) );
}

// Добавим юзера в черный список
add_action( 'add_user_blacklist', 'una_add_user_blacklist' );
function una_add_user_blacklist( $subject_id ) {
    global $wpdb, $user_ID;

    $res = $wpdb->update( UNA_DB, // обновим строку
                          array( 'act_date' => current_time( 'mysql' ) ), array( 'user_id' => $user_ID, 'action' => 'add_user_blacklist', 'subject_id' => $subject_id )
    );
    if ( $res > 0 ) { // были обновлены строки
        $wpdb->query( $wpdb->prepare( "DELETE FROM " . UNA_DB . " WHERE action = 'del_user_blacklist' AND user_id = '%d' AND subject_id = '%d'", $user_ID, $subject_id ) );
    } else { // нет у нас такой строки - значит создадим
        $args['user_id']     = $user_ID;
        $args['action']      = 'add_user_blacklist';
        $args['subject_id']  = $subject_id;
        $args['object_name'] = una_get_username( $subject_id );
        $args['object_type'] = 'user';

        una_insert( $args );
    }
}

// убрал из чёрного списка
add_action( 'remove_user_blacklist', 'una_del_user_blacklist' );
function una_del_user_blacklist( $subject_id ) {
    global $user_ID;

    $args['user_id']     = $user_ID;
    $args['action']      = 'del_user_blacklist';
    $args['subject_id']  = $subject_id;
    $args['object_name'] = una_get_username( $subject_id );
    $args['object_type'] = 'user';

    una_insert( $args );
}

// добавил обложку
add_action( 'rcl_cover_upload', 'una_add_cover', 10 );
function una_add_cover() {
    global $wpdb, $user_ID;

    $cover = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM " . UNA_DB . " WHERE action = 'add_cover' AND object_type = 'user' AND user_id = %d", $user_ID ) );
    if ( $cover ) { // это значит что обложка уже зафиксирована системой
        $wpdb->update( UNA_DB, // обновим дату
                       array( 'act_date' => current_time( 'mysql' ) ), array( 'action' => 'add_cover', 'object_type' => 'user', 'user_id' => $user_ID )
        );
    } else {
        $args['user_id']     = $user_ID;
        $args['action']      = 'add_cover';
        $args['object_type'] = 'user';

        una_insert( $args );
    }
}

// добавил аватарку
add_action( 'rcl_avatar_upload', 'una_add_avatar', 10 );
function una_add_avatar() {
    global $wpdb, $user_ID;

    $ava = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM " . UNA_DB . " WHERE action = 'add_avatar' AND object_type = 'user' AND user_id = %d", $user_ID ) );
    if ( $ava ) { // это значит что ава уже зафиксирована системой
        $wpdb->update( UNA_DB, // обновим дату
                       array( 'act_date' => current_time( 'mysql' ) ), array( 'action' => 'add_avatar', 'object_type' => 'user', 'user_id' => $user_ID )
        );
    } else {
        $args['user_id']     = $user_ID;
        $args['action']      = 'add_avatar';
        $args['object_type'] = 'user';

        una_insert( $args );
    }
}

// удалил аватарку
add_action( 'rcl_delete_avatar', 'una_del_avatar', 10 );
function una_del_avatar() {
    global $wpdb, $user_ID;

    $id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM " . UNA_DB . " WHERE action = 'add_avatar' AND object_type = 'user' AND user_id = %d ORDER BY act_date DESC", $user_ID ) );
    // есть строка
    if ( $id ) {
        // удалим строку по id
        $wpdb->delete( UNA_DB, array( 'id' => $id ) );
        // удалим строку по id
        $wpdb->delete( UNA_DB, array( 'action' => 'del_avatar', 'object_type' => 'user', 'user_id' => $user_ID ) );
    }

    $args['user_id']     = $user_ID;
    $args['action']      = 'del_avatar';
    $args['object_type'] = 'user';

    una_insert( $args );
}

/*
 *
 * Действия с паролями
 *
 */

// успешная отправка письма с ссылкой сброса пароля
add_filter( 'retrieve_password_message', 'una_pass_reset_on_mail', 10, 4 );
function una_pass_reset_on_mail( $message, $key, $user_login, $user_data ) {
    $args['action']      = 'pass_reset_mail';
    $args['object_type'] = 'user';
    $args['subject_id']  = $user_data->data->ID;

    una_insert( $args );

    // фильтр возвращаем
    return $message;
}

// неверные попытки сброса пароля
add_action( 'lostpassword_post', 'una_pass_reset' );
function una_pass_reset( $errors ) {
    // нет запроса
    if ( ! isset( $_POST['user_login'] ) )
        return;

    $in_form = trim( $_POST['user_login'] );

    $args['action']      = 'pass_reset_fail';
    $args['object_type'] = 'user';

    // wp вернул ошибку
    if ( $errors->has_errors() ) {
        $email = sanitize_email( $in_form );

        // ml as mail
        $other = [ 'ml' => $email ];

        $args['other_info'] = serialize( $other );

        una_insert( $args );
    }
    // или проверим на логин
    else {
        $user_data = get_user_by( 'login', $in_form );

        if ( ! $user_data ) {
            $user = sanitize_user( $in_form );

            // nm as name
            $other = [ 'nm' => $user ];

            $args['other_info'] = serialize( $other );

            una_insert( $args );
        }
    }

    return;
}

// Подтвердил изменение пароля через почту
add_action( 'after_password_reset', 'una_change_pass_confirm' );
function una_change_pass_confirm( $user ) {
    $args['user_id']     = $user->data->ID;
    $args['action']      = 'pass_reset_confirm';
    $args['object_type'] = 'user';

    una_insert( $args );
}

// Изменил пароль через ЛК
add_filter( 'password_change_email', 'una_pass_change', 10 );
function una_pass_change( $pass_change_email ) {
    $args['action']      = 'pass_change';
    $args['object_type'] = 'user';

    una_insert( $args );

    // фильтр возвращаем
    return $pass_change_email;
}

//////////////////////////////// END ////////////////////////////////