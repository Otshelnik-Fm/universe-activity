<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


/*
  // что в массиве $data: пример рейтинга
  => Array
  (
  [user_id] => 1
  [name] => Владимир Otshelnik-Fm
  [act_date] => 2017-06-22 19:16:54
  [action] => add_comment
  [object_id] => 123
  [object_name] => для фида 2 без море
  [object_type] => comment
  [subject_id] => 2
  [other_info] => a:2:{i:0;s:4:"1604";i:1;s:4:"post";}
  [user_ip] => 128.70.201.206
  )
 */
// выводим входы/выходы
function una_get_logged_in_out( $data ) {
    $texts_login   = [ 'Вошел', 'Вошла' ];
    $decline_login = una_decline_by_sex( $data['user_id'], $texts_login );

    $texts_logout   = [ 'Вышел', 'Вышла' ];
    $decline_logout = una_decline_by_sex( $data['user_id'], $texts_logout );

    $act = ( $data['action'] == 'logged_out' ) ? $decline_logout . ' с сайта' : $decline_login . ' на сайт';

    $net = '';
    if ( $data['action'] == 'logged_in_ulogin' ) {
        $net = ' через: ' . $data['other_info'];
    }

    return '<span class="una_action">' . $act . '</span>' . $net;
}

// deprecated
// кто-то войти не смог или брутфорсил
/* function una_get_login_failed($data){

  } */

// кто-то использовал существующие логин или мейл
function una_register_failed( $data ) {
    $ret = '';
    if ( $data['other_info'] == 'name' )
        $ret = 'Такое имя';
    if ( $data['other_info'] == 'email' )
        $ret = 'Такая почта';
    if ( $data['other_info'] == 'nameemail' )
        $ret = 'Такие имя и почта';

    $out = '<span class="una_action">Неудачная регистрация:</span> ' . $ret . ' уже есть в базе.';
    $out .= '<br/>Данные регистрации: ' . $data['object_name'];

    return $out;
}

// удаление юзера
function una_get_delete_user( $data ) {
    $mail = '';
    if ( ! empty( $data['other_info'] ) ) {
        $mail = '<br/>Зарегистрирован на почту ' . $data['other_info'];
    }
    $out = '<span class="una_action">Удалил пользователя</span> ' . $data['object_name'] . ' id=' . $data['subject_id'] . $mail;

    return $out;
}

// обновил профиль
function una_get_profile_update( $data ) {
    $texts   = [ 'Обновил', 'Обновила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    return '<span class="una_action">' . $decline . ' настройки профиля</span>';
}

// обновил статус профиля
function una_get_change_user_status( $data ) {
    $texts   = [ 'Сменил', 'Сменила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $userdata = get_userdata( $data['user_id'] );

    return '<span class="una_action">' . $decline . ' статус:</span><div class="una_user_status"><div>' . $userdata->description . '</div></div>';
}

// поставил рейтинг за (потом перепишу ее - главное скелет есть)
function una_get_give_rating_post( $data ) {
    $other = unserialize( $data['other_info'] );
    /* Array
      (
      [0] => Владимир Otshelnik-Fm
      [1] => plus
      [2] => 1
      [3] => 3
      ) */

    $rating = una_rating_styling( $other[1], $other[2] );

    $type = 'запись';
    $link = '<a href="/?p=' . $data['object_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';
    if ( $data['action'] == 'give_rating_notes' ) {
        $type = 'заметку';
    }
    if ( $data['action'] == 'give_rating_comment' ) {
        $type = 'комментарий';
        $name = 'гостя';
        if ( $data['subject_id'] ) {
            $name = get_comment_author( $data['object_id'] );
        }

        $link = '<a href="/?una_comment_id=' . $data['object_id'] . '" title="Перейти к комментарию" rel="nofollow">' . $name . '</a>';
    } else if ( $data['action'] == 'give_rating_products' ) {
        $type = 'товар';
    } else if ( $data['action'] == 'give_rating_forum-post' ) { // если рейтинг за комментарий на prime forum
        $type = 'комментарий на форуме';
        $link = '<a href="/?una_prime_forum_topic_url=' . $data['object_id'] . '" title="Перейти к комментарию" rel="nofollow">' . una_get_username( $data['subject_id'] ) . '</a>';
    } else if ( $data['action'] == 'give_rating_forum-page' ) { // если рейтинг за комментарий на Asgaros forum
        $type = 'комментарий на форуме';
        $link = '<a href="/?una_asgrs_forum_post_url=' . $data['object_id'] . '" title="Перейти к комментарию" rel="nofollow">' . una_get_username( $data['subject_id'] ) . '</a>';
    }

    $object = '';
    if ( $data['action'] == 'give_rating_post-group' ) {
        $group = una_get_group_by_post( $data['object_id'] );

        if ( $group ) {
            $gp_info = '<a href="/?una_group_url=' . $group->term_id . '" title="Перейти" rel="nofollow">' . $group->name . '</a>';
            $object  = ', в группе ' . $gp_info;
        }
    }
    //
    else if ( $data['action'] == 'give_rating_comment' && isset( $data['group_id'] ) && isset( $other[3] ) ) {
        $group = una_get_group_by_post( $other[3] );

        if ( $group ) {
            $gp_info = '<a href="/?una_group_url=' . $group->term_id . '" title="Перейти" rel="nofollow">' . $group->name . '</a>';
            $object  = ', в группе ' . $gp_info;
        }
    }

    $texts   = [ 'Проголосовал', 'Проголосовала' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $out = '<span class="una_action">' . $decline . '</span> ' . $rating . ' за ' . $type . ': ';
    $out .= $link . $object;

    return $out;
}

// для этого есть доп Уведомления!
// получил рейтинг
/* function una_get_get_rating_post($data){
  $other = unserialize($data['other_info']);
  $simbol = '-';
  if($other[1] == 'plus') $simbol = '+';

  $out = '<a href="'.get_author_posts_url($other[3]).'" title="Перейти" rel="nofollow">' . $other[0] . '</a>';
  $out .= ' поставил оценку '.$simbol.$other[2]. ' за запись: ';
  $out .= '<a href="/?p='.$data['object_id'].'" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';

  return $out;
  } */

// зарегался
function una_get_register( $data ) {
    $texts   = [ 'Зарегистрировался', 'Зарегистрировалась' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    return '<span class="una_action">' . $decline . ' на сайте</span>';
}

// подтвердил регу
function una_get_confirm_register( $data ) {
    $texts   = [ 'Подтвердил', 'Подтвердила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    return '<span class="una_action">' . $decline . ' регистрацию на сайте</span>';
}

// оставил комментарий
function una_get_add_comment( $data ) {
    $other = unserialize( $data['other_info'] );
    /* Array(
      [st] => 1                                       // status: 1 - одобрено, 0 - на модерации, spam - спам
      [pt] => post-group                              // post_type: тип записи
      [grid] => 63                                    // group_id: если группа то эти два добавляются: id группы
      [grn] => Группа путешественников во времени     // group_name: Название группы
      [par] => 1                                      // parent - флаг 1. Ответ на комментарий, а не просто комментарий
      [sbj_nm] => Путешественник во времени2          // subject_name - имя того, на чей камент отвечаем (его id пишется в subject_id)
      ) */

    $go_to   = '';
    $gp_info = '';
    $type    = ' к записи: ';
    if ( $other['pt'] == 'products' ) {
        $type = ' к товару: ';
    }
    $type_fin = $type . '"' . $data['object_name'] . '"';

    if ( $other['pt'] == 'post-group' && isset( $other['grid'] ) && isset( $other['grn'] ) ) {
        $gp_info = ', в группе ';
        $gp_info .= '<a href="/?una_group_url=' . $other['grid'] . '" title="Перейти" rel="nofollow">' . $other['grn'] . '</a>';
    }

    $status = '';
    switch ( $other['st'] ) {
        case '0':
            $status = 'на&nbsp;утверждении';
            break;
        case 'trash':
            $status = 'в&nbsp;корзине';
            break;
        case 'spam':
            $status = 'спам';
            break;
    }

    if ( $other['st'] == 1 ) { // комментарий опубликован, одобрен и есть на сайте
        $go_to = '<div class="una_goto_comments">';
        $go_to .= '<a href="/?una_comment_id=' . $data['object_id'] . '" title="Перейти" rel="nofollow">';
        $go_to .= '<span>Прочитать комментарий</span><i class="rcli fa-angle-double-right" aria-hidden="true"></i>';
        $go_to .= '</a>';
        $go_to .= '</div>';
    } else {
        if ( ! empty( $status ) )
            $go_to = ' <span class="una_post_status una_st_' . $other['st'] . '">(' . $status . ')</span>';
    }
    if ( isset( $other['par'] ) && $other['par'] && isset( $other['sbj_nm'] ) && $other['sbj_nm'] ) { // если это ответ и не самому себе
        $texts   = [ 'Ответил', 'Ответила' ];
        $decline = una_decline_by_sex( $data['user_id'], $texts );

        $do          = $decline . ' ';
        $whom        = 'на комментарий пользователя ';
        $link_author = '"' . $other['sbj_nm'] . '"';

        if ( $data['subject_id'] > 0 ) { // и ответ зарегистрированному юзеру
            $link_author = '<a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">"' . $other['sbj_nm'] . '"</a>';
        }

        $out = '<span class="una_action">' . $do . $whom . '</span>' . $link_author . $type_fin . $gp_info;
    } else {
        $texts   = [ 'Оставил', 'Оставила' ];
        $decline = una_decline_by_sex( $data['user_id'], $texts );

        $out = '<span class="una_action">' . $decline . ' комментарий</span>' . $type_fin . $gp_info;
    }

    return $out . $go_to;
}

// добавил запись
function una_get_add_post( $data ) {
    $post_name = '"' . $data['object_name'] . '"';
    $link      = '<a class="una_p_link" href="/?p=' . $data['object_id'] . '" title="Перейти" rel="nofollow">' . $post_name . '</a>';

    $status = $data['post_status'];
    if ( ! $status )
        $link   = $post_name . '<span class="una_post_status">(удалено)</span>'; // удалено
    else if ( $status === 'trash' ) {
        $link = $post_name . '<span class="una_post_status">(удалено в корзину)</span>';
    } else if ( $status === 'draft' ) {
        $link = $post_name . '<span class="una_post_status">(черновик)</span>';
        if ( current_user_can( 'edit_post', $data['object_id'] ) ) {
            $link = '<a href="/?p=' . $data['object_id'] . '" title="Перейти" rel="nofollow">' . $post_name . '</a><span class="una_post_status">(черновик)</span>';
            //$link = '<a href="'.get_permalink($data['object_id']).'" title="Перейти" rel="nofollow">'.$post_name.'</a><span class="una_post_status">(черновик)</span>';
        }
    } else if ( $status === 'pending' ) {
        $link = $post_name . '<span class="una_post_status">(на утверждении)</span>';
        if ( current_user_can( 'edit_post', $data['object_id'] ) ) {
            $link = '<a href="/?p=' . $data['object_id'] . '" title="Перейти" rel="nofollow">' . $post_name . '</a><span class="una_post_status">(на утверждении)</span>';
        }
    }

    $type = 'запись';
    if ( $data['object_type'] === 'notes' )
        $type = 'заметку';
    if ( $data['object_type'] === 'products' )
        $type = 'товар';

    $object = '';
    if ( $data['object_type'] == 'post-group' ) {
        $group = una_get_group_by_post( $data['object_id'] );

        if ( $group ) {
            $gp_info = '<a class="una_p_link" href="/?una_group_url=' . $group->term_id . '" title="Перейти" rel="nofollow">' . $group->name . '</a>';
            $object  = ', в группе ' . $gp_info;
        }
    }

    $decline = una_decline_by_sex( $data['user_id'], [ 'Добавил', 'Добавила' ] );

    $out = '<span class="una_action">' . $decline . ' ' . $type . ':</span> ' . $link . $object;

    return $out;
}

// убрал запись в черновик
function una_get_add_draft( $data ) {
    $texts   = [ 'Убрал', 'Убрала' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $out = '<span class="una_action">' . $decline . ' запись в черновик:</span> ';
    $out .= '"' . $data['object_name'] . '"';

    return $out;
}

// удалил запись
function una_get_delete_post( $data ) {
    $texts   = [ 'удалил', 'удалила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $status      = 'в корзину';
    $what        = $decline . ' ';
    $type        = 'запись';
    $link_author = '';

    if ( $data['action'] === 'delete_post_fully' ) {
        $status = 'навсегда';
    }
    if ( $data['object_type'] === 'notes' )
        $type = 'заметку';
    if ( $data['object_type'] === 'products' )
        $type = 'товар';
    if ( $data['subject_id'] ) {
        $link_author = '<span class="una_post_status una_post_author">- автор: <a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['other_info'] . '</a></span>';
    }

    $out = '<span class="una_action">' . $what . $type . ':</span> ';
    $out .= '"' . $data['object_name'] . '"<span class="una_post_status">(' . $status . ')</span>' . $link_author;

    return $out;
}

// Подписка на юзера
function una_get_add_user_feed( $data ) {
    $texts   = [ 'Подписался', 'Подписалась' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $link_author = '<a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';
    $out         = '<span class="una_action">' . $decline . ' на пользователя</span> ' . $link_author;
    return $out;
}

// Отписка на юзера
function una_get_del_user_feed( $data ) {
    $texts   = [ 'Отписался', 'Отписалась' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $link_author = '<a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';
    $out         = '<span class="una_action">' . $decline . ' от пользователя</span> ' . $link_author;
    return $out;
}

// Добавил в черный список
function una_get_add_user_blacklist( $data ) {
    $texts   = [ 'Добавил', 'Добавила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $link_author = '<a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';
    $out         = '<span class="una_action">' . $decline . ' пользователя</span> ' . $link_author . ' в чёрный список';
    return $out;
}

// Удалил из черного списка
function una_get_del_user_blacklist( $data ) {
    $texts   = [ 'Убрал', 'Убрала' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $link_author = '<a class="una_subject" class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';
    $out         = '<span class="una_action">' . $decline . ' пользователя</span> ' . $link_author . ' из чёрного списка';
    return $out;
}

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

// создал тему на primeForum
function una_get_user_add_topic( $data ) {
    $texts   = [ 'Создал', 'Создала' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $del  = '';
    $link = '<a href="/?una_prime_forum_url=' . $data['object_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';
    if ( $data['other_info'] == 'del' ) { // если группа удалена - то пишется в нее del. А так колонка пустая
        $link = '"' . $data['object_name'] . '"';
        $del  = '<span class="una_post_status">(удалено)</span>';
    }

    $out = '<span class="una_action">' . $decline . ' новую тему на форуме:</span> ' . $link . $del;

    return $out;
}

// удалил топик (тему на форуме)
function una_get_user_del_topic( $data ) {
    $texts   = [ 'Удалил', 'Удалила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $link_author = '';

    if ( $data['subject_id'] ) {
        $link_author = '<span class="una_post_status una_post_author">- автор: <a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['other_info'] . '</a></span>';
    }

    $out = '<span class="una_action">' . $decline . ' тему:</span> "' . $data['object_name'] . '" с форума' . $link_author;

    return $out;
}

// поставил обложку
function una_get_add_cover( $data ) {
    $texts   = [ 'Установил', 'Установила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $user_name = get_the_author_meta( 'display_name', $data['user_id'] );
    $src       = una_get_pictures_src( $data['user_id'], 'rcl_cover' );
    $cover     = '<a class="mpr_image una_cover" href="' . $src . '" title="Обложка пользователя: ' . $user_name . '"><img style="max-height:250px;display:block;" src="' . $src . '" alt="" loading="lazy"></a>';

    return '<span class="una_action">' . $decline . ' обложку</span>' . $cover;
}

// поставил аватар
function una_get_add_avatar( $data ) {
    $texts   = [ 'Установил', 'Установила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $user_name = get_the_author_meta( 'display_name', $data['user_id'] );
    if ( $data['other_info'] == 'archive' ) {
        $datename = date( 'Y-m-d--H-i-s', strtotime( $data['act_date'] ) );
        $src      = RCL_UPLOAD_URL . 'otfm-older-avatars/' . $data['user_id'] . '/' . $datename . '-ava.jpg';
    } else {
        $src = una_get_pictures_src( $data['user_id'] );
    }

    $cover = '<a class="mpr_image una_avatar" href="' . $src . '" title="Аватарка пользователя: ' . $user_name . '<br>Загружена: ' . $data['act_date'] . '"><img style="max-height:250px;display:block;" src="' . $src . '" alt="" loading="lazy"></a>';

    return '<span class="una_action">' . $decline . ' аватарку</span>' . $cover;
}

// удалил аватар
function una_get_del_avatar( $data ) {
    $texts   = [ 'Удалил', 'Удалила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    return '<span class="una_action">' . $decline . ' аватарку</span>';
}

// неверные попытки сброса пароля
function una_get_pass_reset_fail( $data ) {
    $other = unserialize( $data['other_info'] );

    $type = '';
    if ( isset( $other['ml'] ) ) {
        $type = 'по почте: ' . $other['ml'];
    } else if ( isset( $other['nm'] ) ) {
        $type = 'по имени:  ' . $other['nm'];
    }

    $ip = '<span class="una_post_status">(запрос с ip: ' . $data['user_ip'] . ')</span>';

    return '<span class="una_action"> попытался сбросить пароль ' . $type . $ip . '</span>';
}

// успешная отправка письма с ссылкой сброса пароля
function una_get_pass_reset_mail( $data ) {
    $texts   = [ 'Запросил', 'Запросила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    $ip = '<span class="una_post_status">(запрос с ip: ' . $data['user_ip'] . ')</span>';

    return '<span class="una_action">' . $decline . ' отправку письма на сброс пароля пользователя ' . una_get_username( $data['subject_id'], 1 ) . $ip . '</span>';
}

// Подтвердил изменение пароля через почту
function una_get_pass_reset_confirm( $data ) {
    $texts   = [ 'Подтвердил', 'Подтвердила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    return '<span class="una_action">' . $decline . ' изменение пароля через почту</span>';
}

// Изменил пароль через ЛК
function una_get_pass_change( $data ) {
    $texts   = [ 'Изменил', 'Изменила' ];
    $decline = una_decline_by_sex( $data['user_id'], $texts );

    return '<span class="una_action">' . $decline . ' пароль через личный кабинет</span>';
}
