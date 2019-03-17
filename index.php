<?php

/*

  ╔═╗╔╦╗╔═╗╔╦╗
  ║ ║ ║ ╠╣ ║║║ https://otshelnik-fm.ru
  ╚═╝ ╩ ╚  ╩ ╩


 * ***   Возможности:   ****
 * пишет когда пользователь залогинился через ВП
 * когда юзер залогинился через uLogin и какая сеть
 * когда зарегистрировался
 * если в реколл стоит подтверждение регистрации - то запишет как ее подтвердит
  ----убрал--- неверный вход (запишем под каким именем пытался войти)
 * неверная регистрация и причина
 * выход с сайта
 * при удалении юзера - запишем кто удалил его и очистим историю удаленного юзера
 * обновил настройки профиля
 * поставил рейтинг за:
  Проголосовал -10 за запись: для фида 2 без море
 * если это товар:
  Проголосовал +10 за товар: Кот в мешке
 * оставил комментарий:
  Оставил комментарий к записи: "для фида 2 без море"
 * если это комментарий к записе в группе - то выводит и название группы:
  Оставил комментарий к записи: "Я вижу как закат стёкла оконные плавит…" в группе "Скрытые возможности Теней!"
  ссылка будет короткой и ведет к самому комментарию и имя группы - ссылка на группу
 * если это комментарий к товару то и пишем:
  Оставил комментарий к товару: "Продам робота"
 * статус комментария если он отличается от опубликованного учитывается. И ссылка на коммент не выводится
 * при удалении комментария - удаляется он из таблицы
 * опубликовал запись:
  если модерации нет
  если модерация есть - пишется время и статус (add_post), но ссылка на запись не видна. Видна лишь тем кто имеет право редактировать запись (сам автор, редакторы и админ)
  если админ одобрил ее - время публикации не меняется, появляется ссылка на нее
 * убрал ее в черновики
 * удалил запись
 * полное удаление записи с сайта (очистил корзину или если корзина отключена, т.е. когда запись удаляется безвозвратно)
  если полное удаление записи - чистим всю историю по нему, кроме факта (строки) полного удаления
 * ловим когда при сохранении настроек меняют статус. Фиксируем это. При повторной смене статуса - время смены статуса переписывается
 * подписался на пользователя, отписался от него
  если юзер начинает тыкать подписаться/отписаться я стираю при подписке эти два поля и фиксирую новое событие. Избавляемся от дублей
 * добавление и удаление юзеров в черный список
  логика работы как у подписок - исключая тыканье добавить/убрать из блеклиста
 * добавлен в отдельный пункт (item) css класс определяющий что за тип данных выводится https://yadi.sk/i/oWgYPnmR3KJutL - так вы можете кастомизировать отдельное событие и обыграть стилями
 * ловим создание новой группы
 * удаление группы (админка)
  при удалении группы на строчку созданной группы вешаем маркер del - и наша система не будет на нее давать ссылку
 * ввел короткие ссылки для комментариев и для групп и для форума - это значительно сократило кол-во запросов к бд
 * вступил в группу/покинул ее/удалили из группы
 * создал тему на PrimeForum
  ссылка на топик формируется короткая - меньше запросов к бд
 * удалил тему с форума. Если тему удаляет не сам автор - то пишу чья тема была удалена
 * рейтинг за комментарий на PrimeForum
  ссылка на комментарий форума формируется короткая - меньше запросов к бд
 * ловим событие загрузки обложки в ЛК. При загрузки другой обложки дата события меняется
  это событие будет доступно в фильтре "Обновления"
 * ловим событие загрузки аватарки в ЛК
  это событие будет доступно в фильтре "Обновления"
 * ловим событие удаления аватарки
  событие видит автор и админ
  в этом случае удаляем событие загрузки аватарки - т.к. картинки нет, выводить нечего
  и если есть еще одно событие удаления аватарки - удалим его
 * создал тему на Asgaros Forum
  ссылка на топик формируется короткая - меньше запросов к бд
 * удалил тему с Asgaros Forum. Если тему удаляет не сам автор - то пишу чья тема была удалена
 * рейтинг за комментарий на Asgaros Forum (дополнение Asgaros Forum + WP-Recall)
  ссылка на комментарий форума формируется короткая - меньше запросов к бд
 * указал свой город, сменил город, удалил город (дополнение Country & User in Profile PRO)
 * установил день рождения, сменил дату рождения (дополнение Birthday in Profile)
 * запросил статистику по себе в чате (доп Bot User Info)
 * оформил подписку (или отменил её) на комментарии записей или форума (доп Subscription Two)
 * сменил урл кабинета (доп Pretty URL Author)
 * установил (сменил) описание группы (доп Group Recall)
 * смена статуса группы: открытая/закрытая (доп Group Recall)
 * пользователь забанен в группе (доп Group Recall)
 * у пользователя сменили роль в группе (доп Group Recall)
 * установил или сменил статус группы (доп Groups Theme RePlace)
 * установил или сменил аватарку группы (доп Groups Theme RePlace)
 * установил или сменил обложку группы (доп Groups Theme RePlace)

  @todo разобраться с login_failed их может быть нес-ко тысяч. Пока вырубил

 */



// БД
add_action( 'init', 'una_define_constant', 5 );
function una_define_constant() {
    if ( defined( 'UNA_DB' ) )
        return false;

    global $wpdb;

    define( 'UNA_DB', $wpdb->prefix . 'otfm_universe_activity' );
}

// подключим файлы
require_once 'inc/fires.php';                   // хуки
require_once 'inc/callbacks.php';               // колбэки
require_once 'inc/functions.php';               // все функции
require_once 'inc/integration.php';             // интеграции
require_once 'inc/addon-settings.php';          // настройки
require_once 'inc/class-una-query.php';         // класс регистрирущий нашу таблицу
require_once 'inc/class-una-shortcode.php';     // шорткод


/*
 * Интеграции
 *
 * пока набиваю так, как накопится критическая масса перепишу 💩
 */


// плагин "Asgaros Forum" https://wordpress.org/plugins/asgaros-forum/
if ( class_exists( 'AsgarosForum' ) ) {
    require_once 'integration/plugin-asgaros.php';
}

// доп "Asgaros Forum + WP-Recall" https://codeseller.ru/?p=13693
if ( rcl_exist_addon( 'rcl-asgaros' ) ) {
    require_once 'integration/addon-asgaros-forum-to-wp-recall.php';
}

//
if ( rcl_exist_addon( 'country-and-city-in-profile-pro' ) ) {
    require_once 'integration/addon-country-and-city-in-profile-pro.php';
}

// доп "Birthday in Profile" https://codeseller.ru/?p=13377
if ( rcl_exist_addon( 'birthday-in-profile' ) ) {
    require_once 'integration/addon-birthday-in-profile.php';
}

// доп "Bot User Info" https://codeseller.ru/?p=17458
if ( rcl_exist_addon( 'bot-user-info' ) ) {
    require_once 'integration/addon-bot-user-info.php';
}

// доп "Subscription Two" https://codeseller.ru/?p=16774
if ( rcl_exist_addon( 'subscription-two' ) ) {
    require_once 'integration/addon-subscription-two.php';
}

// доп "Pretty URL Author" https://codeseller.ru/?p=13784
if ( rcl_exist_addon( 'pretty-url-author' ) ) {
    require_once 'integration/addon-pretty-url-author.php';
}

// доп "Groups Theme RePlace"
if ( rcl_exist_addon( 'groups-theme-replace' ) ) {
    require_once 'integration/addon-groups-theme-replace.php';
}



