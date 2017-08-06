<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

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
function una_get_logged_in_out($data){
    $act = 'Вошел на сайт';
    if($data['action'] == 'logged_out'){
        $act = 'Вышел с сайта';
    }
    $net = '';
    if($data['action'] == 'logged_in_ulogin'){
        $net = ' через: '.$data['other_info'];
    }
    
    return '<span class="una_action">' . $act . '</span>' . $net;
}

// кто-то войти не смог или брутфорсил
/* function una_get_login_failed($data){
    
} */

// кто-то использовал существующие логин или мейл
function una_register_failed($data){
    $ret = '';
    if($data['other_info'] == 'name') $ret = 'Такое имя';
    if($data['other_info'] == 'email') $ret = 'Такая почта';
    if($data['other_info'] == 'nameemail') $ret = 'Такие имя и почта';
    
    $out = '<span class="una_action">Неудачная регистрация:</span> ' . $ret . ' уже есть в базе.';
    $out .= '<br/>Данные регистрации: '.$data['object_name'];
    
    return $out;
}


// удаление юзера
function una_get_delete_user($data){
    $out = '<span class="una_action">Удалил пользователя</span> '.$data['object_name']. ' id=' . $data['subject_id'];
    return $out;
}

// обновил профиль
function una_get_profile_update($data){
    return '<span class="una_action">Обновил настройки профиля</span>';
}


// обновил статус профиля
function una_get_change_user_status($data){
    $userdata = get_userdata($data['user_id']);
    
    return '<span class="una_action">Сменил статус:</span><div class="una_user_status">'.$userdata->description.'</div>';
}


// поставил рейтинг за (потом перепишу ее - главное скелет есть)
function una_get_give_rating_post($data){
    $other = unserialize($data['other_info']);
/* Array
(
    [0] => Владимир Otshelnik-Fm
    [1] => plus
    [2] => 1
    [3] => 3
) */
    $rating = una_rating_styling($other[1], $other[2]);
    
    $type = 'запись';
    $link = '<a href="/?p='.$data['object_id'].'" title="Перейти" rel="nofollow">"' . $data['object_name'] . '"</a>';
    if($data['action'] == 'give_rating_notes'){
        $type = 'заметку';
    }
    if($data['action'] == 'give_rating_comment'){
        $type = 'комментарий';
        $name = 'гостя';
        if($data['subject_id']){
            $name = get_comment_author($data['object_id']);
        }
        
        $link = '<a href="/?una_comment_id='.$data['object_id'].'" title="Перейти к комментарию" rel="nofollow">'.$name.'</a>'; // get_comment_link TODO +5db
    } else if ($data['action'] == 'give_rating_products'){
        $type = 'товар';
    }
    
    $out = '<span class="una_action">Проголосовал</span> '.$rating. ' за '.$type.': ';
    $out .= $link;
    
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
function una_get_register($data){
    return '<span class="una_action">Зарегистрировался на сайте</span>';
}

// подтвердил регу
function una_get_confirm_register($data){
    return '<span class="una_action">Подтвердил регистрацию на сайте</span>';
}


// оставил комментарий
function una_get_add_comment($data){
    $other = unserialize($data['other_info']);
/* Array(
    [st] => 1                                       // status: 1 - одобрено, 0 - на модерации, spam - спам
    [pt] => post-group                              // post_type: тип записи
    [grid] => 63                                    // group_id: если группа то эти два добавляются: id группы
    [grn] => Группа путешественников во времени     // group_name: Название группы
    [par] => 1                                      // parent - флаг 1. Ответ на комментарий, а не просто комментарий
    [sbj_nm] => Путешественник во времени2          // subject_name - имя того, на чей камент отвечаем (его id пишется в subject_id)
) */
//vda($other['st']);
    $go_to = '';
    $gp_info = '';
    $type = ' к записи: ';
    if($other['pt'] == 'products'){
        $type = ' к товару: ';
    }
    $type_fin = $type . '"' . $data['object_name'] . '"';
    
    if($other['pt'] == 'post-group'){
        $gp_info = ', в группе ';
        $gp_info .= '<a href="/?una_group_url='.$other['grid'].'" title="Перейти" rel="nofollow">'.$other['grn'].'</a>';
    }
    
    $status = '';
    switch ($other['st']) {
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
    
    if($other['st'] == 1){ // комментарий опубликован, одобрен и есть на сайте
        $go_to = '<div class="una_goto_comments">';
            $go_to .= '<a href="/?una_comment_id='.$data['object_id'].'" title="Перейти" rel="nofollow">';
                $go_to .= '<span>Прочитать комментарий</span><i class="fa fa-angle-double-right" aria-hidden="true"></i>';
            $go_to .= '</a>';
        $go_to .= '</div>';
    } else {
        if( !empty($status) ) $go_to = ' <span class="una_post_status">('.$status.')</span>';
    }
    if( isset($other['par']) && $other['par'] && isset($other['sbj_nm']) && $other['sbj_nm'] ){ // если это ответ и не самому себе
        $do = 'Ответил ';
        $whom = 'на комментарий пользователя ';
        $link_author = '"'.$other['sbj_nm'].'"';
        
        if($data['subject_id'] > 0){ // и ответ зарегистрированному юзеру
            $link_author = '<a href="/?author='.$data['subject_id'].'" title="Перейти" rel="nofollow">"'.$other['sbj_nm'].'"</a>';
        }
        
        $out = '<span class="una_action">' . $do . $whom . '</span>' . $link_author . $type_fin . $gp_info;
    } else {
        $out = '<span class="una_action">Оставил комментарий</span>' . $type_fin . $gp_info;
    }
    
    return $out . $go_to;
}


// добавил запись
function una_get_add_post($data){
    $post_name = '"'.$data['object_name'].'"';
    $link = '<a href="/?p='.$data['object_id'].'" title="Перейти" rel="nofollow">'.$post_name.'</a>';
    
    $status = get_post_status($data['object_id']);
    if(!$status) $link = $post_name.'<span class="una_post_status">(удалено)</span>'; // удалено
    else if($status === 'trash'){
         $link = $post_name.'<span class="una_post_status">(удалено в корзину)</span>';
    }
    else if($status === 'draft'){
        $link = $post_name.'<span class="una_post_status">(черновик)</span>';
        if(current_user_can('edit_post', $data['object_id'])){
            $link = '<a href="/?p='.$data['object_id'].'" title="Перейти" rel="nofollow">'.$post_name.'</a><span class="una_post_status">(черновик)</span>';
            //$link = '<a href="'.get_permalink($data['object_id']).'" title="Перейти" rel="nofollow">'.$post_name.'</a><span class="una_post_status">(черновик)</span>';
        }
    }
    else if($status === 'pending'){
        $link = $post_name.'<span class="una_post_status">(на утверждении)</span>';
        if(current_user_can('edit_post', $data['object_id'])){
            $link = '<a href="/?p='.$data['object_id'].'" title="Перейти" rel="nofollow">'.$post_name.'</a><span class="una_post_status">(на утверждении)</span>';
        }
    }
    
    $type = 'запись';
    if($data['object_type'] === 'notes') $type = 'заметку';
    if($data['object_type'] === 'products') $type = 'товар';
    
    $out = '<span class="una_action">Добавил '.$type.':</span> '.$link;
    
    return $out;
}

// убрал запись в черновик
function una_get_add_draft($data){
    $out = '<span class="una_action">Убрал запись в черновик:</span> ';
    $out .= '"'.$data['object_name'].'"';
    
    return $out;
}

// удалил запись
function una_get_delete_post($data){
    $status = 'в корзину';
    $what = 'Автор удалил ';
    $type = 'запись';
    $link_author = '';
    if($data['action'] === 'delete_post_fully'){
        $status = 'навсегда';
    }
    if($data['object_type'] === 'notes') $type = 'заметку';
    if($data['object_type'] === 'products') $type = 'товар';
    if($data['subject_id']){
        $what = 'Удалил ';
        if($data['action'] === 'delete_post_fully'){
            $what = 'WP-Cron удалил ';
        }
        $link_author = '<span class="una_post_status una_post_author">- автор: <a href="/?author='.$data['subject_id'].'" title="Перейти" rel="nofollow">'.$data['other_info'].'</a></span>';
    }

    $out = '<span class="una_action">'.$what.$type.':</span> ';
    $out .= '"'.$data['object_name'].'"<span class="una_post_status">('.$status.')</span>'.$link_author;
    
    return $out;
}



// Подписка на юзера
function una_get_add_user_feed($data){
    $link_author = '<a href="/?author='.$data['subject_id'].'" title="Перейти" rel="nofollow">'.$data['object_name'].'</a>';
    $out = '<span class="una_action">Подписался на пользователя</span> '.$link_author;
    return $out;
}

// Отписка на юзера
function una_get_del_user_feed($data){
    $link_author = '<a href="/?author='.$data['subject_id'].'" title="Перейти" rel="nofollow">'.$data['object_name'].'</a>';
    $out = '<span class="una_action">Отписался от пользователя</span> '.$link_author;
    return $out;
}




// Добавил в черный список
function una_get_add_user_blacklist($data){
    $link_author = '<a href="/?author='.$data['subject_id'].'" title="Перейти" rel="nofollow">'.$data['object_name'].'</a>';
    $out = '<span class="una_action">Добавил пользователя</span> '.$link_author.' в черный список';
    return $out;
}

// Удалил из черного списка
function una_get_del_user_blacklist($data){
    $link_author = '<a href="/?author='.$data['subject_id'].'" title="Перейти" rel="nofollow">'.$data['object_name'].'</a>';
    $out = '<span class="una_action">Убрал пользователя</span> '.$link_author.' из черного списка';
    return $out;
}


// Создал группу
function una_get_create_group($data){
    $link = '"'.$data['object_name'].'"';
    if(!$data['other_info']){ // если группа удалена - то пишется в нее del. А так колонка пустая 
        $link = '<a href="/?una_group_url='.$data['object_id'].'" title="Перейти" rel="nofollow">"'.$data['object_name'].'"</a>';
        //$link = rcl_get_group_permalink($data['object_id']); // +1 db
    }
    
    $out = '<span class="una_action">Создал новую группу:</span> '.$link.'';
    return $out;
}

// удалил группу
function una_get_delete_group($data){
    $out = '<span class="una_action">Удалил группу:</span> "'.$data['object_name'].'"';
    return $out;
}



// вступил в группу/покинул группу
function una_get_user_in_out_group($data){
    $out = 'Вступил в группу: ';
    if($data['action'] == 'user_out_group'){
        $out = 'Покинул группу: ';
    }
    $link = '<a href="/?una_group_url='.$data['object_id'].'" title="Перейти" rel="nofollow">"'.$data['object_name'].'"</a>';
    return '<span class="una_action">'.$out.'</span>'.$link;
}


// создал тему на primeForum
function una_get_user_add_topic($data){
    $del = '';
    $link = '<a href="/?una_prime_forum_url='.$data['object_id'].'" title="Перейти" rel="nofollow">"'.$data['object_name'].'"</a>';
    if($data['other_info'] == 'del'){ // если группа удалена - то пишется в нее del. А так колонка пустая 
        $link = '"'.$data['object_name'].'"';
        $del = '<span class="una_post_status">(удалено)</span>';
    }
    
    $out = '<span class="una_action">Создал новую тему на форуме:</span> ' . $link . $del;

    return $out;
}


// удалил топик (тему на форуме)
function una_get_user_del_topic($data){
    $link_author = '';

    if($data['subject_id']){
        $link_author = '<span class="una_post_status una_post_author">- автор: <a href="/?author='.$data['subject_id'].'" title="Перейти" rel="nofollow">'.$data['other_info'].'</a></span>';
    }

    $out = '<span class="una_action">Удалил тему:</span> "'.$data['object_name'].'" с форума'.$link_author;
    
    return $out;
}















