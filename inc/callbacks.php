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
    $act = ( $data['action'] == 'logged_out' ) ? 'Вышел с сайта' : 'Вошел на сайт';

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
    return '<span class="una_action">Обновил настройки профиля</span>';
}

// обновил статус профиля
function una_get_change_user_status( $data ) {
    $userdata = get_userdata( $data['user_id'] );

    return '<span class="una_action">Сменил статус:</span><div class="una_user_status"><div>' . $userdata->description . '</div></div>';
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
        $name = get_userdata( $data['subject_id'] );
        $link = '<a href="/?una_prime_forum_topic_url=' . $data['object_id'] . '" title="Перейти к комментарию" rel="nofollow">' . $name->display_name . '</a>';
    } else if ( $data['action'] == 'give_rating_forum-page' ) { // если рейтинг за комментарий на Asgaros forum
        $type = 'комментарий на форуме';
        $name = get_userdata( $data['subject_id'] );
        $link = '<a href="/?una_asgrs_forum_post_url=' . $data['object_id'] . '" title="Перейти к комментарию" rel="nofollow">' . $name->display_name . '</a>';
    }

    $object = '';
    if ( $data['action'] == 'give_rating_post-group' ) {
        $group = una_get_group_by_post( $data['object_id'] );

        if ( $group ) {
            $group_id   = $group->term_id;
            $group_name = $group->name;

            $gp_info = '<a href="/?una_group_url=' . $group_id . '" title="Перейти" rel="nofollow">' . $group_name . '</a>';
            $object  = ', в группе ' . $gp_info;
        }
    }

    $out = '<span class="una_action">Проголосовал</span> ' . $rating . ' за ' . $type . ': ';
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
    return '<span class="una_action">Зарегистрировался на сайте</span>';
}

// подтвердил регу
function una_get_confirm_register( $data ) {
    return '<span class="una_action">Подтвердил регистрацию на сайте</span>';
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

    if ( $other['pt'] == 'post-group' ) {
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
        $go_to .= '<span>Прочитать комментарий</span><i class="fa fa-angle-double-right" aria-hidden="true"></i>';
        $go_to .= '</a>';
        $go_to .= '</div>';
    } else {
        if ( ! empty( $status ) )
            $go_to = ' <span class="una_post_status una_st_' . $other['st'] . '">(' . $status . ')</span>';
    }
    if ( isset( $other['par'] ) && $other['par'] && isset( $other['sbj_nm'] ) && $other['sbj_nm'] ) { // если это ответ и не самому себе
        $do          = 'Ответил ';
        $whom        = 'на комментарий пользователя ';
        $link_author = '"' . $other['sbj_nm'] . '"';

        if ( $data['subject_id'] > 0 ) { // и ответ зарегистрированному юзеру
            $link_author = '<a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">"' . $other['sbj_nm'] . '"</a>';
        }

        $out = '<span class="una_action">' . $do . $whom . '</span>' . $link_author . $type_fin . $gp_info;
    } else {
        $out = '<span class="una_action">Оставил комментарий</span>' . $type_fin . $gp_info;
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
            $gp_info .= '<a class="una_p_link" href="/?una_group_url=' . $group->term_id . '" title="Перейти" rel="nofollow">' . $group->name . '</a>';
            $object  = ', в группе ' . $gp_info;
        }
    }

    $out = '<span class="una_action">Добавил ' . $type . ':</span> ' . $link . $object;

    return $out;
}

// убрал запись в черновик
function una_get_add_draft( $data ) {
    $out = '<span class="una_action">Убрал запись в черновик:</span> ';
    $out .= '"' . $data['object_name'] . '"';

    return $out;
}

// удалил запись
function una_get_delete_post( $data ) {
    $status      = 'в корзину';
    $what        = 'Автор удалил ';
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
        $what = 'Удалил ';
        if ( $data['action'] === 'delete_post_fully' ) {
            $what = 'WP-Cron удалил ';
        }
        $link_author = '<span class="una_post_status una_post_author">- автор: <a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['other_info'] . '</a></span>';
    }

    $out = '<span class="una_action">' . $what . $type . ':</span> ';
    $out .= '"' . $data['object_name'] . '"<span class="una_post_status">(' . $status . ')</span>' . $link_author;

    return $out;
}

// Подписка на юзера
function una_get_add_user_feed( $data ) {
    $link_author = '<a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';
    $out         = '<span class="una_action">Подписался на пользователя</span> ' . $link_author;
    return $out;
}

// Отписка на юзера
function una_get_del_user_feed( $data ) {
    $link_author = '<a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';
    $out         = '<span class="una_action">Отписался от пользователя</span> ' . $link_author;
    return $out;
}

// Добавил в черный список
function una_get_add_user_blacklist( $data ) {
    $link_author = '<a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';
    $out         = '<span class="una_action">Добавил пользователя</span> ' . $link_author . ' в черный список';
    return $out;
}

// Удалил из черного списка
function una_get_del_user_blacklist( $data ) {
    $link_author = '<a class="una_subject" class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['object_name'] . '</a>';
    $out         = '<span class="una_action">Убрал пользователя</span> ' . $link_author . ' из черного списка';
    return $out;
}

// Создал группу
function una_get_create_group( $data ) {
    $link = '"' . $data['object_name'] . '"';
    if ( ! $data['other_info'] ) { // если группа удалена - то пишется в нее del. А так колонка пустая
        $link = '<a href="/?una_group_url=' . $data['object_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';
        //$link = rcl_get_group_permalink($data['object_id']); // +1 db
    }

    $out = '<span class="una_action">Создал новую группу<span class="una_colon">:</span></span> ' . $link . '';
    return $out;
}

// удалил группу
function una_get_delete_group( $data ) {
    $group_name = ( ! empty( $data['object_name'] )) ? $data['object_name'] : 'unknown';

    return '<span class="una_action">Удалил группу:</span> "' . $group_name . '"';
}

// вступил в группу/покинул группу/удалили из группы
function una_get_user_in_out_group( $data ) {
    $out = 'Вступил в группу';
    if ( $data['action'] == 'user_out_group' ) {
        $out = 'Покинул группу';
    }
    if ( $data['action'] == 'user_out_group' && $data['other_info'] == 'kick' ) {
        $userdata    = get_userdata( $data['subject_id'] );
        $link_author = '<a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $userdata->display_name . '</a>';
        $out         = 'удалил пользователя ' . $link_author . ' из группы';
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

    $role_txt  = 'забанил пользователя';
    $role_type = '';
    if ( $data['action'] !== 'group_user_ban' ) {
        $role_txt  = 'сменил роль пользователя';
        $role_type = '. Назначена роль - ' . una_group_user_role_name( $other['ur'] );
    }
    $group_link = '<a class="una_group_name" href="/?una_group_url=' . $data['object_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';

    return '<span class="una_action">' . $role_txt . ' </span>' . $link_author . ' в группе<span class="una_colon">:</span> ' . $group_link . $role_type;
}

// установил в группе описание, сменил его
function una_get_group_change_desc( $data ) {
    $termdata = get_term( $data['group_id'] );
    $name     = '<a class="una_group_name" href="/?una_group_url=' . $data['group_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';

    return '<span class="una_action">Установил описание группы<span class="una_colon">:</span></span> ' . $name . '<div class="una_user_status"><div>' . $termdata->description . '</div></div>';
}

// статус группы: открыта/закрыта
// изменил приватность группы Gutenberg. Сейчас это открытая группа
function una_get_group_is_closed( $data ) {
    $status = ('closed' === $data['other_info']) ? 'закрытая' : 'открытая';

    $name = '<a class="una_group_name" href="/?una_group_url=' . $data['group_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';

    return '<span class="una_action">Изменил приватность группы </span> ' . $name . '. Сейчас это ' . $status . ' группа';
}

// создал тему на primeForum
function una_get_user_add_topic( $data ) {
    $del  = '';
    $link = '<a href="/?una_prime_forum_url=' . $data['object_id'] . '" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';
    if ( $data['other_info'] == 'del' ) { // если группа удалена - то пишется в нее del. А так колонка пустая
        $link = '"' . $data['object_name'] . '"';
        $del  = '<span class="una_post_status">(удалено)</span>';
    }

    $out = '<span class="una_action">Создал новую тему на форуме:</span> ' . $link . $del;

    return $out;
}

// удалил топик (тему на форуме)
function una_get_user_del_topic( $data ) {
    $link_author = '';

    if ( $data['subject_id'] ) {
        $link_author = '<span class="una_post_status una_post_author">- автор: <a class="una_subject" href="/?una_author=' . $data['subject_id'] . '" title="Перейти" rel="nofollow">' . $data['other_info'] . '</a></span>';
    }

    $out = '<span class="una_action">Удалил тему:</span> "' . $data['object_name'] . '" с форума' . $link_author;

    return $out;
}

// поставил обложку
function una_get_add_cover( $data ) {
    $user_name = get_the_author_meta( 'display_name', $data['user_id'] );
    $src       = RCL_UPLOAD_URL . 'covers/' . $data['user_id'] . '.jpg';
    $cover     = '<a class="mpr_image una_cover" href="' . $src . '" title="Обложка пользователя: ' . $user_name . '"><img style="max-height: 250px;display: block;" src="' . $src . '" alt=""></a>';

    return '<span class="una_action">Установил обложку</span>' . $cover;
}

// поставил аватар
function una_get_add_avatar( $data ) {
    $user_name = get_the_author_meta( 'display_name', $data['user_id'] );
    if ( $data['other_info'] == 'archive' ) {
        $datename = date( 'Y-m-d--H-i-s', strtotime( $data['act_date'] ) );
        $src      = RCL_UPLOAD_URL . 'otfm-older-avatars/' . $data['user_id'] . '/' . $datename . '-ava.jpg';
    } else {
        $src = RCL_UPLOAD_URL . 'avatars/' . $data['user_id'] . '.jpg';
    }

    $cover = '<a class="mpr_image una_avatar" href="' . $src . '" title="Аватарка пользователя: ' . $user_name . '<br>Загружена: ' . $data['act_date'] . '"><img style="max-height: 250px;display: block;" src="' . $src . '" alt=""></a>';

    return '<span class="una_action">Установил аватарку</span>' . $cover;
}

// удалил аватар
function una_get_del_avatar( $data ) {
    return '<span class="una_action">Удалил аватарку</span>';
}
