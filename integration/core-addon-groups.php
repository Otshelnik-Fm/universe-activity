<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/*
 * 1. Зарегистрируем в массив новые типы и привилегии
 * (если не указана привилегия - то видят все начиная от гостя)
 * подробнее в описании допа вкладка "Логика/Настройки" пункт "События и привилегии"
 * https://codeseller.ru/products/universe-activity/
 */
// $type['уникальный_экшен']['callback'] = 'имя_коллбек_функции';
add_filter( 'una_register_type', 'una_register_groups_addon', 8 );
function una_register_groups_addon( $type ) {
    $type['create_group']      = [
        'name'     => 'Создал группу', ////////// Событие. "отвечая на вопрос: Что сделал"
        'source'   => 'groups', ///////////////// Источник (wordpress, плагин, аддон - slug аддона или имя, как в списке допов)
        'callback' => 'una_get_create_group', /// функция вывода
    ];
    $type['delete_group']      = [
        'name'     => 'Удалил группу',
        'source'   => 'groups',
        'callback' => 'una_get_delete_group',
        'access'   => 'admin',
    ];
    $type['user_in_group']     = [
        'name'     => 'Вступил в группу',
        'source'   => 'groups',
        'callback' => 'una_get_user_in_out_group',
    ];
    $type['user_out_group']    = [
        'name'     => 'Покинул группу',
        'source'   => 'groups',
        'callback' => 'una_get_user_in_out_group',
        'access'   => 'logged',
    ];
    $type['group_change_desc'] = [
        'name'     => 'Пользователь установил (сменил) в группе описание',
        'source'   => 'groups',
        'callback' => 'una_get_group_change_desc',
    ];
    $type['group_user_ban']    = [
        'name'     => 'Пользователь забанен в группе',
        'source'   => 'groups',
        'callback' => 'una_get_group_user_change_role',
        'access'   => 'logged',
    ];
    $type['group_user_role']   = [
        'name'     => 'Сменилась роль пользователя в группе',
        'source'   => 'groups',
        'callback' => 'una_get_group_user_change_role',
        'access'   => 'logged',
    ];
    $type['group_is_closed']   = [
        'name'     => 'Смена статуса группы: открытая/закрытая',
        'source'   => 'groups',
        'callback' => 'una_get_group_is_closed',
        'access'   => 'logged',
    ];

    return $type;
}

/*
 * 2. Пишем активность в бд:
 */


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

    $args['user_id']     = $user_ID;
    $args['action']      = 'delete_group';
    $args['object_id']   = $term_id;
    $args['object_type'] = 'group';
    $args['subject_id']  = $admin_group;
    $args['other_info']  = una_get_username( $admin_group );

    una_insert( $args );
}

// вступил в группу
add_action( 'rcl_group_add_user', 'una_user_in_group' );
function una_user_in_group( $argums ) {
//	global $wpdb;

    $term = get_term( $argums['group_id'], 'groups' );
//	$admin_group = $wpdb->get_var( $wpdb->prepare( "SELECT admin_id FROM " . RCL_PREF . "groups WHERE ID = %d", $argums['group_id'] ) );
//	$userdata	 = get_userdata( $admin_group );

    $args['user_id']     = $argums['user_id'];
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
    $term = get_term( $data['group_id'], 'groups' );

    $in_other = array( 'un' => una_get_username( $data['user_id'] ), 'ur' => $data['user_role'] ); // массив данных записи

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

/*
 * 3. Выводим в общую ленту
 * una_get_create_group - зарегистрированная в 1-й функции в callback
 *
 */

/*
  // $data содержит:
  Array(
  [id] => 2834
  [user_id] => 1
  [action] => give_rating_notes
  [act_date] => 2018-01-17 19:24:30
  [object_id] => 45692
  [object_name] => Заголовок
  [object_type] => video
  [subject_id] => 0
  [other_info] =>
  [user_ip] => 3.52.235.164
  [display_name] =>
  [post_status] =>
  ) */
// Создал группу
function una_get_create_group( $data ) {
    $link = '"' . $data['object_name'] . '"';
    if ( ! $data['other_info'] ) { // если группа удалена - то пишется в нее del. А так колонка пустая
        $link = '<a href="/?una_group_url=' . $data['object_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';
        //$link = rcl_get_group_permalink($data['object_id']); // +1 db
    }

    $texts   = [ 'Создал', 'Создала' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $out = '<span class="una_action">' . $decline . ' новую группу<span class="una_colon">:</span></span> ' . $link . '';
    return $out;
}

// удалил группу
function una_get_delete_group( $data ) {
    $group_name = ( ! empty( $data['object_name'] )) ? $data['object_name'] : 'unknown';

    $texts   = [ 'Удалил', 'Удалила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    return '<span class="una_action">' . $decline . ' группу:</span> "' . $group_name . '"';
}

// вступил в группу/покинул группу/удалили из группы
function una_get_user_in_out_group( $data ) {
    $texts   = [ 'Вступил', 'Вступила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $out = $decline . ' в группу';
    if ( $data['action'] == 'user_out_group' ) {
        $texts   = [ 'Покинул', 'Покинула' ];
        $decline = una_decline_by_sex( $data['user_id'], $texts );

        $out = $decline . ' группу';
    }
    if ( $data['action'] == 'user_out_group' && $data['other_info'] == 'kick' ) {
        $texts   = [ 'удалил', 'удалила' ];
        $decline = una_decline_by_sex( $data['user_id'], $texts );

        $out = $decline . ' пользователя ' . una_get_username( $data['subject_id'], 1 ) . ' из группы';
    }

    $link = '<a class="una_group_name" href="/?una_group_url=' . $data['object_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';

    return '<span class="una_action">' . $out . '<span class="una_colon">:</span> </span>' . $link;
}

// сменили юзеру в группе роль/забанили в группе
// Otshelnik-Fm забанил пользователя Игорь в группе: Gutenberg
// Otshelnik-Fm сменил роль пользователя Игорь в группе: Gutenberg. Назначена роль - редактор.
function una_get_group_user_change_role( $data ) {
    $other = unserialize( $data['other_info'] );

    $link_author = '<a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $other['un'] . '</a>';

    $texts   = [ 'забанил', 'забанила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $role_txt  = $decline . ' пользователя';
    $role_type = '';
    if ( $data['action'] !== 'group_user_ban' ) {
        $texts   = [ 'сменил', 'сменила' ];
        $decline = una_decline_by_sex( $data['user_id'], $texts );

        $role_txt  = $decline . ' роль пользователя';
        $role_type = '. Назначена роль - ' . una_group_user_role_name( $other['ur'] );
    }
    $group_link = '<a class="una_group_name" href="/?una_group_url=' . $data['object_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';

    return '<span class="una_action">' . $role_txt . ' </span>' . $link_author . ' в группе<span class="una_colon">:</span> ' . $group_link . $role_type;
}

// установил в группе описание, сменил его
function una_get_group_change_desc( $data ) {
    $texts   = [ 'Установил', 'Установила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $termdata = get_term( $data['group_id'] );
    $name     = '<a class="una_group_name" href="/?una_group_url=' . $data['group_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';

    return '<span class="una_action">' . $decline . ' описание группы<span class="una_colon">:</span></span> ' . $name . '<div class="una_user_status"><div>' . $termdata->description . '</div></div>';
}

// статус группы: открыта/закрыта
// изменил приватность группы Gutenberg. Сейчас это открытая группа
function una_get_group_is_closed( $data ) {
    $texts   = [ 'Изменил', 'Изменила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $status = ('closed' === $data['other_info']) ? 'закрытая' : 'открытая';

    $name = '<a class="una_group_name" href="/?una_group_url=' . $data['group_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';

    return '<span class="una_action">' . $decline . ' приватность группы </span> ' . $name . '. Сейчас это ' . $status . ' группа';
}

/*
 * 4. добавлю к кнопкам фильтрам
 *
 */

// к кнопке-фильтр "Обновления" добавлю события
add_filter( 'una_filter_updates', 'una_group_filter_button', 10 );
function una_group_filter_button( $actions ) {
    array_push( $actions, 'create_group', 'user_in_group' );

    return $actions;
}
