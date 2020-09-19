== Установка/Обновление ==

<h2 style="text-align:center;color:#26901b;font-weight:bold;">Установка:</h2>
Т.к. это дополнение для WordPress плагина <a href="https://codeseller.ru/groups/plagin-wp-recall-lichnyj-kabinet-na-wordpress/" target="_blank">WP-Recall</a>, то оно устанавливается через <a href="https://codeseller.ru/obshhie-svedeniya-o-dopolneniyax-wp-recall/" target="_blank"><strong>менеджер дополнений WP-Recall</strong></a>.

1. В админке вашего сайта перейдите на страницу "WP-Recall" -> "Дополнения" и в самом верху нажмите на кнопку "Обзор", выберите .zip архив дополнения на вашем пк и нажмите кнопку "Установить".
2. В списке загруженных дополнений, на этой странице, найдите это дополнение, наведите на него курсор мыши и нажмите кнопку "Активировать". Или выберите чекбокс и в выпадающем списке действия выберите "Активировать". Нажмите применить.


<h2 style="text-align:center;color:#26901b;font-weight:bold;">Обновление:</h2>
Дополнение поддерживает <a href="https://codeseller.ru/avtomaticheskie-obnovleniya-dopolnenij-plagina-wp-recall/" target="_blank">автоматическое обновление</a> - два раза в день отправляются вашим сервером запросы на обновление.
Если в течении суток вы не видите обновления (а на странице дополнения вы видите что версия вышла новая), советую ознакомиться с этой <a href="https://codeseller.ru/post-group/rabota-wordpress-krona-cron-prinuditelnoe-vypolnenie-kron-zadach-dlya-wp-recall/" target="_blank">статьёй</a>



== Логика/Настройки ==

<h2 style="text-align:center;color:#26901b;font-weight:bold;">Список регистрируемых событий и логика:</h2>

&nbsp;&nbsp;&nbsp;&nbsp;Убрал под спойлер - т.к. много + примеры что пишет.

<details style="margin: 15px 0;">
<summary>
<h3>Список регистрируемых событий и логика</h3>
</summary>
<p>
    * пишет когда пользователь залогинился через ВП  
    * когда юзер залогинился через плагин uLogin и какая сеть  
    * когда зарегистрировался  
        &nbsp;&nbsp;&nbsp;&nbsp; если в WP-Recall стоит подтверждение регистрации - то запишет как ее подтвердит  
    * неверная регистрация и причина  
    * выход с сайта  
    * при удалении юзера - запишем кто удалил его и очистим историю удаленного юзера  
    * обновил настройки профиля  
    * поставил рейтинг за: <code>Проголосовал -10 за запись: для фида 2 без море</code>  
        &nbsp;&nbsp;&nbsp;&nbsp; если это товар: <code>Проголосовал +10 за товар: Кот в мешке</code>  
        &nbsp;&nbsp;&nbsp;&nbsp; если это группа: <code>Проголосовал +5 за запись: "Посёлок программистов", в группе Открытая 2019</code>  
    * оставил комментарий: <code>Оставил комментарий к записи: "для фида 2 без море"</code>  
        &nbsp;&nbsp;&nbsp;&nbsp; если это комментарий к записе в группе - то выводит и название группы: <code>Оставил комментарий к записи: "Я вижу как закат стёкла оконные плавит…" в группе "Скрытые возможности Теней!"</code>  
        &nbsp;&nbsp;&nbsp;&nbsp; ссылка будет короткой и ведет к самому комментарию, а имя группы - ссылка на группу  
        &nbsp;&nbsp;&nbsp;&nbsp; если это комментарий к товару то и пишем: <code>Оставил комментарий к товару: "Продам робота"</code>  
        &nbsp;&nbsp;&nbsp;&nbsp; статус комментария, если он отличается от опубликованного, учитывается. И ссылка на коммент не выводится  
    * при удалении комментария - удаляется он из таблицы  
    * опубликовал запись:  
        &nbsp;&nbsp;&nbsp;&nbsp; если модерации нет  
        &nbsp;&nbsp;&nbsp;&nbsp; если модерация есть - пишется время и статус (add_post), но ссылка на запись не видна. Видна лишь тем кто имеет право редактировать запись (сам автор, редакторы и админ)  
        &nbsp;&nbsp;&nbsp;&nbsp; если админ одобрил ее - время публикации не меняется, появляется ссылка на нее  
    * убрал ее в черновики  
    * удалил запись  
    * полное удаление записи с сайта (очистил корзину или если корзина отключена, т.е. когда запись удаляется безвозвратно)  
        &nbsp;&nbsp;&nbsp;&nbsp; если полное удаление записи - чистим всю историю по нему, кроме факта (строки) полного удаления  
    * ловим когда при сохранении настроек профиля меняют статус (description). Фиксируем это. При повторной смене статуса - время смены статуса переписывается  
    * подписался на пользователя, отписался от него  
        &nbsp;&nbsp;&nbsp;&nbsp; если юзер начинает тыкать "подписаться/отписаться" - я стираю при подписке эти два поля и фиксирую новое событие. Избавляемся от дублей  
    * добавление и удаление юзеров в черный список  
        &nbsp;&nbsp;&nbsp;&nbsp; логика работы как у подписок - исключая тыканье "добавить/убрать" из блеклиста  
    * создал тему на Prime-Forum  
        &nbsp;&nbsp;&nbsp;&nbsp; ссылка на топик формируется короткая - меньше запросов к бд  
    * удалил тему с форума Prime-Forum. Если тему удаляет не сам автор - то пишу чья тема была удалена  
    * рейтинг за комментарий на PrimeForum  
        &nbsp;&nbsp;&nbsp;&nbsp; ссылка на комментарий форума формируется короткая - меньше запросов к бд  
    * ловим событие загрузки обложки в ЛК. При загрузки другой обложки дата события меняется  
        &nbsp;&nbsp;&nbsp;&nbsp; это событие будет доступно в фильтре "Обновления"  
    * ловим событие загрузки аватарки в ЛК  
        &nbsp;&nbsp;&nbsp;&nbsp; это событие будет доступно в фильтре "Обновления"  
    * ловим событие удаления аватарки  
        &nbsp;&nbsp;&nbsp;&nbsp; событие видит автор и админ  
        &nbsp;&nbsp;&nbsp;&nbsp; в этом случае удаляем событие загрузки аватарки - т.к. картинки нет, выводить нечего  
        &nbsp;&nbsp;&nbsp;&nbsp; и если есть еще одно событие удаления аватарки - удалим его  
    * создал тему на Asgaros Forum  
        &nbsp;&nbsp;&nbsp;&nbsp; ссылка на топик формируется короткая - меньше запросов к бд  
    * удалил тему с Asgaros Forum. Если тему удаляет не сам автор - то пишу чья тема была удалена  
    * рейтинг за комментарий на Asgaros Forum (дополнение Asgaros Forum + WP-Recall)  
        &nbsp;&nbsp;&nbsp;&nbsp; ссылка на комментарий форума формируется короткая - меньше запросов к бд  
    * указал свой город, сменил город, удалил город (дополнение Country & User in Profile PRO)  
        &nbsp;&nbsp;&nbsp;&nbsp; Первые 2 видят залогиненные. Второе - только админ  
        &nbsp;&nbsp;&nbsp;&nbsp; установил город - указывается город  
        &nbsp;&nbsp;&nbsp;&nbsp; сменил город - указывается старый и новый город  
        &nbsp;&nbsp;&nbsp;&nbsp; удалил город - указывается старый город  
        &nbsp;&nbsp;&nbsp;&nbsp; эти события будут доступны и в кнопке-фильтре "Обновления"  
    * установил день рождения, сменил дату рождения (дополнение Birthday in Profile)  
        &nbsp;&nbsp;&nbsp;&nbsp; установил день рождения (событие видит автор)  
        &nbsp;&nbsp;&nbsp;&nbsp; сменил дату рождения (событие видит автор)  
        &nbsp;&nbsp;&nbsp;&nbsp; эти события выводятся также в кнопке-фильтре "Обновления"  
    * запросил статистику по себе в чате (доп Bot User Info)  
        &nbsp;&nbsp;&nbsp;&nbsp; пишет событие, когда пользователь запросил информацию по себе (событие видит админ)  
    * оформил подписку на комментарии записей и форума или удалил подписку (доп Subscription Two)  
        &nbsp;&nbsp;&nbsp;&nbsp; оформил подписку на комментарии записей или форума (событие видит залогиненный)  
        &nbsp;&nbsp;&nbsp;&nbsp; Причём для групп пишет: <code>Otshelnik-Fm в группе "Приют Отшельника", подписался на комментарии к записи: Кукла (The Inhabitant)(2016)</code>
        &nbsp;&nbsp;&nbsp;&nbsp; удалил подписку на комментарии записей или форума (событие видит админ)  
        &nbsp;&nbsp;&nbsp;&nbsp; это событие будет доступно в фильтре "Подписки"  
    * сменил урл кабинета (событие видит админ)(доп Pretty URL Author)  
    * поддержка событий дополнения Групп (Group Recall):  
        &nbsp;&nbsp;&nbsp;&nbsp; ловим создание новой группы  
        &nbsp;&nbsp;&nbsp;&nbsp; удаление группы (админка)  
        &nbsp;&nbsp;&nbsp;&nbsp; при удалении группы на строчку созданной группы вешаем маркер del - и наша система не будет на нее давать ссылку  
        &nbsp;&nbsp;&nbsp;&nbsp; вступил в группу/покинул ее/удалили из группы (если админ в списке пользователей группы удалил его из группы)  
        &nbsp;&nbsp;&nbsp;&nbsp; установил (сменил) описание группы (событие видят все)  
        &nbsp;&nbsp;&nbsp;&nbsp; смена статуса группы: открытая/закрытая  
        &nbsp;&nbsp;&nbsp;&nbsp; пользователь забанен в группе  
        &nbsp;&nbsp;&nbsp;&nbsp; у пользователя сменили роль в группе  
    * установил или сменил статус группы (событие видят все)(доп Groups Theme RePlace)  
    * установил или сменил аватарку группы (событие видят все)(доп Groups Theme RePlace)  
    * установил или сменил обложку группы (событие видят все)(доп Groups Theme RePlace)  
    * поддержка событий дополнения Подписок на новые записи группы (Group New Post Notify):  
        &nbsp;&nbsp;&nbsp;&nbsp; юзер подписался на уведомления о новых записях группы (событие видит автор)  
        &nbsp;&nbsp;&nbsp;&nbsp; юзер изменил тип уведомлений подписки (событие видит автор)  
        &nbsp;&nbsp;&nbsp;&nbsp; юзер удалил подписку (событие видит автор)  
        &nbsp;&nbsp;&nbsp;&nbsp; это событие будет доступно в фильтре "Подписки"   
        &nbsp;&nbsp;&nbsp;&nbsp; админ из управления подписками удаляет подписку юзера (событие видит админ) 
        &nbsp;&nbsp;&nbsp;&nbsp; Пишет: <code>Анжелика отменила подписку пользователю Надежда в группе Супер группа!</code>
        &nbsp;&nbsp;&nbsp;&nbsp; когда админ из управления подписками отправляет письмо - напоминание, о продолжении подписки на рассылку из групп (событие видит админ) 
        &nbsp;&nbsp;&nbsp;&nbsp; Пишет: <code>Анжелика отправила письмо (напоминание) - о продолжении подписки на рассылку из групп, пользователю Василий</code>
        &nbsp;&nbsp;&nbsp;&nbsp; когда прошел недельный дайджест по рассылке из групп (событие видит админ) 
        &nbsp;&nbsp;&nbsp;&nbsp; Пишет: <code>Недельный дайджест был успешно отправлен</code>
    * поддержка событий дополнения Закладок (Bookmarks):  
        &nbsp;&nbsp;&nbsp;&nbsp; добавил в закладки запись (событие видят все) и удалил из закладок (событие видит автор)  
        &nbsp;&nbsp;&nbsp;&nbsp; Пишет: <code>Добавил в закладки запись: Секретные материалы (The X-Files)(2016)(1 сезон)</code>
        &nbsp;&nbsp;&nbsp;&nbsp; И если в группе запись: <code>В группе "Кино", добавил в закладки к запись: Секретные материалы (The X-Files)(2016)(1 сезон)</code>
    * поддержка WordPress событий:
        &nbsp;&nbsp;&nbsp;&nbsp; неверные попытки смена пароля через форму (события видит админ): 
        &nbsp;&nbsp;&nbsp;&nbsp; Пишет: <code>Гость попытался сбросить пароль по почте: dfdfd@dfdfdf.com (запрос с ip: 11.11.111.111)</code>
        &nbsp;&nbsp;&nbsp;&nbsp; Пишет: <code>Гость попытался сбросить пароль по имени: sdsdsdsdsd (запрос с ip: 11.11.111.111)</code>
        &nbsp;&nbsp;&nbsp;&nbsp; а также валидные сбросы пароля через форму (событие видит админ):
        &nbsp;&nbsp;&nbsp;&nbsp; Пишет: <code>Гость запросил отправку письма на сброс пароля пользователя Василий (запрос с ip: 11.11.111.111)</code>
        &nbsp;&nbsp;&nbsp;&nbsp; Пишет: <code>Василий подтвердил изменение пароля через почту</code> (событие видит автор)  

    * Также пишется событие изменения пароля в ЛК (через поля "Новый пароль" и "повтор нового пароля" в настройках профиля) (событие видит автор)  
        &nbsp;&nbsp;&nbsp;&nbsp; Пишет: <code>Василий изменил пароль через личный кабинет</code>
<br>
</p>
</details>



<hr style="border: 1px solid #ddd;">


<h2 style="text-align:center;color:#26901b;font-weight:bold;">Список событий по группам:</h2>  

Активность дополнение пишет: как самого WordPress, WP-Recall плагина, сторонних плагинов и дополнений к WP-Recall.  
Важно понимать: что пока дополнения или плагины отключены - регистрация связанных с ними событий не производится.  
Ниже я собрал список регистрируемых событий по таким категориям.  


<h3>WordPress:</h3>  

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid rgb(229, 229, 229);">
<thead><tr>
<th>slug</th>
<th>действие</th>
<th>привилегия</th>
</tr></thead>
<tbody>
<tr>
<td>register</td>
<td>зарегистрировался</td>
<td>залогиненный</td>
</tr>
<tr>
<td>register_failed</td>
<td>неудачная регистрация</td>
<td>админ</td>
</tr>
<tr>
<td>delete_user</td>
<td>удалил юзера</td>
<td>админ</td>
</tr>
<tr>
<td>logged_in</td>
<td>пользователь вошел на сайт</td>
<td>гость</td>
</tr>
<tr>
<td>logged_out</td>
<td>пользователь вышел с сайта</td>
<td>автор</td>
</tr>
<tr>
<td>add_post</td>
<td>добавлена запись</td>
<td>гость</td>
</tr>
<tr>
<td>add_draft</td>
<td>добавил черновик</td>
<td>залогиненный</td>
</tr>
<tr>
<td>delete_post</td>
<td>удалил запись - в корзину</td>
<td>админ</td>
</tr>
<tr>
<td>delete_post_fully</td>
<td>удалил запись навсегда. Или автоочистка корзины по крону</td>
<td>админ</td>
</tr>
<tr>
<td>add_comment</td>
<td>добавлен комментарий</td>
<td>гость</td>
</tr>
<tr>
<td>profile_update</td>
<td>обновил настройки профиля</td>
<td>админ</td>
</tr>
<tr>
<td>change_status</td>
<td>юзер сменил свой статус</td>
<td>гость</td>
</tr>
<tr>
<td>pass_reset_fail</td>
<td>неверные попытки сброса пароля</td>
<td>админ</td>
</tr>
<tr>
<td>pass_reset_mail</td>
<td>успешная отправка письма с ссылкой сброса пароля</td>
<td>админ</td>
</tr>
<tr>
<td>pass_reset_confirm</td>
<td>подтвердил изменение пароля через почту</td>
<td>автор</td>
</tr>
</tbody></table>

<h3>Плагины:</h3>  

<h4>WP-Recall</h4>  

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid rgb(229, 229, 229);">
<thead><tr>
<th>slug</th>
<th>действие</th>
<th>привилегия</th>
</tr></thead>
<tbody>
<tr>
<td>add_cover</td>
<td>юзер добавил обложку в своём ЛК</td>
<td>гость</td>
</tr>
<tr>
<td>add_avatar</td>
<td>юзер добавил/сменил аватарку (локальная аватарка. не граватар)</td>
<td>гость</td>
</tr>
<tr>
<td>del_avatar</td>
<td>юзер удалил свой аватар (локальный, не граватар)</td>
<td>автор</td>
</tr>
<tr>
<td>confirm_register</td>
<td>подтвердил регистрацию</td>
<td>залогиненный</td>
</tr>
<tr>
<td>add_user_blacklist</td>
<td>добавил в черный список</td>
<td>залогиненный</td>
</tr>
<tr>
<td>del_user_blacklist</td>
<td>удалил из черного списка</td>
<td>залогиненный</td>
</tr>
<tr>
<td>pass_change</td>
<td>изменил пароль через ЛК</td>
<td>автор</td>
</tr>
</tbody></table>


дополнения из базовой части плагина:  

<strong><a href="https://codeseller.ru/products/rayting-recall-organizaciya-rejtingovoj-sistemy-wp-recall/" target="_blank">Rating System</a></strong>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid rgb(229, 229, 229);">
<thead><tr>
<th>slug</th>
<th>действие</th>
<th>привилегия</th>
</tr></thead>
<tbody>
<tr>
<td>give_rating_comment</td>
<td>рейтинг за комментарий</td>
<td>гость</td>
</tr>
<tr>
<td>give_rating_notes</td>
<td>рейтинг за заметку (+доп: Notes)</td>
<td>гость</td>
</tr>
<tr>
<td>give_rating_post</td>
<td>рейтинг за запись - тип post</td>
<td>гость</td>
</tr>
<tr>
<td>give_rating_forum-post</td>
<td>рейтинг за сообщение на Prime Forum (+доп: Prime Forum)</td>
<td>гость</td>
</tr>
<tr>
<td>give_rating_forum-page</td>
<td>рейтинг за сообщение на Asgaros Forum (+доп: Asgaros Forum + WP-Recall)</td>
<td>гость</td>
</tr>
<tr>
<td>give_rating_post-group</td>
<td>рейтинг за запись в группе - тип post-group (+доп: Groups)</td>
<td>гость</td>
</tr>
<tr>
<td>give_rating_products</td>
<td>рейтинг за товар - тип products (+доп: Commerce)</td>
<td>гость</td>
</tr>
</tbody></table>


<strong><a href="https://codeseller.ru/products/feed-recall-dopolnenie-wp-recall-dlya-formirovaniya-feed-lenty-novostej-polzovatelya/" target="_blank">Feed</a></strong>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid rgb(229, 229, 229);">
<thead><tr>
<th>slug</th>
<th>действие</th>
<th>привилегия</th>
</tr></thead>
<tbody>
<tr>
<td>add_user_feed</td>
<td>подписался на юзера</td>
<td>гость</td>
</tr>
<tr>
<td>del_user_feed</td>
<td>отписался от юзера</td>
<td>залогиненный</td>
</tr>
</tbody></table>


<strong><a href="https://codeseller.ru/products/groups-recall-organizaciya-grupp-na-sajte-s-pomoshhyu-wp-recall/" target="_blank">Groups Recall</a></strong>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid rgb(229, 229, 229);">
<thead><tr>
<th>slug</th>
<th>действие</th>
<th>привилегия</th>
</tr></thead>
<tbody>
<tr>
<td>create_group</td>
<td>создал группу</td>
<td>гость</td>
</tr>
<tr>
<td>delete_group</td>
<td>удалил группу</td>
<td>админ</td>
</tr>
<tr>
<td>user_in_group</td>
<td>юзер вступил в группу</td>
<td>гость</td>
</tr>
<tr>
<td>group_change_desc</td>
<td>установил или сменил описание группы</td>
<td>гость</td>
</tr>
<tr>
<td>user_out_group</td>
<td>вышел из группы/удалили из группы</td>
<td>залогиненный</td>
</tr>
<tr>
<td>group_user_ban</td>
<td>пользователя забанили в группе</td>
<td>залогиненный</td>
</tr>
<tr>
<td>group_user_role</td>
<td>пользователю назначили роль в группе</td>
<td>залогиненный</td>
</tr>
<tr>
<td>group_is_closed</td>
<td>смена статуса группы: открытая/закрытая</td>
<td>залогиненный</td>
</tr>
</tbody></table>


<strong><a href="https://codeseller.ru/products/primeforum/" target="_blank">PrimeForum</a></strong>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid rgb(229, 229, 229);">
<thead><tr>
<th>slug</th>
<th>действие</th>
<th>привилегия</th>
</tr></thead>
<tbody>
<tr>
<td>pfm_add_topic</td>
<td>создана новая тема на Prime Forum</td>
<td>гость</td>
</tr>
<tr>
<td>pfm_del_topic</td>
<td>удалил тему с форума (Prime Forum)</td>
<td>админ</td>
</tr>
</tbody></table>

Другие плагины:

<h4>uLogin</h4>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid rgb(229, 229, 229);">
<thead><tr>
<th>slug</th>
<th>действие</th>
<th>привилегия</th>
</tr></thead>
<tbody>
<tr>
<td>logged_in_ulogin</td>
<td>вошел на сайт и через какую сеть</td>
<td>гость</td>
</tr>
</tbody></table>


<h4>Asgaros Forum</h4>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid rgb(229, 229, 229);">
<thead><tr>
<th>slug</th>
<th>действие</th>
<th>привилегия</th>
</tr></thead>
<tbody>
<tr>
<td>asgrs_add_topic</td>
<td>создана новая тема на Asgaros Forum</td>
<td>гость</td>
</tr>
<tr>
<td>asgrs_del_topic</td>
<td>удалил тему с форума (Asgaros Forum)</td>
<td>админ</td>
</tr>
</tbody></table>


<h3>Дополнения WP-Recall</h3>

<strong><a href="https://codeseller.ru/products/birthday-in-profile/" target="_blank">Birthday in Profile</a></strong>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid rgb(229, 229, 229);">
<thead><tr>
<th>slug</th>
<th>действие</th>
<th>привилегия</th>
</tr></thead>
<tbody>
<tr>
<td>bip_add_dob</td>
<td>установил день рождения</td>
<td>автор</td>
</tr>
<tr>
<td>bip_change_dob</td>
<td>сменил дату рождения</td>
<td>автор</td>
</tr>
</tbody></table>


<strong><a href="https://codeseller.ru/products/bookmarks-recall-polzovatelskie-zakladki-s-wp-recall/" target="_blank">Bookmarks Recall</a></strong>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid rgb(229, 229, 229);">
<thead><tr>
<th>slug</th>
<th>действие</th>
<th>привилегия</th>
</tr></thead>
<tbody>
<tr>
<td>bkmrk_add</td>
<td>добавил в закладки</td>
<td>гость</td>
</tr>
<tr>
<td>bkmrk_del</td>
<td>удалил из закладок</td>
<td>автор</td>
</tr>
</tbody></table>


<strong><a href="https://codeseller.ru/products/bot-user-info/" target="_blank">Bot User Info</a></strong>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid rgb(229, 229, 229);">
<thead><tr>
<th>slug</th>
<th>действие</th>
<th>привилегия</th>
</tr></thead>
<tbody>
<tr>
<td>bui_get_info</td>
<td>запросил статистику в чате</td>
<td>админ</td>
</tr>
</tbody></table>


<strong>Country & User in Profile PRO</strong>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid rgb(229, 229, 229);">
<thead><tr>
<th>slug</th>
<th>действие</th>
<th>привилегия</th>
</tr></thead>
<tbody>
<tr>
<td>cpp_add_city</td>
<td>добавил город</td>
<td>залогиненный</td>
</tr>
<tr>
<td>cpp_change_city</td>
<td>сменил город</td>
<td>залогиненный</td>
</tr>
<tr>
<td>cpp_del_city</td>
<td>удалил город</td>
<td>админ</td>
</tr>
</tbody></table>


<strong><a href="https://codeseller.ru/products/group-new-post-notify/" target="_blank">Group New Post Notify</a></strong>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid rgb(229, 229, 229);">
<thead><tr>
<th>slug</th>
<th>действие</th>
<th>привилегия</th>
</tr></thead>
<tbody>
<tr>
<td>add_group_notify</td>
<td>подписался на уведомления о новых записях группы</td>
<td>автор</td>
</tr>
<tr>
<td>change_group_notify</td>
<td>изменил тип уведомлений подписки</td>
<td>автор</td>
</tr>
<tr>
<td>del_group_notify</td>
<td>юзер удалил подписку</td>
<td>автор</td>
</tr>
<tr>
<td>unsub_group_notify</td>
<td>админ удалил подписку у пользователя</td>
<td>админ</td>
</tr>
<tr>
<td>verify_group_notify</td>
<td>админ отправил письмо с напоминанием о продолжении подписки</td>
<td>админ</td>
</tr>
<tr>
<td>send_group_digest</td>
<td>успешная отправка недельного дайджеста</td>
<td>админ</td>
</tr>
</tbody></table>


<strong><a href="https://codeseller.ru/products/groups-theme-replace/" target="_blank">Groups Theme RePlace</a></strong>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid rgb(229, 229, 229);">
<thead><tr>
<th>slug</th>
<th>действие</th>
<th>привилегия</th>
</tr></thead>
<tbody>
<tr>
<td>group_change_exc</td>
<td>установил или сменил статус группы</td>
<td>гость</td>
</tr>
<tr>
<td>add_group_avatar</td>
<td>установил или сменил аватарку группы</td>
<td>гость</td>
</tr>
<tr>
<td>add_group_cover</td>
<td>установил или сменил обложку группы</td>
<td>гость</td>
</tr>
</tbody></table>


<strong><a href="https://codeseller.ru/products/pretty-url-author/" target="_blank">Pretty URL Author</a></strong>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid rgb(229, 229, 229);">
<thead><tr>
<th>slug</th>
<th>действие</th>
<th>привилегия</th>
</tr></thead>
<tbody>
<tr>
<td>pua_change_url</td>
<td>сменил урл кабинета</td>
<td>админ</td>
</tr>
</tbody></table>


<strong><a href="https://codeseller.ru/products/subscription-two/" target="_blank">Subscription Two</a></strong>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid rgb(229, 229, 229);">
<thead><tr>
<th>slug</th>
<th>действие</th>
<th>привилегия</th>
</tr></thead>
<tbody>
<tr>
<td>sbt_add_subs</td>
<td>оформил подписку на комментарии записей или форума</td>
<td>залогиненный</td>
</tr>
<tr>
<td>sbt_del_subs</td>
<td>удалил подписку на комментарии записей или форума</td>
<td>автор</td>
</tr>
</tbody></table>

<hr style="border: 1px solid #ddd;">

<h2 style="text-align:center;color:#26901b;font-weight:bold;">События и привилегии:</h2>

Дополнение позволяет разным типам пользователей видеть разные события. Так что все события видит только админ, а гость видит минимум - у него есть мотивация зарегистрироваться или войти на сайт чтобы видет больше событий.  
Ниже список все объясняет.  

<h3>Привилегии:</h3>
<code>Гость</code> - пользователь не вошедший на сайт. Если у события нет привилегии доступа - значит видно всем, начиная с гостя. 
<code>Залогинен</code> - 'logged' 
<code>Автор</code> (в своем ЛК - это если передан атрибут шорткода include_users="author_lk") - 'logged' и 'author' 
<code>Админ</code> - 'logged', 'author', 'admin' 

<h3>События:</h3>

<h4>Гость видит:</h4>
 
<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid #e5e5e5;">
<thead><tr>
<th>slug</th>
<th>действие</th>
</tr></thead><tbody>
<tr>
<td>logged_in</td>
<td>когда юзер вошел на сайт</td>
</tr>
<tr>
<td>logged_in_ulogin</td>
<td>когда юзер вошел через плагин u-login и через какую сеть</td>
</tr>
<tr>
<td>add_comment</td>
<td>добавлен комментарий</td>
</tr><tr>
<td>add_post</td>
<td>добавлена запись</td>
</tr><tr>
<td>change_status</td>
<td>юзер сменил свой статус</td>
</tr><tr>
<td>give_rating_comment</td>
<td>рейтинг за комментарий</td>
</tr><tr>
<td>give_rating_notes</td>
<td>рейтинг за заметку</td>
</tr>
<tr>
<td>give_rating_post</td>
<td>рейтинг за запись - тип post</td>
</tr>
<tr>
<td>give_rating_forum-post</td>
<td>рейтинг за сообщение на Prime Forum</td>
</tr>
<tr>
<td>give_rating_forum-page</td>
<td>рейтинг за сообщение на Asgaros Forum (дополнение Asgaros Forum + WP-Recall)</td>
</tr>
<tr>
<td>give_rating_post-group</td>
<td>рейтинг за запись в группе - тип post-group</td>
</tr><tr>
<td>give_rating_products</td>
<td>рейтинг за товар - тип products</td>
</tr><tr>
<td>add_user_feed</td>
<td>подписался на юзера</td>
</tr><tr>
<td>create_group</td>
<td>создал группу</td>
</tr><tr>
<td>user_in_group</td>
<td>юзер вступил в группу</td>
</tr><tr>
<td>pfm_add_topic</td>
<td>создана новая тема на Prime Forum</td>
</tr>
<tr>
<td>add_cover</td>
<td>юзер добавил обложку в своём ЛК</td>
</tr>
<tr>
<td>add_avatar</td>
<td>юзер добавил (сменил) аватарку (локальная аватарка. не граватар)</td>
</tr>
<tr>
<td>asgrs_add_topic</td>
<td>создана новая тема на Asgaros Forum</td>
</tr>
<tr>
<td>group_change_desc</td>
<td>установил или сменил описание группы</td>
</tr>
<tr>
<td>group_change_exc</td>
<td>установил или сменил статус группы (дополнение Groups Theme RePlace)</td>
</tr>
<tr>
<td>add_group_avatar</td>
<td>установил или сменил аватарку группы (дополнение Groups Theme RePlace)</td>
</tr>
<tr>
<td>add_group_cover</td>
<td>установил или сменил обложку группы (дополнение Groups Theme RePlace)</td>
</tr>
<tr>
<td>bkmrk_add</td>
<td>добавил в закладки (дополнение Bookmarks)</td>
</tr>
</tbody></table>


<h4>Залогиненый видит: те что выше, плюс:</h4>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid #e5e5e5;">
<thead><tr>
<th>slug</th>
<th>действие</th>
</tr></thead><tbody>
<tr>
<td>add_draft</td>
<td>добавил черновик</td>
</tr><tr>
<td>confirm_register</td>
<td>подтвердил регистрацию</td>
</tr><tr>
<td>register</td>
<td>зарегистрировался</td>
</tr><tr>
<td>del_user_feed</td>
<td>отписался от юзера</td>
</tr><tr>
<td>add_user_blacklist</td>
<td>добавил в черный список</td>
</tr><tr>
<td>del_user_blacklist</td>
<td>удалил из черного списка</td>
</tr>
<tr>
<td>user_out_group</td>
<td>вышел из группы/удалили из группы</td>
</tr>
<tr>
<td>group_user_ban</td>
<td>пользователя забанили в группе</td>
</tr>
<tr>
<td>group_user_role</td>
<td>пользователю назначили роль в группе</td>
</tr>
<tr>
<td>group_is_closed</td>
<td>смена статуса группы: открытая/закрытая</td>
</tr>
<tr>
<td>cpp_add_city</td>
<td>добавил город (Country & User in Profile PRO)</td>
</tr>
<tr>
<td>cpp_change_city</td>
<td>сменил город (Country & User in Profile PRO)</td>
</tr>
<tr>
<td>sbt_add_subs</td>
<td>оформил подписку на комментарии записей или форума (Subscription Two)</td>
</tr>
</tbody></table>

<h4>Автор видит: все что выше, плюс:</h4>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid #e5e5e5;">
<thead><tr>
<th>slug</th>
<th>действие</th>
</tr></thead><tbody>
<tr>
<td>logged_out</td>
<td>когда он вышел с сайта</td>
</tr>
<tr>
<td>del_avatar</td>
<td>когда он удалил свой аватар (локальный, не граватар)</td>
</tr>
<tr>
<td>add_group_notify</td>
<td>подписался на уведомления о новых записях группы (Group New Post Notify)</td>
</tr>
<tr>
<td>change_group_notify</td>
<td>изменил тип уведомлений подписки (Group New Post Notify)</td>
</tr>
<tr>
<td>del_group_notify</td>
<td>удалил подписку (Group New Post Notify)</td>
</tr>
<tr>
<td>sbt_del_subs</td>
<td>удалил подписку на комментарии записей или форума (Subscription Two)</td>
</tr>
<tr>
<td>bip_add_dob</td>
<td>установил день рождения (Birthday in Profile)</td>
</tr>
<tr>
<td>bkmrk_del</td>
<td>удалил из закладок (дополнение Bookmarks)</td>
</tr>
<tr>
<td>pass_reset_confirm</td>
<td>подтвердил изменение пароля через почту</td>
</tr>
<tr>
<td>pass_change</td>
<td>изменил пароль через ЛК</td>
</tr>

</tbody></table> 

<h4>Админ видит: все что выше, плюс:</h4>

<table style="padding: 5px; border-spacing: 5px; margin: 10px; border: 1px solid #e5e5e5;">
<thead><tr>
<th>slug</th>
<th>действие</th>
</tr></thead><tbody>
<tr>
<td>delete_post</td>
<td>удалил запись - в корзину</td>
</tr><tr>
<td>delete_post_fully</td>
<td>удалил запись навсегда. Если это автоочистка корзины - пишет "wp-cron"</td>
</tr><tr>
<td>delete_user</td>
<td>удалил юзера</td>
</tr><tr>
<td>profile_update</td>
<td>обновил настройки профиля</td>
</tr><tr>
<td>register_failed</td>
<td>неудачная регистрация</td>
</tr><tr>
<td>delete_group</td>
<td>удалил группу</td>
</tr><tr>
<td>pfm_del_topic</td>
<td>удалил тему с форума (Prime Forum)</td>
</tr>
<tr>
<td>asgrs_del_topic</td>
<td>удалил тему с форума (Asgaros Forum)</td>
</tr>
<tr>
<td>cpp_del_city</td>
<td>удалил город (Country & User in Profile PRO)</td>
</tr>
<tr>
<td>bip_change_dob</td>
<td>сменил дату рождения (Birthday in Profile)</td>
</tr>
<tr>
<td>bui_get_info</td>
<td>запросил статистику в чате (Bot User Info)</td>
</tr>
<tr>
<td>pua_change_url</td>
<td>сменил урл кабинета (Pretty URL Author)</td>
</tr>
<tr>
<td>pass_reset_fail</td>
<td>неверные попытки сброса пароля</td>
</tr>
<tr>
<td>pass_reset_mail</td>
<td>успешная отправка письма с ссылкой сброса пароля</td>
</tr>
<tr>
<td>unsub_group_notify</td>
<td>админ удалил подписку у пользователя (Group New Post Notify)</td>
</tr>
<tr>
<td>verify_group_notify</td>
<td>админ отправил письмо с напоминанием о продолжении подписки (Group New Post Notify)</td>
</tr>
<tr>
<td>send_group_digest</td>
<td>успешная отправка недельного дайджеста (Group New Post Notify)</td>
</tr>
</tbody></table>

<hr style="border: 1px solid #ddd;">

<h2 style="text-align:center;color:#26901b;font-weight:bold;">Шорткод:</h2>
Дополнение автоматически ничего нигде не выводит. За исключением поддержки дополнения <a href="https://codeseller.ru/products/user-info-tab/" target="_blank">User Info Tab</a> - но без постраничной навигации (там выводит последние 30 событий пользователя)  
(чтобы у User Info Tab была постраничная навигация нужно поставить дополнение <a href="https://codeseller.ru/products/universe-activity-extended/" target="_blank">Universe Activity Extended</a>)  

Итак - всё выводим с помощью шорткода: 
```
[otfm_universe filter=1]
```

<h3>Дополнительные атрибуты шорткода:</h3>

<strong>filter</strong> - показывать кнопки фильтра. Поставьте "1" чтобы выводить сверху фильтр по событиям (по умолчанию 0). Над активностью выведется 6-ть кнопок: Все, Публикации, Комментарии, Рейтинг, Обновления, Подписки    

<strong>number</strong> - количество событий на странице (по умолчанию 30). Поставьте "-1" чтобы вывести все. Для постраничной навигации вам нужно дополнение <a href="https://codeseller.ru/products/universe-activity-extended/" target="_blank">Universe Activity Extended</a>  

<strong>include_actions</strong> - включить эти события. Через запятую (события -slug- на английском смотрите выше в "Событиях и привилегиях" или "Список событий по группам"). Ничего не вписывайте если хотите вывести их все.  

<strong>exclude_actions</strong> - исключить события (нельзя в атрибутах одновременно использовать include_actions и exclude_actions. Что-то одно)  

<strong>include_users</strong> - включая юзеров. Через запятую (id юзеров - число). 
В личном кабинете в произвольной вкладке WP-Recall допустимо вписать <code>author_lk</code> и система подставит туда id автора кабинета.  
За пределами ЛК используйте <code>current</code> - система подставит туда id текущего авторизованного юзера. 

<strong>events_count</strong> - верхний счетчик показывающий кол-во событий (по умолчанию значение "1" - показывать). <code>events_count="0"</code> - отключит его

<strong>class</strong> - wrapper (обёртка) css class главного блока (<a href="https://yadi.sk/i/f1OvpO_E3LmcZh" target="_blank">скриншот</a>). Например чтобы вы создали свой дизайн вывода. На основе этого атрибута уже подготовлено несколько значений:  
(если не указан - то дизайн будет самый минималистичный)  
<code>"una_zebra"</code> - простой зеброй  
<code>"una_basic"</code> - базовый  
<code>"una_modern"</code> - модерн стиль  
<code>"una_card"</code> - стиль карточкой  

<h4>Примеры:</h4>
1. Выведем модерновый стиль, фильтр и 40 записей:  
<code>[otfm_universe class="una_modern" number="40" filter=1]</code>

2. Выведем, создав в админке вашего сайта: "WP-Recall" -> "Менеджер вкладок" - вкладку в ЛК, чтобы она показывала только события автора кабинета:  
<code>[otfm_universe filter=1 include_users="author_lk"]</code>

3. Выведем все события: комментарии и рейтинг за них. Без фильтра сверху:  
<code>[otfm_universe class="una_basic" number="-1" include_actions="add_comment,give_rating_comment"]</code>

4. Выведем все рейтинги и стилизуем базовым стилем:  
<code>[otfm_universe class="una_basic" include_actions="give_rating_comment,give_rating_notes,give_rating_post,give_rating_post-group,give_rating_products,give_rating_forum-post,give_rating_forum-page"]</code>

5. Выведем активность входа текущего пользователя:
<code>[otfm_universe include_actions="logged_in" include_users="current"]</code>

<hr style="border: 1px solid #ddd;">

<h2 style="text-align:center;color:#26901b;font-weight:bold;">Какие события включены в фильтр:</h2>
<code>"Публикации"</code> - add_post  
<code>"Комментарии"</code> - add_comment  
<code>"Рейтинг"</code> - give_rating_comment,give_rating_notes,give_rating_post,give_rating_forum-page,give_rating_post-group,give_rating_products,give_rating_forum-post   
<code>"Обновления"</code> - change_status,profile_update,create_group,user_in_group,pfm_add_topic,asgrs_add_topic,add_cover,add_avatar,add_group_avatar,group_change_exc,add_group_cover,cpp_add_city,cpp_change_city,bip_add_dob,bip_change_dob  
<code>"Подписки"</code> - add_user_feed,sbt_add_subs,sbt_del_subs,add_group_notify,change_group_notify,del_group_notify  





== FAQ ==

<h2 style="text-align:center;color:#26901b;font-weight:bold;">Установил дополнение - не вижу ничего</h2>
- Нужно вывести шорткодом. Смотри описание шорткода в вкладке "Логика/Настройки" и конечно же должно быть записано хоть одно событие в базу - админ видит их все.  

<hr style="border:1px solid #ddd;margin:18px;">
  
<h2 style="text-align:center;color:#26901b;font-weight:bold;">Я вижу не все события из последних. Некоторые скрыты</h2>
- У дополнения есть система событий и привилегий. Читай в вкладке "Логика/Настройки" - <strong>"События и привилегии"</strong>

<hr style="border:1px solid #ddd;margin:18px;">
  
<h2 style="text-align:center;color:#26901b;font-weight:bold;">Установил, вывел. Пишет счетчик: событий 100, но я вижу только 30</h2>
- По умолчанию и выводит 30. Нужно вывести все - ставь в атрибут шорткода <code>number="-1"</code>. 
Нужно вывести 100 - ставь в атрибут шорткода <code>number="100"</code>. 
Нужна постраничная навигация ставь дополнение <a href="https://codeseller.ru/products/universe-activity-extended/" target="_blank">Universe Activity Extended</a> (читай пункт <strong>"Ограничения"</strong> в вкладке "Описание")  

<hr style="border:1px solid #ddd;margin:18px;">
  
<h2 style="text-align:center;color:#26901b;font-weight:bold;">Как вывести активность пользователя в его личном кабинете?</h2>
- Читай в вкладке "Логика/Настройки" - <strong>"Шорткод"</strong>. Второй пример.  

<hr style="border:1px solid #ddd;margin:18px;">
  
<h2 style="text-align:center;color:#26901b;font-weight:bold;">Вывел фильтр - но я не вижу рейтинг и подписки</h2>
- Базовые дополнения "Rating System (Система рейтинга)" и "Feed (Подписки)" должны быть у вас активны

<hr style="border:1px solid #ddd;margin:18px;">

<h2 style="text-align:center;color:#26901b;font-weight:bold;">У дополнения есть настройки?</h2>
- Да. В админке: "WP-Recall" -> "Настройки Universe Activity"  

<hr style="border:1px solid #ddd;margin:18px;">  

<h2 style="text-align:center;color:#26901b;font-weight:bold;">А в какой таблице в базе данных хранятся события?</h2>
- Смотрите таблицу: <strong>wp_otfm_universe_activity</strong>

<hr style="border:1px solid #ddd;margin:18px;">

<h2 style="text-align:center;color:#26901b;font-weight:bold;">Как в лайтбоксе увеличивать аватарки и обложки? -открывает картинку в отдельной вкладке</h2>
- Ставьте дополнение для просмотра увеличенных изображений <a href="https://codeseller.ru/products/magnific-popup-recall/" target="_blank">Magnific Popup Recall</a>

<hr style="border:1px solid #ddd;margin:18px;">

<h2 style="text-align:center;color:#26901b;font-weight:bold;">Удалив это дополнение я потеряю данные о активности?</h2>
- При удалении дополнения через менеджер дополнений - он за собой удалит свою таблицу, в которой он хранит пользовательскую активность.  

<hr style="border:1px solid #ddd;margin:18px;">

<h2 style="text-align:center;color:#26901b;font-weight:bold;">Вписал шорткод в менеджере вкладок. Поставил галку на "кеширование" и все разъехалось (стили не загружаются)</h2>
- При кешировании вкладки (когда html из кеша отдается) дело до функции шорткода не доходит. Поэтому стили не загружаются (а стили у нас грузятся только там где требуется)
Значит вам нужно вручную вызвать нужные стилевые файлы для нужной вкладки.

<strong>Решение проблемы:</strong>
<strong>1-вариант:</strong> Отключить поддержку кеширования у вкладки. Это самый простой и надежный способ. Больше ничего делать не нужно.

<strong>2-вариант:</strong>
Вам нужно открыть в админке в "Менеджере вкладок" вкладку с шорткодом и посмотреть "Идентификатор вкладки". В моем случае он <code>aktivnost_89</code>
Вписать в ваш functions.php следующий сниппет (отредактируйте под свой случай):
```
// Universe Activity. ручной старт когда вкладка закеширована
function otfm_una_manual_load_styles(){
    if(!rcl_exist_addon('universe-activity')) return false; // наш доп не активирован

    una_manual_start($class = 'author_lk'); // передаем сюда из шорткода атрибут class. Или, если вызываете шорткод для конкретного юзера, впишите author_lk
}
add_action('rcl_construct_aktivnost_89_tab', 'otfm_una_manual_load_styles'); // вместо aktivnost_89 - вписываем свой id вкладки на котором вызываете шорткод
```

- обратите внимание на формирование динамического хука rcl_construct_<strong>aktivnost_89</strong>_tab вместо <strong>aktivnost_89</strong> вписывайте свой идентификатор вкладки
- в функцию на 5 строке передавайте значение из атрибута class шорткода. А если вы просто выводите активность конкретного юзера - то впишите author_lk
таким образом загрузится основной стилевой файл и стили для конкретного вызова (author_lk - значит файл будет загружаться una_one_user.css)

<hr style="border:1px solid #ddd;margin:18px;">

<h2 style="text-align:center;color:#26901b;font-weight:bold;">Как исключить post_type - (типы записей WordPress) из логгирования?</h2>
- исключить ненужный для записи post_type в базу можно следующим сниппетом:
```
add_filter( 'una_exclude_post_types', 'exs1_exclude_post_type', 10 );
function exs1_exclude_post_type( $exclude_post_types ) {
    $exclude_post_types[] = 'media_slider';

    return $exclude_post_types;
}
```

- после того как сниппет вписали - с этого момента этот тип записи перестанет логгироваться. Проверьте опубликовав что-нибудь.

<hr style="border:1px solid #ddd;margin:18px;">

<h2 style="text-align:center;color:#26901b;font-weight:bold;">Вписал сниппет выше - но этот тип записи так и выводится. Почему?</h2>
Выводится не этот, а тот, что был записан - когда вы еще не вписывали никакой сниппет на запрет логгирования.
Находите в БД таблицу активности wp_otfm_universe_activity и в поиске по колонке object_type находите и удаляете эти события.


<hr style="border:1px solid #ddd;margin:18px;">

<h2 style="text-align:center;color:#26901b;font-weight:bold;">События каких плагинов и дополнений он пишет?</h2>

Читай в вкладке "Логика/Настройки" - "Список событий по группам"
- там более подробно описаны а плагины/дополнения и что из них пишет.




== Changelog ==
= 2020-09-19 =
v0.80
* работа с WP-Recall 16.23.20
* поддержка браузерного lazy load
* исправил неверное получение пути до аватарки и обложки (они могут быть png-формата)
* переход на апи реколл кнопок
* переход на апи реколл нотисов



= 2020-04-08 =
v0.70
* Исправлена ошибка с зарузкой стилей в ЛК если стоял user info tab и было включено кеширование реколл (спасибо за репорт Garry)


= 2020-02-29 =
v0.60
* работа с WP-Recall 16.18.7

* добавлена функция <code>una_get_username( $user_id, $link = false )</code>
- по переданному id пользователя выводит имя. Если во 2-м аргументе передать 1, то имя вернется обёрнутое в короткую ссылку ведущую в ЛК

* Добавлена поддержка событий дополнения Group New Post Notify:  
когда админ из управления подписками удаляет подписку юзера (событие видит админ) 
Пример: Анжелика отменила подписку пользователю Надежда в группе Супер группа!

когда админ из управления подписками отправляет письмо - напоминание, о продолжении подписки на рассылку из групп (событие видит админ) 
Пример: Анжелика отправила письмо (напоминание) - о продолжении подписки на рассылку из групп, пользователю Василий

когда прошел недельный дайджест по рассылке из групп (событие видит админ) 
Пример: Недельный дайджест был успешно отправлен
  
* Добавлен лог событий WordPress:
- неверные попытки смена пароля через форму: 
Пример: "Гость попытался сбросить пароль по почте: dfdfd@dfdfdf.com (запрос с ip: 11.11.111.111)"
Пример: "Гость попытался сбросить пароль по имени: sdsdsdsdsd (запрос с ip: 11.11.111.111)"
события видит админ

- а также валидные сбросы пароля через форму:
Пример: "Гость запросил отправку письма на сброс пароля пользователя Василий (запрос с ip: 11.11.111.111)"
событие видит админ

Пример: "Василий подтвердил изменение пароля через почту"
событие видит автор и выше

* Также пишется событие изменения пароля в ЛК (через поля "Новый пароль" и "повтор нового пароля" в настройках профиля)
Пример: "Василий изменил пароль через личный кабинет"
событие видит автор и выше

* Исправлено неверное поведение сортировки, когда события шли совпадающие до секунды
* Исправлен баг выхода. Не писалось кто вышел (изменения хука в WordPress 5.3 - <a href="https://core.trac.wordpress.org/ticket/35488" target="_blank">тикет</a>)


= 2020-01-29 =
v0.55  
* Поддержка дополнения закладок (Bookmarks Recall) версии v1.16.2
там был добавлен хук срабатывающий при удалении закладки. Теперь доп пишет и это событие.


= 2019-12-11 =
v0.54  
* Небольшие правки в стилях


= 2019-10-28 =
v0.53  
* В некоторых случаях было возможным переполнение контейнера и вывод текста за пределы нужного. Пофиксил
* Небольшие правки стилей


= 2019-10-24 =
v0.52  
* к аватаркам добавлен атрибут loading="lazy" (пока его поддерживает хром). Полезно для 1-й длинной страницы


v0.51  
* События logged_in и logged_in_ulogin будут видеть гости. Мотивация - залогиниться, когда видишь что и другие также делают.
* Поддержка дополнения <a href="https://codeseller.ru/products/fake-online/" target="_blank">Fake Online</a> совместно с <a href="https://codeseller.ru/products/bonus-on-login/" target="_blank">Bonus on Login</a>
- боты как будто стремятся получить рейтинг "За логин" и поэтому каждый день логинятся на сайте.

* рейтинг активности "bonus-login" также как и 'smart-comment' исключены из ленты чтобы не засорять активность.



= 2019-09-04 =
v0.50  
* В html отдельного пункта события добавлен класс типа выводимого объекта (object_type): una_type_%object_type%
пример: <a href="https://yadi.sk/i/CG8Wxl2q8RCKqw" target="_blank">Скрин</a> 
Полезно для дополнительной стилизации например по типу записи.

* Добавлен фильтр <code>una_exclude_post_types</code> - исключаем post_type - (типы записей WordPress)
и существующий массив имеет следующие исключения:
'nav_menu_item' - вордпресс меню
'customize_changeset' - изменения кастомайзера
'oembed_cache' - кеш оэмбеда (если в теле линк на ютуб например)
'custom_css' - кастомные стили
'wp_block' - импортирован или создан гутенберг блок

Подробней, как исключить не нужный для вас тип записей из записи в активность, читайте в разделе F.A.Q.


= 2019-07-08 =
v0.41  
* Корректировка стилей под <a href="https://codeseller.ru/products/universe-activity-comments/">Universe Activity Comments</a>


= 2019-07-05 =
v0.40   
* Смена версии т.к. менеджер дополнений и репозиторий WP-Recall не видит увеличения цифры при переходе с версии 0.30 на 0.4. Соответственно не предлагает обновиться.


= 2019-07-03 =
v0.4   
* Поддержка дополнения <a href="https://codeseller.ru/products/woman-man/">Woman Man</a> - теперь все события будут учитывать пол пользователя, что он указал в настройках профиля при использовании названного допа.  
Например:  
"Владимир вошел на сайт"  
"Анжелика вошла на сайт"  
- это сделает ваш сайт максимально человечным, используя всю мощь и привлекательность русского языка.  

* Исправлены опечатки
* Небольшие правки css


= 2019-04-26 =
v0.30   
* поддержка WP-Recall 16.16
* для крон события добавлена своя аватарка
* для событий от имени гостя добавлена своя аватарка
* добавлен новый стиль вывода: "una_card" - веведет события карточкой (используйте в шорткоде вписам в атрибут class="una_card")  
* тип записи "wp_block" не участвует в логах. Это создание, импорт или удаление гутенберг блока
* теперь при выставлении рейтинга к посту в группе пишется и имя группы. Пример: "Проголосовал +5 за запись: "Посёлок программистов", в группе Открытая 2019"
- в прошлых версиях был добавлен в отдельный пункт (item) css класс определяющий что за тип данных выводится - <a href="https://yadi.sk/i/oWgYPnmR3KJutL" target="_blank">скриншот</a> - так вы можете кастомизировать отдельное событие и обыграть стилями
* в ЛК, в ленте пользователя, дополнил новыми иконками на новые события.


Добавлены фильтры: 
<code>una_filter_updates</code> передает один аргумент - массив событий для вывода в кнопке-фильтре "Обновления"
(пример, как работать - смотри в файле integration/addon-country-and-city-in-profile-pro.php 4-й пункт)
<code>una_filter_publications</code> передает один аргумент - массив событий для вывода в кнопке-фильтре "Публикации"
<code>una_filter_comments</code> передает один аргумент - массив событий для вывода в кнопке-фильтре "Комментарии"
<code>una_filter_ratings</code> передает один аргумент - массив событий для вывода в кнопке-фильтре "Рейтинг"
<code>una_filter_subscriptions</code> передает один аргумент - массив событий для вывода в кнопке-фильтре "Подписки"

db_version = '1.1.0':
- добавлена колонка group_id (число) - содержит id группы. Для полноценной поддержки дополнения групп
- добавлена колонка hide (число) - маркер "1" укажет что событие в архиве (скрыто)

Добавлена константа <code>UNA_DB</code> - для быстрого доступа к таблице БД 'wp_otfm_universe_activity'


* Добавлена поддержка дополнения Bookmarks - добавил запись в закладки. Событие видят все. Гостям сайта покажет что есть возможность закладок.


* Добавлена поддержка событий дополнения Country & User in Profile PRO:
установил город, сменил город, удалил город. Первые 2 видят залогиненные. Второе - только админ. 
установил город - указывается город
сменил город - указывается старый и новый город
удалил город - указывается старый город
- эти события выводятся также в кнопке-фильтре "Обновления"


* Добавлена поддержка событий дополнения Birthday in Profile:
установил день рождения (событие видит автор)
сменил дату рождения (событие видит автор)
- эти события выводятся также в кнопке-фильтре "Обновления"

* Добавлена поддержка дополнения Bot User Info: 
пишет событие когда пользователь запросил информацию по себе (событие видит админ). 


* Добавлена поддержка дополнения Subscription Two: 
оформил подписку на комментарии записей или форума (событие видит залогиненный)
удалил подписку на комментарии записей или форума (событие видит админ)
Причём для групп пишет: <code>Otshelnik-Fm в группе "Приют Отшельника", подписался на комментарии к записи: Кукла (The Inhabitant)(2016)</code>

* Добавлена поддержка дополнения Pretty URL Author:
сменил урл кабинета (событие видит админ)


Добавлена поддержка событий дополнений Групп (Group Recall):
установил (сменил) описание группы (событие видят все)
смена статуса группы: открытая/закрытая
пользователь забанен в группе
у пользователя сменили роль в группе
теперь ловится не только выход из группы, но также если админ в списке пользователей группы удалил его из группы


Добавлена поддержка событий дополнения Groups Theme RePlace:
установил (сменил) статус группы (событие видят все)
установил или сменил аватарку группы (событие видят все)
установил или сменил обложку группы (событие видят все)


Добавлена поддержка событий дополнения Group New Post Notify:  
подписался на уведомления о новых записях группы (событие видит автор)  
изменил тип уведомлений подписки (событие видит автор)  
удалил подписку (событие видит автор)  


= 2018-04-22 =
v0.23   
* добавлены дополнительные классы оборачивающие главный контейнер:
Если ничего нету - добавляется класс una_wrapper_all
И если переходим по фильтру то соответственно: una_wrapper_publications, una_wrapper_comments, una_wrapper_ratings, una_wrapper_updates, una_wrapper_subscriptions


= 2018-01-17 =
v0.22   
* Реорганизация файловой структуры. Была проблема - отключив плагин (пример: asgaros forum) события его работы продолжали выводиться
это неверно - т.к. к отключенному плагину могли идти запросы (к его функциям).
Теперь в папке integration будут создаваться файлы каждый на свой доп или плагин.


= 2017-12-16 =
v0.21   
* Тип записи custom_css не учитывается в активности (это кастомные стили что ввели в новой версии ВП)


= 2017-11-23 =
v0.20.1  
* Подправил еще стили для админки

= 2017-11-23 =
v0.20  
* Подправил стили для админки
* Немного рекламы своих допов к Universe Activity в блоке настроек допа


= 2017-11-22 =
v0.19  
* Убрал фиксацию бесполезного ВП типа записей "oembed_cache". Она создавалась если контент записи содержал oEmbed


= 2017-11-18 =
v0.18  
* Оптимизировал доп по запросам к БД


= 2017-11-09 =
v0.17  
* Возможность в атрибуте шорткода class передавать несколько классов


= 2017-11-03 =
v0.16  
* Добавил служебную информацию - версию бд системы.
* Добавлен новый параметр в шорткод <code>events_count</code> - указав его значение 0 - отключим счетчик событий. По умолчанию "1" - включен.
* Добавлен фильтр <code>una_get_data_db</code> - фильтр массива полученных на страницу данных. Можно применять для дополнения массива своими данными
* Очистка передаваемых include, exclude аргументов шорткода от возможных пробелов.
* Статус комментариев выделил цветом. "На утверждении" - оранжевый, "Спам" - красный
* Поддержка дополнения Universe Activity Comments


= 2017-10-28 =
v0.15  
* Поддержка дополнения <a href="https://codeseller.ru/products/universe-activity-modal/" target="_blank">Universe Activity Modal</a>


= 2017-10-09 =
v0.14  
* Исправлен баг приводящей к игнорированию любых из капч при регистрации ( спасибо за репорт Игорю (garry) )


= 2017-09-25 =
v0.13  
* Исправил ошибку проверки на дубликаты. Спасибо Игорь (garry)  


= 2017-08-23 =
v0.12  
* Уточнил проверку - в админке мы или нет. Не влияет теперь на ajax запрос  
* Ввел новую переменную запроса - короткую ссылку на кабинет автора (спасибо пользователю Kerncraft1 за репорт). Т.к. ВП функция редиректа короткой ссылки автора работает через раз  



= 2017-08-22 =
v0.11  
* Добавлена иконка дополнения
* Поддержка плагина Asgaros Forum (не ниже версии 1.5.9)
- Ловим событие создания новой темы на форуме
- Ловим события удаления темы форума
* Рейтинг за сообщение на Asgaros Forum (дополнение <a href="https://codeseller.ru/products/asgaros-forum-wp-recall/" target="_blank">Asgaros Forum + WP-Recall</a>)
- ссылка на комментарий форума формируется короткая - меньше запросов к бд
* Добавлен вывод 30-ти последних событий в админке на странице консоли WP-Recall


= 2017-08-16 =
v0.10  
* Исправлен баг: при попытке вывести одно лишь событие удаления пользователя (include_actions="delete_user") - для админа все работает, а с правами для других пользователей происходил игнор.
* Добавлен новый параметр в атрибут шорткода: <code>include_users</code> принимает параметр <code>current</code> (получит id текущего авторизованного юзера)
Пример: <code>[otfm_universe include_actions="logged_in" include_users="current"]</code> - выведет нам все входы текущего пользователя. Если не указать <code>include_users="current"</code> - то шорткод выдаст нам все входы всех пользователей
Для ЛК был подобный параметр <code>author_lk</code>


= 2017-08-15 =
v0.9  
* работа с плагином WP-Recall верси 16.5.0! и выше  
* добавил поддержку рейтинга дополнения <a href="https://codeseller.ru/products/primeforum/" target="_blank">Prime Forum</a>
    короткая ссылка на запись (топик)
* дополнил стили одиночной страницы иконками
* событие, фиксирующее удаление пользователя (видимое только админу) теперь еще содержит и email удаленного юзера
* ловим событие загрузки обложки в ЛК. При загрузки другой обложки дата события меняется
    это событие будет доступно в фильтре "Обновления"
* ловим событие загрузки аватарки в ЛК
    это событие будет доступно в фильтре "Обновления"
* ловим событие удаления аватарки
    событие видит автор и админ
    в этом случае удаляем событие загрузки аватарки - т.к. картинки нет, выводить нечего
    и если есть еще одно событие удаления аватарки - удалим его
* обложки и аватарки можно просматривать в лайтбоксе. Я работаю с дополнением <a href="https://codeseller.ru/products/magnific-popup-recall/" target="_blank">Magnific Popup Recall</a>


= 2017-08-10 =
v0.8.1  
* Устранил существующую ошибку


= 2017-08-10 =
v0.8  
* работа над тесной интеграцией с <a href="https://codeseller.ru/products/universe-activity-extended/" target="_blank">Universe Activity Extended</a>
* изменение страницы настроек дополнения  
* решение проблемы: когда в менеджере вкладок выставлено кеширование вкладки. Читай в FAQ


= 2017-08-09 =
v0.7  
* добавил хук (action) <code>una_start_shortcode</code> - срабатывает на странице с шорткодом. 
* в админке появились настройки. Пока одна "Используем цвета из "основного цвета" WP-Recall?" - выбрав "Да" - цветовая гамма блоков будет формироваться на основе цвета WP-Recall


= 2017-08-07 =
v0.6.1  
* вырубил функцию своего дебага. У вас ее нет и не нужна

= 2017-08-07 =
v0.6  
* ООП  
* Исправление уведомлений уровня notice  
* Исправлены найденные баги  
* Пагинация, плавающий блок даты, плавное доведение до верха блока - это отделилось в стороннее решение <a href="https://codeseller.ru/products/universe-activity-extended/" target="_blank">Universe Activity Extended</a>
* Пункт выше - т.к. я ядро (сам доп "Universe Activity") решил распространять бесплатно.  
Мотив простой - базовая версия самодостаточна и ее можно использовать как фреймворк. А бесплатное распространение позволит охватить максимум аудитории и сделать базу еще крепче и гибче.  
Отдельными дополнениями будет наращиваться к ней функционал - кому-то нужный (возьмет), а кому дополнительно обвязка не нужна - не будет нагружать сервак.  

= 2017-08-03 =
v0.5  
* вывел фильтры  
* переработал стили  
* добавил новые атрибуты в шорткод  

= 2017-07-11 =
v0.4  
* ajax пагинация на странице вывода шорткодом.  
* плавное доведение блока вверх.  

= 2017-07-07 =
v0.3  
* Постраничная навигация (пагинация) - возможно установить свое кол-во для вывода, или если -1 - выведет все.  

= 2017-07-06 =
v0.2  
* Использование класса <a href="https://codeseller.ru/post-group/rcl_query-udobnyj-klass-dlya-postroeniya-zaprosov-k-bd-ot-wp-recall/" target="_blank">Rcl_Query</a> от WP-Recall  
* На данный момент дополнение позволяет выводить шорткодом данные:  
1. Указать id пользователей которых выводить, или наоборот - которых исключить.  
2. Позволяет указать конкретные действия которые выводить, или наоборот - какие исключить. Поддерживая при этом приватность в зависимости от привилегий пользователя.  
3. Позволяет установить сколько элементов на страницу выводить (для постраничной навигации, ее пока нет еще)  
4. Позволяет установить в шорткоде класс которым будет оборачиваться главный блок - это вам позволит каждый шорткод, к примеру, стилизовать по своему.  
Я подготовил другой внешний вид - как пример возможностей.  

= 2017-05-28 =
v0.007  
* Идея, проектирование бд и первый код.  



== Прочее ==

* Поддержка осуществляется в рамках текущего функционала дополнения
* При возникновении проблемы, создайте соотвествующую тему на <a href="https://codeseller.ru/forum/product-15611/" target="_blank">форуме поддержки</a> товара
* Если вам нужна доработка под ваши нужды - вы можете обратиться ко мне в <a href="https://codeseller.ru/author/otshelnik-fm/?tab=chat" target="_blank">ЛС</a> с техзаданием на платную доработку.

Полный список моих работ опубликован <a href="https://otshelnik-fm.ru/?p=2562&utm_source=free-addons&utm_medium=addon-description&utm_campaign=universe-activity&utm_content=codeseller.ru&utm_term=all-my-addons" target="_blank">моём сайте</a> и в каталоге магазина <a href="https://codeseller.ru/author/otshelnik-fm/?tab=publics&subtab=type-products" target="_blank">CodeSeller.ru</a>
