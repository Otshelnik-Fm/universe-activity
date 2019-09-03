<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


// логин через вордпресс
add_action( 'wp_login', 'una_login', 10, 2 );
function una_login( $user_login, $user ) {
    $args['user_id']     = $user->data->ID;
    $args['action']      = 'logged_in';
    $args['object_type'] = 'user';

    una_insert( $args );
}

// логин через uLogin (хук сработает если только есть uLogin)
add_action( 'ulogin_enter_user', 'una_login_ulogin', 999 );
function una_login_ulogin( $user_id ) {
    global $wpdb;

    $network = $wpdb->get_var( $wpdb->prepare( "SELECT network FROM " . $wpdb->prefix . "ulogin WHERE userid = %d", $user_id ) );
    if ( $network ) {
        $args['other_info'] = $network;
    }

    $args['action']      = 'logged_in_ulogin';
    $args['object_type'] = 'user';

    una_insert( $args );
}

// регистрация пользователя
add_action( 'user_register', 'una_register', 10 );
function una_register( $user_id ) {
    $date_time = current_time( 'mysql' );
    $time      = new DateTime( $date_time );

    $time->modify( '-1 second' ); // хак. Чтобы регистрация была раньше логина

    $args['act_date']    = $time->format( 'Y-m-d H:i:s' );
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

// выход с сайта
add_action( 'wp_logout', 'una_logout' );
function una_logout() {
    $args['action']      = 'logged_out';
    $args['object_type'] = 'user';

    una_insert( $args );
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

// Поставил рейтинг
add_action( 'rcl_insert_rating', 'una_give_rating' );
function una_give_rating( $data ) {
    // рейтинг за отзыв не фиксируем
    if ( $data['rating_type'] == 'review-content' )
        return false;

    // рейтинг за smart-comment игнорим
    if ( $data['rating_type'] == 'smart-comment' )
        return false;

    // обрабатываемые записи для вывода заголовка
    $types = array( 'post', 'products', 'post-group', 'notes' );

    if ( in_array( $data['rating_type'], $types ) ) {
        $args['object_name'] = get_the_title( $data['object_id'] );
    }

    // рейтинг за запись групп
    if ( $data['rating_type'] == 'post-group' ) {
        $group = una_get_group_by_post( $data['object_id'] );
        if ( $group ) {
            $args['group_id'] = $group->term_id;
        }
    }

    $p_id = [];
    // рейтинг за комментарий групп
    if ( $data['rating_type'] == 'comment' ) {
        $comment = get_comment( $data['object_id'] );

        $group = una_get_group_by_post( $comment->comment_post_ID );
        if ( $group ) {
            $p_id             = [ $comment->comment_post_ID ];
            $args['group_id'] = $group->term_id;
        }
    }

    // рейтинг за комментарий на prime forum
    if ( $data['rating_type'] == 'forum-post' ) {
        $post  = pfm_get_post( $data['object_id'] );
        $topic = pfm_get_topic( $post->topic_id );

        $args['object_name'] = $topic->topic_name;
    }

    // рейтинг за комментарий на Asgaros forum
    if ( $data['rating_type'] == 'forum-page' ) {
        global $wpdb;
        $topic_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM " . $wpdb->prefix . "forum_posts AS fp LEFT JOIN " . $wpdb->prefix . "forum_topics AS ft ON(fp.parent_id = ft.id) WHERE fp.id = %d ORDER BY date ASC", $data['object_id'] ) );

        $args['object_name'] = $topic_name;
    }

    $current_user = wp_get_current_user();

    $other = array( // какой оценкой
        $current_user->display_name,
        $data['rating_status'],
        $data['rating_value'],
    );

    if ( isset( $p_id ) ) {
        $other = array_merge( $other, $p_id );
    }

    $args['object_type'] = $data['rating_type'];
    $args['user_id']     = $data['user_id'];                        // кто
    $args['action']      = 'give_rating_' . $data['rating_type'];   // проголосавал за тип
    $args['object_id']   = $data['object_id'];                      // за id записи
    $args['subject_id']  = $data['object_author'];                  // id автора кому поставил
    $args['other_info']  = serialize( $other );

    una_insert( $args );
}

// снял рейтинг - удалим тогда из истории
add_action( 'rcl_delete_rating', 'una_delete_rating' );
function una_delete_rating( $data ) {
    global $wpdb;

    $wpdb->query( $wpdb->prepare( "DELETE FROM " . UNA_DB . " WHERE user_id = '%d' AND action = '%s' AND object_id = '%d' ", $data['user_id'], "give_rating_" . $data['rating_type'], $data['object_id'] ) );
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

//    // меню нам нафиг не надо
//    if ( $post->post_type === 'nav_menu_item' )
//        return false;
//
//    // а это кастомайзер - нам нафиг не надо
//    if ( $post->post_type === 'customize_changeset' )
//        return false;
//
//    // кеш оэмбеда - если в теле линк на ютуб например
//    if ( $post->post_type === 'oembed_cache' )
//        return false;
//
//    // кастомные стили
//    if ( $post->post_type === 'custom_css' )
//        return false;
//
//    // Импортирован или создан гутенберг блок
//    if ( $post->post_type === 'wp_block' )
//        return false;

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
            $user_info = get_userdata( $post_author );

            $args['user_id']    = $user_ID;
            $args['subject_id'] = $post_author;
            $args['other_info'] = $user_info->display_name;
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
        $user_info = get_userdata( $post_author );

        $args['subject_id'] = $post_author;
        $args['other_info'] = $user_info->display_name;
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

// подписка на юзера (доп FEED)
add_action( 'rcl_insert_feed_data', 'una_add_user_feed', 10, 2 );
function una_add_user_feed( $feed_id, $argums ) {
    global $wpdb;

    $res = $wpdb->update( UNA_DB, // обновим строку
                          array( 'act_date' => current_time( 'mysql' ) ), array( 'user_id' => $argums['user_id'], 'action' => 'add_user_feed', 'subject_id' => $argums['object_id'] )
    );
    if ( $res > 0 ) { // были обновлены строки
        $wpdb->query( $wpdb->prepare( "DELETE FROM " . UNA_DB . " WHERE action = 'del_user_feed' AND user_id = '%d' AND subject_id = '%d'", $argums['user_id'], $argums['object_id'] ) );
    } else {
        $userdata = get_userdata( $argums['object_id'] );

        $args['user_id']     = $argums['user_id'];
        $args['action']      = 'add_user_feed';
        $args['subject_id']  = $argums['object_id'];
        $args['object_name'] = $userdata->display_name;
        $args['object_type'] = 'user';

        una_insert( $args );
    }
}

// отписался от юзера (доп FEED)
add_action( 'rcl_pre_remove_feed', 'una_del_user_feed' );
function una_del_user_feed( $feed ) {
    $userdata = get_userdata( $feed->object_id );

    $args['user_id']     = $feed->user_id;
    $args['action']      = 'del_user_feed';
    $args['subject_id']  = $feed->object_id;
    $args['object_name'] = $userdata->display_name;
    $args['object_type'] = 'user';

    una_insert( $args );
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
        $userdata = get_userdata( $subject_id );

        $args['user_id']     = $user_ID;
        $args['action']      = 'add_user_blacklist';
        $args['subject_id']  = $subject_id;
        $args['object_name'] = $userdata->display_name;
        $args['object_type'] = 'user';

        una_insert( $args );
    }
}

// отписался от юзера
add_action( 'remove_user_blacklist', 'una_del_user_blacklist' );
function una_del_user_blacklist( $subject_id ) {
    global $user_ID;

    $userdata = get_userdata( $subject_id );

    $args['user_id']     = $user_ID;
    $args['action']      = 'del_user_blacklist';
    $args['subject_id']  = $subject_id;
    $args['object_name'] = $userdata->display_name;
    $args['object_type'] = 'user';

    una_insert( $args );
}

// создал группу
add_action( 'rcl_create_group', 'una_create_group' );
function una_create_group( $term_id ) {
    global $user_ID;

    $term = get_term( $term_id, 'groups' );

    $args['user_id']     = $user_ID;
    $args['action']      = 'create_group';
    $args['object_id']   = $term_id;
    $args['object_name'] = $term->name;
    $args['object_type'] = 'group';
    $args['group_id']    = $term_id;

    una_insert( $args );
}

// из админки удаление группы
add_action( 'rcl_pre_delete_group', 'una_delete_group' );
function una_delete_group( $term_id ) {
    global $wpdb, $user_ID;

    $group_name = $wpdb->get_var( $wpdb->prepare( "SELECT object_name FROM " . UNA_DB . " WHERE action = 'create_group' AND object_type = 'group' AND object_id = %d", $term_id ) );
    if ( $group_name ) { // это значит создание группы было зафиксированно системой
        $args['object_name'] = $group_name;

        // и поставим маркер что группа была удалена:
        $wpdb->update( UNA_DB, // обновим строку
                       array( 'other_info' => 'del' ), array( 'action' => 'create_group', 'object_type' => 'group', 'object_id' => $term_id )
        );
    }

    $admin_group = $wpdb->get_var( $wpdb->prepare( "SELECT admin_id FROM " . RCL_PREF . "groups WHERE ID = %d", $term_id ) );

    $userdata = get_userdata( $admin_group );

    $args['user_id']     = $user_ID;
    $args['action']      = 'delete_group';
    $args['object_id']   = $term_id;
    $args['object_type'] = 'group';
    $args['subject_id']  = $admin_group;
    $args['other_info']  = $userdata->display_name;

    una_insert( $args );
}

// вступил в группу
add_action( 'rcl_group_add_user', 'una_user_in_group' );
function una_user_in_group( $argums ) {
//	global $wpdb;

    $term = get_term( $argums['group_id'], 'groups' );
//	$admin_group = $wpdb->get_var( $wpdb->prepare( "SELECT admin_id FROM " . RCL_PREF . "groups WHERE ID = %d", $argums['group_id'] ) );
//	$userdata	 = get_userdata( $admin_group );

    $args['action']      = 'user_in_group';
    $args['object_id']   = $argums['group_id'];
    $args['object_name'] = $term->name;
    $args['object_type'] = 'group';
//	$args['subject_id']	 = $admin_group;
//	$args['other_info']	 = $userdata->display_name;
    $args['group_id']    = $argums['group_id'];

    una_insert( $args );
}

// покинул группу/удалили из группы
add_action( 'rcl_group_remove_user', 'una_user_out_group' );
function una_user_out_group( $argums ) {
    $term = get_term( $argums['group_id'], 'groups' );

    //$_POST['group-action'] == 'leave'; // значит сам вышел из группы. Нет если - значит админ выкинул
    if ( ! isset( $_POST['group-action'] ) || $_POST['group-action'] !== 'leave' ) {
        $args['subject_id'] = $argums['user_id'];
        $args['other_info'] = 'kick';
    }

    $args['action']      = 'user_out_group';
    $args['object_id']   = $argums['group_id'];
    $args['object_name'] = $term->name;
    $args['object_type'] = 'group';
    $args['group_id']    = $argums['group_id'];

    una_insert( $args );
}

// добавил/изменил описание группы
add_action( 'rcl_update_group', 'una_set_description_group' );
function una_set_description_group( $data ) {
    if ( empty( $data['description'] ) )
        return false;

    $input_description      = sanitize_textarea_field( $data['description'] );
    $input_description_hash = wp_hash( $input_description );

    $sql          = "SELECT other_info FROM " . UNA_DB . " WHERE action = 'group_change_desc' AND group_id = %d ORDER BY act_date DESC";
    $current_hash = una_get_var( $sql, $data['group_id'] );

    // нет еще в событиях строки. Создадим его
    if ( empty( $current_hash ) ) {
        $args['action']      = 'group_change_desc';
        $args['object_id']   = $data['group_id'];
        $args['object_name'] = $data['name'];
        $args['object_type'] = 'group';
        $args['group_id']    = $data['group_id'];
        $args['other_info']  = $input_description_hash;

        una_insert( $args );
    } else {
        // данные равны с теми что пришли
        if ( $input_description_hash == $current_hash )
            return false;

        $new_data = array( 'other_info' => $input_description_hash, 'object_name' => $data['name'], 'act_date' => current_time( 'mysql' ) );
        $where    = array( 'action' => 'group_change_desc', 'group_id' => $data['group_id'] );
        una_update( $new_data, $where );
    }
}

// статус группы: открыта/закрыта
add_action( 'rcl_update_group', 'una_group_closed_opened' );
function una_group_closed_opened( $data ) {
    $sql    = "SELECT other_info FROM " . UNA_DB . " WHERE action = 'group_is_closed' AND group_id = %d ORDER BY act_date DESC";
    $status = una_get_var( $sql, $data['group_id'] );

    // нет еще данных, запишем
    if ( empty( $status ) ) {
        $args['action']      = 'group_is_closed';
        $args['object_id']   = $data['group_id'];
        $args['object_name'] = $data['name'];
        $args['object_type'] = 'group';
        $args['other_info']  = $data['status'];
        $args['group_id']    = $data['group_id'];

        una_insert( $args );
    }

    // есть, но статус сменился. обновим
    else if ( $data['status'] !== $status ) {
        $new_data = array( 'other_info' => $data['status'], 'object_name' => $data['name'], 'act_date' => current_time( 'mysql' ) );
        $where    = array( 'action' => 'group_is_closed', 'group_id' => $data['group_id'] );
        una_update( $new_data, $where );
    }
}

// сменили юзеру в группе роль/забанили в группе
add_action( 'rcl_update_group_user_role', 'una_group_change_user_role' );
function una_group_change_user_role( $data ) {
    $term     = get_term( $data['group_id'], 'groups' );
    $userdata = get_userdata( $data['user_id'] );

    $in_other = array( 'un' => $userdata->display_name, 'ur' => $data['user_role'] ); // массив данных записи

    $action = ('banned' === $data['user_role']) ? 'group_user_ban' : 'group_user_role';

    $args['action']      = $action;
    $args['object_id']   = $data['group_id'];
    $args['object_name'] = $term->name;
    $args['object_type'] = 'group';
    $args['subject_id']  = $data['user_id'];
    $args['other_info']  = serialize( $in_other );
    $args['group_id']    = $data['group_id'];

    una_insert( $args );
}

// создал тему на primeForum
add_action( 'pfm_add_topic', 'una_user_add_topic', 10, 2 );
function una_user_add_topic( $topic_id, $argums ) {

    $args['action']      = 'pfm_add_topic';
    $args['object_id']   = $topic_id;
    $args['object_name'] = $argums['topic_name'];
    $args['object_type'] = 'prime_forum';

    una_insert( $args );
}

// удалил тему на primeForum
add_action( 'pfm_pre_delete_topic', 'una_user_del_topic' );
function una_user_del_topic( $topic_id ) {
    global $wpdb, $user_ID;

    $topic_name = $wpdb->get_var( $wpdb->prepare( "SELECT object_name FROM " . UNA_DB . " WHERE action = 'pfm_add_topic' AND object_type = 'prime_forum' AND object_id = %d", $topic_id ) );
    if ( $topic_name ) { // это значит создание топика было зафиксированно системой
        $args['object_name'] = $topic_name;
        // и поставим маркер что топик был удален:
        $wpdb->update( UNA_DB, // обновим строку
                       array( 'other_info' => 'del' ), array( 'action' => 'pfm_add_topic', 'object_type' => 'prime_forum', 'object_id' => $topic_id )
        );
    } else { // если топик не найден в системе - запрашиваю из форума его название
        $args['object_name'] = pfm_get_topic_name( $topic_id );
    }

    $topic_user_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM " . RCL_PREF . "pforum_topics WHERE topic_id = %d", $topic_id ) );

    if ( $topic_user_id != $user_ID ) { // если удаляет топик не его автор
        $userdata = get_userdata( $topic_user_id );

        $args['subject_id'] = $topic_user_id;
        $args['other_info'] = $userdata->display_name;
    }

    $args['action']      = 'pfm_del_topic';
    $args['object_id']   = $topic_id;
    $args['object_type'] = 'prime_forum';

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
