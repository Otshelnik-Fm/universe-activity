<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// логин через вордпресс
function una_login($user_login, $user){
    $args['user_id'] = $user->data->ID;
    $args['action'] = 'logged_in';
    $args['object_type'] = 'user';
    
    una_insert($args);
}
add_action('wp_login', 'una_login', 10, 2 );


// логин через uLogin (хук сработает если только есть uLogin)
function una_login_ulogin($user_id){
    global $wpdb;
    $network = $wpdb->get_var($wpdb->prepare("SELECT network FROM ".$wpdb->prefix."ulogin WHERE userid = %d",$user_id));
    if($network){
        $args['other_info'] = $network;
    }

    $args['action'] = 'logged_in_ulogin';
    $args['object_type'] = 'user';
    
    una_insert($args);
}
add_action('ulogin_enter_user', 'una_login_ulogin', 999);


// регистрация пользователя
function una_register($user_id){
    $date_time = current_time('mysql');
    $time = new DateTime($date_time);
    $time->modify('-1 second'); // хак. Чтобы регистрация была раньше логина
    $args['act_date'] = $time->format('Y-m-d H:i:s');
    
    $args['action'] = 'register';
    $args['object_type'] = 'user';
    $args['user_id'] = $user_id;
    
    una_insert($args);
}
add_action('user_register', 'una_register', 10);


// подтвердил регистрацию
function una_confirm_registration($user_id){
    $args['action'] = 'confirm_register';
    $args['object_type'] = 'user';
    $args['user_id'] = $user_id;
    
    una_insert($args);
}
add_action('rcl_confirm_registration','una_confirm_registration',100);


// неверный вход (логин или пароль)
/* function una_failed_login($username){
    $args['action'] = 'login_failed';
    $args['object_name'] = $username;
    $args['object_type'] = 'user';
    
    una_insert($args);
}
add_action('wp_login_failed', 'una_failed_login'); */


// неверная регистрация (имя или емейл уже используются)
function una_failed_registration($errors, $sanitized_user_login, $user_email){
    $reason = '';
    if($errors->errors['username_exists']){ // логин такой есть
        $reason = 'name';
    }
    if($errors->errors['email_exists']){    // мейл такой уже есть
        $reason .= 'email';
    }
    if(!$reason) return false; // даже при регистрации есть объект $errors, но пустой
    
    $args['object_name'] = $sanitized_user_login.','.$user_email;
    $args['action'] = 'register_failed';
    $args['other_info'] = $reason;
    $args['object_type'] = 'user';

    una_insert($args);
    
    return $errors;
}
add_filter( 'registration_errors', 'una_failed_registration', 10, 3);


// выход с сайта
function una_logout() {
    $args['action'] = 'logged_out';
    
    $current_user = wp_get_current_user();
    $args['object_type'] = 'user';
    
    una_insert($args);
}
add_action('wp_logout', 'una_logout');


// при удалении юзера - запишем кто удалил его и очистим историю удаленного юзера
function una_delete_userdata_activity($user_id){
    $args['action'] = 'delete_user';
    $args['subject_id'] = $user_id;
    
    $user = get_userdata($user_id);
    if($user){
        $args['object_name'] = $user->get('display_name');
        $args['other_info'] = $user->get('user_email');
    }
    $args['object_type'] = 'user';
    
    una_insert($args);
    
    global $wpdb;
    $wpdb->query($wpdb->prepare("DELETE FROM ".$wpdb->prefix."otfm_universe_activity WHERE user_id = '%d'",$user_id));
}
add_action('delete_user', 'una_delete_userdata_activity');


// Юзер обновил настройки профиля.
function una_update_profile($user_id){
    $args['action'] = 'profile_update';
    $args['user_id'] = $user_id;
    $args['object_type'] = 'user';
    
    una_insert($args);
}
add_action('personal_options_update', 'una_update_profile');
//add_action('profile_update', 'una_update_profile'); // не использую - срабатывает и когда юзер регается.


// Ловим смену статуса юзера (description)
function una_change_user_status($user_id){
    if(!isset($_POST['description'])) return false; // нет запроса
    if(empty($_POST['description'])) return false;  // или пустой

    global $wpdb;
    $input_description = sanitize_textarea_field($_POST['description']);
    $input_description_hash = wp_hash($input_description);

    $table = $wpdb->prefix.'otfm_universe_activity';
    $current_hash = $wpdb->get_var($wpdb->prepare("SELECT other_info FROM $table WHERE action = 'change_status' AND user_id = %d ORDER BY act_date DESC", $user_id));
    
    if(empty($current_hash)){ // нет еще в событиях строки. Создадим его
        $args['action'] = 'change_status';
        $args['user_id'] = $user_id;
        $args['object_type'] = 'user';
        $args['other_info'] = $input_description_hash;
        
        una_insert($args);
    } else { // есть в событиях
        if($input_description_hash == $current_hash) return false; // данные равны с теми что пришли
        
        $res = $wpdb->update($table, // обновим строку
                    array('other_info' => $input_description_hash, 'act_date' => current_time('mysql')),
                    array('user_id' => $user_id, 'action' => 'change_status')
                );
    }
}
add_action('personal_options_update', 'una_change_user_status');



// Поставил рейтинг
function una_give_rating($data){
    if($data['rating_type'] == 'review-content') return false; // рейтинг за отзыв не фиксируем
    if($data['rating_type'] == 'smart-comment') return false; // рейтинг за smart-comment игнорим
    
    $current_user = wp_get_current_user();
    
    $types = array('post','products','post-group','notes'); // обрабатываемые записи для вывода заголовка
    if(in_array($data['rating_type'], $types)){
        $args['object_name'] = get_the_title($data['object_id']);
    }
    $args['object_type'] = $data['rating_type'];

    $args['user_id'] = $data['user_id'];                    // кто
    $args['action'] = 'give_rating_'.$data['rating_type'];  // проголосавал за тип
    $args['object_id'] = $data['object_id'];                // за id записи
    $args['subject_id'] = $data['object_author'];           // id автора кому поставил
    $other = array(                                         // какой оценкой
                $current_user->display_name,
                $data['rating_status'],
                $data['rating_value']
            );
    $args['other_info'] = serialize($other);
    
    una_insert($args);
}
add_action('rcl_insert_rating', 'una_give_rating');





// снял рейтинг - удалим тогда из истории
function una_delete_rating($data){
    global $wpdb;
    
    $table = $wpdb->prefix.'otfm_universe_activity';
    $wpdb->query($wpdb->prepare("DELETE FROM ".$table." WHERE user_id = '%d' AND action = '%s' AND object_id = '%d' ",
                                $data['user_id'], "give_rating_".$data['rating_type'], $data['object_id']));
}
add_action('rcl_delete_rating', 'una_delete_rating');



// оставил комментарий
function una_add_comment($id, $comment){
    $group_data = array();
    $has_parent = array();
    $subject_name = array();
    $post_id = $comment->comment_post_ID;
    $comm_parent = $comment->comment_parent; // ответ на камент
    
    if($comm_parent){ // ответ на комментарий - другой массив данных
        $has_parent = array('par' => 1); // добавим в other_info ключ par=1
        $parent = get_comment($comm_parent);
        if($comment->user_id != $parent->user_id){ // комментатор не отвечает сам себе
            $subject_name = array('sbj_nm' => $parent->comment_author);
            $args['subject_id'] = $parent->user_id;
        }
    } 
    else { // если это не ответ на комментарий - то пишем к какой записи комментарий и какого автора
        $id_author_post = get_post_field('post_author', $post_id);
        
        if($comment->user_id != $id_author_post){ // комментатор не автор записи
            $args['subject_id'] = $id_author_post;
        }
    }


    $post_type = get_post_type($post_id);
    if($post_type == 'post-group'){
        $group = rcl_get_group_by_post($post_id);
        $group_id = $group->term_id;
        $group_name = $group->name;
        $group_data = array('grid' => $group_id, 'grn' => $group_name); // массив данных группы
    }
    
    $post_data = array('st' => $comment->comment_approved, 'pt' => $post_type); // массив данных записи
    $in_other = array_merge($post_data, $group_data, $has_parent, $subject_name);
    
    $args['action'] = 'add_comment';
    $args['object_id'] = $id;
    $args['object_name'] = get_the_title($post_id);
    $args['object_type'] = 'comment';
    $args['other_info'] = serialize($in_other);
    
    una_insert($args);
}
add_action('wp_insert_comment', 'una_add_comment', 10, 2);


// комментарий одобряют/не одобряют, в спам, в корзину... Сменим в нашей таблице ему статус
function una_approved_comment($new_status, $old_status, $comment){

    if($new_status == 'delete') return false; // стопим. ф-ция: una_delete_comment дальше работает.

    global $wpdb;
    $table = $wpdb->prefix.'otfm_universe_activity'; // получим значение колонки other_info:
    $status = $wpdb->get_var($wpdb->prepare("SELECT other_info FROM $table WHERE action = 'add_comment' AND object_type = 'comment' AND object_id = %d", $comment->comment_ID));

    $other = unserialize($status);

    if( isset($other['st']) ){ // нас интересует там статус
        if($new_status == 'approved') $new_status = 1;  // чтобы у нас с функцией выше una_add_comment логика одна была
        else if($new_status == 'unapproved') $new_status = 0;
        $other['st'] = $new_status;                     // заменим его на новый
        $serializedData = serialize($other);            // упакуем

        $wpdb->update($table,                           // и обновим данные в колонке
                    array('other_info' => $serializedData),
                    array('action' => 'add_comment', 'object_type' => 'comment', 'object_id' => $comment->comment_ID)
                );
    }
}
add_action('transition_comment_status', 'una_approved_comment', 10, 3);


// если админ удалит камент - вычистить из таблицы
function una_delete_comment($id){
    global $wpdb;
    $table = $wpdb->prefix.'otfm_universe_activity';
    $wpdb->query($wpdb->prepare("DELETE FROM ".$table." WHERE (action = 'add_comment' OR action = 'reply_comment') AND object_id = '%d' ", $id));
}
add_action('delete_comment', 'una_delete_comment');




/*
    Пока самая геморная ф-ция.
    Ловим событие записи - новая запись, убирание в черновик и т.д
    Если админ удаляет запись - то пишется как его действие по отношению к автору записи (subject)
    Если сам юзер удаляет запись - то действие принадлежит ему
    Полное удаление записи (из корзины) это ф-ция ниже
    Если админ одобряет запись - то действие публикации записи принадлежит автору записи, а не админу

*/
// Статусы записей: новая, черновик
function una_post_status($new_status, $old_status, $post){
vdl('o '.$old_status);
vdl('n '.$new_status);

vdl('ps '.$post->post_type);

     // что игнорим: автосохранение, или прикрепления или ревизии
    if($new_status == 'auto-draft'
        || ($old_status ==  'new' && $new_status == 'inherit')
       ) return false;
    //if($old_status == 'auto-draft' && $new_status == 'draft') return false;     // автосохранение первой записи в админке
    if($old_status == 'draft' && $new_status == 'draft') return false;          // гонять из черновика в черновик не дадим
    if(wp_is_post_revision($post->ID)) return false;                           // ревизии не нужны
    if($post->post_type === 'nav_menu_item') return false;                     // меню нам нафиг не надо
    if($post->post_type === 'customize_changeset') return false;               // а это кастомайзер - нам нафиг не надо

/*     if($old_status == 'new' && $new_status == 'publish' 
        || ($old_status == 'draft' && $new_status == 'publish')
        || ($old_status == 'new' && $new_status == 'pending')
       ){ // опубликовал новую запись. Link
        $args['action'] = 'add_post';
        if($new_status == 'pending'){
            $args['other_info'] = 'pending';
        }
    }
    else if($old_status == 'pending' && $new_status == 'publish'){ // модератор одобрил запись
        global $wpdb;
        $table = $wpdb->prefix.'otfm_universe_activity';
        $res = $wpdb->update($table,
                    array('other_info' => ''),
                    array('object_id' => $post->ID, 'other_info' => 'pending') // только ту запись что была в pending
                );
        if( isset($res) ){ // ответ 0 строк или > 0
            return true; // все прошло хорошо - обновили данные. Остановим скрипт
        }
    }
    else if($old_status == 'publish' && $new_status == 'pending'){ // запись вновь на модерации
        global $wpdb;
        $table = $wpdb->prefix.'otfm_universe_activity';
        $res = $wpdb->update($table,
                    array('other_info' => 'pending'), // добавим флаг pending
                    array('user_id' => $post->post_author, 'object_id' => $post->ID, 'action' => 'add_post')
                );
        if( isset($res) ){ // ответ 0 строк или > 0
            return true; // все прошло хорошо - обновили данные. Остановим скрипт
        }
    }
    else if($old_status == 'draft' && $new_status == 'pending'){ // запись из черновика на модерации
        global $wpdb;
        $table = $wpdb->prefix.'otfm_universe_activity';
        $res = $wpdb->update($table,
                    array('other_info' => 'pending', 'action' => 'add_post'), // добавим флаг pending и сменим на add_post
                    array('user_id' => $post->post_author, 'object_id' => $post->ID, 'action' => 'add_draft')
                );
        if( isset($res) ){ // ответ 0 строк или > 0
            return true; // все прошло хорошо - обновили данные. Остановим скрипт
        }
    } */
    $post_author = $post->post_author;
    
    if($old_status == 'new' || $old_status == 'auto-draft' && $new_status == 'draft'){ // опубликовал новую запись
        if($post->post_date == $post->post_modified){ // дата публикации и изменения должны совпадать
            $args['action'] = 'add_post';
        } else {
            return false;
        }
    }
    else if($old_status == 'publish' && $new_status == 'draft' || $old_status == 'pending' && $new_status == 'draft'){ // сохранил опубликованную запись в черновик
        $args['action'] = 'add_draft';
    }
    else if($old_status == 'draft' && $new_status == 'pending' || $old_status == 'draft' && $new_status == 'publish'){ // запись из черновика на модерацию
        global $wpdb;
        $table = $wpdb->prefix.'otfm_universe_activity';
        $res = $wpdb->delete($table,
                    array('user_id' => $post_author, 'object_id' => $post->ID, 'action' => 'add_draft') // добавим флаг pending и сменим на add_post
                );
        if( isset($res) ){ // ответ 0 строк или > 0
            return true; // все прошло хорошо - обновили данные. Остановим скрипт
        }
    }
    else if($new_status == 'trash'){ // удалил
        $args['action'] = 'delete_post';
    }
    else {
        return false; // любые другие перемены нам не нужны
    }

    
    $args['user_id'] = $post_author;
    
    global $user_ID;
    if($user_ID != $post_author){ // если действие не автора записи 
        if($args['action'] == 'delete_post'){ // запись удаляет не автор записи (а например админ сайта)
            $user_info = get_userdata($post_author);
            $args['user_id'] = $user_ID;
            $args['subject_id'] = $post_author;
            $args['other_info'] = $user_info->display_name;
        } else { // все другие действия (например одобрил запись)
            $args['other_info'] = $user_ID;
        }
    }
    
    
    $args['object_id'] = $post->ID;
    if($post->post_type === 'notes'){ // если это заметка то получаем ее id (т.к. у всех заметок заголовок "Заметка")
        $args['object_name'] = una_separate_id_notes($post->post_name);
    } else {
        $args['object_name'] = $post->post_title;
    }
    $args['object_type'] = $post->post_type;
    
    una_insert($args);
}
add_action('transition_post_status', 'una_post_status', 10, 3);

 
/* add_action('save_post', 'wpse120996_on_creation_not_update', 10, 3);
function wpse120996_on_creation_not_update($post_ID, $post, $update) {
    //get_post( $post_id ) == null checks if the post is not yet in the database
vdl('pst '.$post->post_type);
    if( $post->post_type === 'video' ) {
        $args['user_id'] = $post->post_author;
        $args['object_name'] = $post->post_title;
        $args['action'] = 'add_post';
        $args['object_type'] = $post->post_type;
        
        una_insert($args);
    }
} */


// удаляем запись навсегда (запись удаляется из корзины или если корзина отключена, т.е. когда запись удаляется безвозвратно)
function una_delete_post($postid){
    $post_type = get_post_field('post_type', $postid);
    if($post_type === 'revision' || $post_type === 'video' || $post_type === 'customize_changeset') return false; // ревизии, видео из галереи и кастомайзер мы не фиксируем
    
    global $user_ID;
    $post_author = get_post_field('post_author', $postid);

    $args['user_id'] = $user_ID;
    if( defined( 'DOING_CRON' ) && DOING_CRON && !$user_ID){ // это крон очистка
        $args['user_id'] = -1;
    }
    $args['action'] = 'delete_post_fully';
    $args['object_id'] = $postid;
    $args['object_name'] = get_the_title($postid);
    $args['object_type'] = $post_type;
    
    if($user_ID != $post_author){ // не сам автор удаляет запись, а скорее всего редакторы или админ
        $user_info = get_userdata($post_author);
        $args['subject_id'] = $post_author;
        $args['other_info'] = $user_info->display_name;
    }
    
    una_insert($args);
}
add_action('before_delete_post', 'una_delete_post');


// если админ удаляет запись окончательно - вычистить из таблицы всю историю по этому id записи
function una_delete_post_in_table($postid){
    global $wpdb;
    $post_type = get_post_field('post_type', $postid);
    
    $table = $wpdb->prefix.'otfm_universe_activity';
    $wpdb->query($wpdb->prepare("DELETE FROM ".$table." WHERE object_type = '%s' AND object_id = '%d' AND action != 'delete_post_fully' ",
                                $post_type, $postid));
}
add_action('before_delete_post', 'una_delete_post_in_table');



// подписка на юзера (доп FEED)
function una_add_user_feed($feed_id, $argums){
    global $wpdb;
    $table = $wpdb->prefix.'otfm_universe_activity';
    $userdata = get_userdata($argums['object_id']);
    
    $res = $wpdb->update($table, // обновим строку
                array('act_date' => current_time('mysql')),
                array('user_id' => $argums['user_id'], 'action' => 'add_user_feed', 'subject_id' => $argums['object_id'])
            );
    if($res > 0){ // были обновлены строки
        $wpdb->query($wpdb->prepare("DELETE FROM ".$table." WHERE action = 'del_user_feed' AND user_id = '%d' AND subject_id = '%d'",
                                    $argums['user_id'], $argums['object_id']));
    } else {
        $args['user_id'] = $argums['user_id'];
        $args['action'] = 'add_user_feed';
        $args['subject_id'] = $argums['object_id'];
        $args['object_name'] = $userdata->display_name;
        $args['object_type'] = 'user';
        
        una_insert($args);
    }
}
add_action('rcl_insert_feed_data', 'una_add_user_feed', 10, 2);

// отписался от юзера (доп FEED)
function una_del_user_feed($feed){
    $userdata = get_userdata($feed->object_id);
    
    $args['user_id'] = $feed->user_id;
    $args['action'] = 'del_user_feed';
    $args['subject_id'] = $feed->object_id;
    $args['object_name'] = $userdata->display_name;
    $args['object_type'] = 'user';
    
    una_insert($args);
}
add_action('rcl_pre_remove_feed', 'una_del_user_feed');



// Добавим юзера в черный список
function una_add_user_blacklist($subject_id){
    global $wpdb, $user_ID;
    $table = $wpdb->prefix.'otfm_universe_activity';
    $userdata = get_userdata($subject_id);
    
    $res = $wpdb->update($table, // обновим строку
                array('act_date' => current_time('mysql')),
                array('user_id' => $user_ID, 'action' => 'add_user_blacklist', 'subject_id' => $subject_id)
            );
    if($res > 0){ // были обновлены строки
        $wpdb->query($wpdb->prepare("DELETE FROM ".$table." WHERE action = 'del_user_blacklist' AND user_id = '%d' AND subject_id = '%d'",
                                    $user_ID, $subject_id));
    } else { // нет у нас такой строки - значит создадим
        $args['user_id'] = $user_ID;
        $args['action'] = 'add_user_blacklist';
        $args['subject_id'] = $subject_id;
        $args['object_name'] = $userdata->display_name;
        $args['object_type'] = 'user';
        
        una_insert($args);
    }
}
add_action('add_user_blacklist', 'una_add_user_blacklist');

// отписался от юзера (доп FEED)
function una_del_user_blacklist($subject_id){
    global $user_ID;
    $userdata = get_userdata($subject_id);
    
    $args['user_id'] = $user_ID;
    $args['action'] = 'del_user_blacklist';
    $args['subject_id'] = $subject_id;
    $args['object_name'] = $userdata->display_name;
    $args['object_type'] = 'user';
    
    una_insert($args);
}
add_action('remove_user_blacklist', 'una_del_user_blacklist');



// создал группу
function una_create_group($term_id){
    global $user_ID;
    $term = get_term($term_id, 'groups');

    $args['user_id'] = $user_ID;
    $args['action'] = 'create_group';
    $args['object_id'] = $term_id;
    $args['object_name'] = $term->name;
    $args['object_type'] = 'group';
    
    una_insert($args);
}
add_action('rcl_create_group', 'una_create_group');


// из админки удаление группы
function una_delete_group($term_id){
    global $wpdb, $user_ID;

    $table = $wpdb->prefix.'otfm_universe_activity';
    $group_name = $wpdb->get_var($wpdb->prepare("SELECT object_name FROM $table WHERE action = 'create_group' AND object_type = 'group' AND object_id = %d", $term_id));
    if($group_name){ // это значит создание группы было зафиксированно системой
        $args['object_name'] = $group_name;
        // и поставим маркер что группа была удалена:
        $wpdb->update($table, // обновим строку
            array('other_info' => 'del'),
            array('action' => 'create_group', 'object_type' => 'group', 'object_id' => $term_id)
        );
    }
    
    $admin_group = $wpdb->get_var($wpdb->prepare("SELECT admin_id FROM ".RCL_PREF."groups WHERE ID = %d", $term_id));
    
    $userdata = get_userdata($admin_group);
    
    $args['user_id'] = $user_ID;
    $args['action'] = 'delete_group';
    $args['object_id'] = $term_id;
    $args['object_type'] = 'group';
    $args['subject_id'] = $admin_group;
    $args['other_info'] = $userdata->display_name;
    
    una_insert($args);
}
add_action('rcl_pre_delete_group', 'una_delete_group');



// вступил в группу
function una_user_in_group($argums){
    global $wpdb;
    
    $term = get_term($argums['group_id'], 'groups');
    $admin_group = $wpdb->get_var($wpdb->prepare("SELECT admin_id FROM ".RCL_PREF."groups WHERE ID = %d", $argums['group_id']));
    $userdata = get_userdata($admin_group);

    $args['action'] = 'user_in_group';
    $args['object_id'] = $argums['group_id'];
    $args['object_name'] = $term->name;
    $args['object_type'] = 'group';
    $args['subject_id'] = $admin_group;
    $args['other_info'] = $userdata->display_name;
    
    una_insert($args);
}
add_action('rcl_group_add_user', 'una_user_in_group');


// покинул группу
function una_user_out_group($argums){
    global $wpdb;
    
    $term = get_term($argums['group_id'], 'groups');
    $admin_group = $wpdb->get_var($wpdb->prepare("SELECT admin_id FROM ".RCL_PREF."groups WHERE ID = %d", $argums['group_id']));
    $userdata = get_userdata($admin_group);

    $args['action'] = 'user_out_group';
    $args['object_id'] = $argums['group_id'];
    $args['object_name'] = $term->name;
    $args['object_type'] = 'group';
    $args['subject_id'] = $admin_group;
    $args['other_info'] = $userdata->display_name;
    
    una_insert($args);
}
add_action('rcl_group_remove_user', 'una_user_out_group');


// создал тему на primeForum
function una_user_add_topic($topic_id, $argums){
 
    $args['action'] = 'pfm_add_topic';
    $args['object_id'] = $topic_id;
    $args['object_name'] = $argums['topic_name'];
    $args['object_type'] = 'prime_forum';
    
    una_insert($args);
}
add_action('pfm_add_topic', 'una_user_add_topic', 10, 2);


// удалил тему на primeForum
function una_user_del_topic($topic_id){
    global $wpdb, $user_ID;

    $table = $wpdb->prefix.'otfm_universe_activity';
    $topic_name = $wpdb->get_var($wpdb->prepare("SELECT object_name FROM $table WHERE action = 'pfm_add_topic' AND object_type = 'prime_forum' AND object_id = %d", $topic_id));
    if($topic_name){ // это значит создание топика было зафиксированно системой
        $args['object_name'] = $topic_name;
        // и поставим маркер что топик был удален:
        $wpdb->update($table, // обновим строку
            array('other_info' => 'del'),
            array('action' => 'pfm_add_topic', 'object_type' => 'prime_forum', 'object_id' => $topic_id)
        );
    } else { // если топик не найден в системе - запрашиваю из форума его название
        $args['object_name'] = pfm_get_topic_name($topic_id);
    }
    
    $topic_user_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM ".RCL_PREF."pforum_topics WHERE topic_id = %d", $topic_id));
    
    if($topic_user_id != $user_ID){ // если удаляет топик не его автор
        $userdata = get_userdata($topic_user_id);
        $args['subject_id'] = $topic_user_id;
        $args['other_info'] = $userdata->display_name;
    }
    
    $args['action'] = 'pfm_del_topic';
    $args['object_id'] = $topic_id;
    $args['object_type'] = 'prime_forum';

    una_insert($args);
}
add_action('pfm_pre_delete_topic', 'una_user_del_topic');






/* 
add_action('add_attachment', 'ual_shook_add_attachment');
add_action('edit_attachment', 'ual_shook_edit_attachment');
add_action('delete_attachment', 'ual_shook_delete_attachment');
add_action('edit_comment', 'ual_shook_edit_comment');
add_action('trash_comment', 'ual_shook_trash_comment');
add_action('spam_comment', 'ual_shook_spam_comment');
add_action('unspam_comment', 'ual_shook_unspam_comment');
 */





