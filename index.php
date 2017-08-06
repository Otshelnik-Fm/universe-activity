<?php
/*
****   Возможности:   ****
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
    * вступил в группу/покинул ее
    * создал тему на PrimeForum
        ссылка на топик формируется короткая - меньше запросов к бд
    * удалил тему с форума. Если тему удаляет не сам автор - то пишу чья тема была удалена






TODO:
    разобраться с login_failed их может быть нес-ко тысяч. Пока вырубил

удаление аватарки: (нужен id юзера)
но надо бы при удалении авы получать старую аватарку. т.е. перед удалением аватрки нужен хук и и ловить старую фото
потом уже хук успешной загрузки авы. Или в одном хуке это ловить

 rcl_delete_avatar_action
 plugins\wp-recall\functions\supports\uploader-avatar.php


загрузка аватарки: (нужен id юзера) + то что выше

 rcl_avatar_upload
 plugins\wp-recall\functions\supports\uploader-avatar.php


загрузка обложки: (нужен id юзера)
 rcl_cover_upload
 plugins\wp-recall\functions\supports\uploader-cover.php

*/





// подключим файлы
require_once('inc/fires.php');                  // хуки
require_once('inc/callbacks.php');              // колбэки
require_once('inc/functions.php');              // все функции
require_once('inc/integration.php');            // интеграции
//require_once('inc/addon-settings.php');         // настройки
require_once('inc/class-una-query.php');        // класс регистрирущий нашу таблицу
require_once('inc/class-una-shortcode.php');    // шорткод




