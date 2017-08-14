## Описание:

Дополнение для WordPress плагина [WP-Recall](https://wordpress.org/plugins/wp-recall/) - добавляет возможность писать пользовательскую активность в базу данных, а так же выводить ее - формируя ленту активности сайта. 

p.s. - это базовое ядро. Оно пишет в базу, выводит из нее, но имеет ограничения (смотри ниже) 

------------------------------

## Demo:

На [этой странице](http://across-ocean.otshelnik-fm.ru/top-secret-addons/) и там же примеры 

В [личном кабинете](http://across-ocean.otshelnik-fm.ru/author/otshelnik-fm/) - под дополнением "User Info Tab" 

Большой скрин: что видит [админ](http://across-ocean.otshelnik-fm.ru/wp-content/uploads/2017/07/snapshot.jpg) 

------------------------------

## Возможности:
- Пишет пользовательскую активность в свою таблицу в базе данных 
- Выводит пользовательскую активность 
- Система привилегий и доступов на выводимые события. Так, например, админ видит всю активность, гость (не залогиненный пользователь) видит установленный минимум информации. Вошедший на сайт - видит больше событий. А автор, в своем ЛК, видит свои события (например вход и выход с сайта) 
- Гибкий вывод событий с помощью шорткода 
- Счетчик событий 
- Кнопки фильтра событий 
- Возможность указать количество событий к выводу, включить или исключить события, выводить кнопки фильтра событий или нет, включить вывод событий юзера по его user_id 
- Имеется 5 предустановленных стилей (задаются атрибутом class в шорткоде) 
- Возможность влияния на привилегии к событиям (простой функцией переназначить событие - показать или скрыть его) 
- Интеграция с дополнением [User Info Tab](https://codeseller.ru/products/user-info-tab/) - но без постраничной навигации. Последние 30 событий пользователя. Ограничения снимаются дополнением [Universe Activity Extended](https://codeseller.ru/products/universe-activity-extended/)  
- В админке настройка позволяющая задать цвета от настройки цвета WP-Recall  

------------------------------

## Ограничения. Чего нет в этой версии, но доступно в Extended версии: 
- В этом дополнении нет: постраничной навигации 
- В этом дополнении нет: ajax-а в пагинации 
- В этом дополнении нет: плавающего блока даты 
- В этом дополнении нет: плавного доведения до блока при навигации 
- В этом дополнении нет: возможности среди событий разместить свой информационный блок или рекламу  

Но все это есть в дополнении [Universe Activity Extended](https://codeseller.ru/products/universe-activity-extended/) - оно дополняет и расширяет возможности базового допа 

------------------------------

## Список дочерних дополнений к нему:

- [Universe Activity Extended](https://codeseller.ru/products/universe-activity-extended/)  
- ...  

------------------------------

## Список регистрируемых событий и логика:
    * Пишет когда пользователь залогинился через ВП
    * Когда юзер залогинился через плагин uLogin и какая сеть
    * когда зарегистрировался
    * если в WP-Recall стоит подтверждение регистрации - то запишет как ее подтвердит
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
    * статус комментария, если он отличается от опубликованного, учитывается. И ссылка на коммент не выводится
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
    * добавлен в отдельный пункт (item) css класс определяющий что за тип данных выводится - [скриншот](https://yadi.sk/i/oWgYPnmR3KJutL) - так вы можете кастомизировать отдельное событие и обыграть стилями
    * ловим создание новой группы
    * удаление группы (админка)
        при удалении группы на строчку созданной группы вешаем маркер del - и наша система не будет на нее давать ссылку
    * ввел короткие ссылки для комментариев и для групп и для форума (Prime-Forum) - это значительно сократило кол-во запросов к бд
    * вступил в группу/покинул ее
    * создал тему на Prime-Forum
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

------------------------------

## События и привилегии:

Дополнение позволяет разным типам пользователей видеть разные события. Так что все события видит только админ, а гость видит минимум - у него есть мотивация зарегистрироваться или войти на сайт чтобы видет больше событий.  
Ниже таблица все объясняет.  

### Привилегии:  
- Гость - пользователь не вошедший на сайт. Если у события нет привилегии доступа - значит видно всем, начиная с гостя. 
- Залогинен - 'logged' 
- Автор (в своем ЛК - это если передан атрибут шорткода include_users="author_lk") - 'logged' и 'author' 
- Админ - 'logged', 'author', 'admin' 

### События:  

#### Гость видит: 
 
| slug | действие |
|------|----------|
| add_comment | добавлен комментарий |  
| add_post | добавлена запись |  
| change_status | юзер сменил свой статус |  
| give_rating_comment | рейтинг за комментарий |
| give_rating_notes | рейтинг за заметку |  
| give_rating_post | рейтинг за запись - тип post |  
| give_rating_forum-post | рейтинг за сообщение на Prime Forum |  
| give_rating_post-group | рейтинг за запись в группе - тип post-group |  
| give_rating_products | рейтинг за товар - тип products |  
| add_user_feed | подписался на юзера |  
| create_group | создал группу |  
| user_in_group | юзер вступил в группу |  
| pfm_add_topic | создана новая тема на prime-forum |  
| add_cover | юзер добавил обложку в своём ЛК |  
| add_avatar | юзер добавил (сменил) аватарку (локальная аватарка. не граватар) |  


#### Залогиненый видит: те что выше, плюс:  

| slug | действие |
|------|----------|
| add_draft | добавил черновик |  
| confirm_register | подтвердил регистрацию |  
| register | зарегистрировался |  
| del_user_feed | отписался от юзера |  
| add_user_blacklist | добавил в черный список |  
| del_user_blacklist | удалил из черного списка |  
| user_out_group | вышел из группы |  

#### Автор видит: все что выше, плюс: 

| slug | действие |
|------|----------| 
| logged_in | когда он вошел на сайт |  
| logged_in_ulogin | когда он вошел через плагин u-login и через какую сеть |  
| logged_out | когда он вышел с сайта |  
| del_avatar | когда он удалил свой аватар (локальный, не граватар) |  

#### Админ видит: все что выше, плюс: 

| slug | действие |
|------|----------| 
| delete_post | удалил запись - в корзину |  
| delete_post_fully | удалил запись навсегда. Если это автоочистка корзины - пишет "wp-cron" |  
| delete_user | удалил юзера |  
| profile_update | обновил настройки профиля |  
| register_failed | неудачная регистрация |  
| delete_group | удалил группу |  
| pfm_del_topic | удалил тему с форума |  

------------------------------

## Шорткод:  
Дополнение автоматически ничего нигде не выводит. За исключением поддержки дополнения [User Info Tab](https://codeseller.ru/products/user-info-tab/) - но без постраничной навигации (там выводит последние 30 событий пользователя)  
(чтобы у User Info Tab была постраничная навигация нужно поставить дополнение [Universe Activity Extended](https://codeseller.ru/products/universe-activity-extended/) )  

Итак - всё выводим с помощью шорткода: `[otfm_universe filter=1]`  

**Дополнительные атрибуты шорткода:**   

**filter** - показывать фильтр. Поставьте "1" чтобы выводить сверху фильтр по событиям (по умолчанию 0)  
**number** - количество событий на странице (по умолчанию 30). Поставьте "-1" чтобы вывести все. Для постраничной навигации вам нужно дополнение [Universe Activity Extended](https://codeseller.ru/products/universe-activity-extended/)   
**include_actions** - включить эти события. Через запятую (события -slug- на английском смотрите выше в "Событиях и привилегиях").  
**exclude_actions** - исключить события (нельзя в атрибутах одновременно использовать include_actions и exclude_actions. Что-то одно)  
**include_users** - включая юзеров. Через запятую (id юзеров - число). В личном кабинете в произвольной вкладке WP-Recall допустимо вписать `author_lk` и система подставит туда id автора кабинета.  
**class** - css class главного блока [скриншот](https://yadi.sk/i/f1OvpO_E3LmcZh). Например чтобы вы создали свой дизайн вывода. На основе этого атрибута уже подготовлено несколько значений:  
(если не указан - то дизайн будет самый минималистичный)  
"una_zebra" - простой зеброй  
"una_basic" - базовый  
"una_modern" - модерн стиль  

**Примеры:**  
1. Выведем модерновый стиль, фильтр и 40 записей:  
`[otfm_universe class="una_modern" number="40" filter=1]`  

2. Выведем, создав в админке вашего сайта: "WP-Recall" -> "Менеджер вкладок" - вкладку в ЛК, чтобы она показывала только события автора кабинета:  
`[otfm_universe filter=1 include_users="author_lk"]`  

3. Выведем все события: комментарии и рейтинг за них. Без фильтра сверху:  
`[otfm_universe class="una_basic" number="-1" include_actions="add_comment,give_rating_comment"]`  

4. Выведем все рейтинги и стилизуем базовым стилем:  
`[otfm_universe class="una_basic" include_actions="give_rating_comment,give_rating_notes,give_rating_post,give_rating_post-group,give_rating_products,give_rating_forum-post"]`  

------------------------------

## Какие события включены в фильтр:  
**Публикации** - add_post  
**Комментарии** - add_comment  
**Рейтинг** - give_rating_comment,give_rating_notes,give_rating_post,give_rating_post-group,give_rating_products,give_rating_forum-post  
**Обновления** - change_status,profile_update,create_group,user_in_group,pfm_add_topic,add_cover,add_avatar  
**Подписки** - add_user_feed  

------------------------------

## FAQ:  
#### Установил дополнение - не вижу ничего  
- Нужно вывести шорткодом. Смотри описание шорткода  и конечно же должно быть записано хоть одно событие в базу - админ видит их все.  
  
  
#### Я вижу не все события из последних. Некоторые скрыты  
- У дополнения есть система событий и привилегий. Читай выше **"События и привилегии"**  
  
  
#### Установил, вывел. Пишет счетчик: событий 100, но я вижу только 30  
- По умолчанию и выводит 30. Нужно вывести все - ставь в атрибут шорткода `number="-1"`. Нужна постраничная навигация ставь дополнение [Universe Activity Extended](https://codeseller.ru/products/universe-activity-extended/) (читай пункт **"Ограничения"**)  
  
  
#### Как вывести активность пользователя в его личном кабинете?  
- Читай пункт **"Шорткод"**. Второй пример.  
  
  
#### Вывел фильтр - но я не вижу рейтинг и подписки  
- Дополнения "Rating System (Система рейтинга)" и "Feed (Подписки)" должны быть у вас активны
  

#### У дополнения есть настройки?  
- Да. В админке: "WP-Recall" -> "Настройки Universe Activity"  

  
#### А в какой таблице в базе данных хранятся события?  
- Смотрите таблицу: **wp_otfm_universe_activity**  
  
  
#### Как в лайтбоксе увеличивать аватарки и обложки? -открывает картинку в отдельной вкладке  
- Ставьте дополнение для просмотра увеличенных изображений [Magnific Popup Recall](https://codeseller.ru/products/magnific-popup-recall/)  
  
  
#### Удалив это дополнение я потеряю данные о активности?  
- При удалении дополнения через менеджер дополнений - он за собой удалит свою таблицу, в которой он хранит пользовательскую активность.  


#### Вписал шорткод в менеджере вкладок. Поставил галку на "кеширование" и все разъехалось (стили не загружаются)  
- При кешировании вкладки (когда html из кеша отдается) дело до функции шорткода не доходит. Поэтому стили не загружаются (а стили у нас грузятся только там где требуется)  
Значит вам нужно вручную вызвать нужные стилевые файлы для нужной вкладки.  
  
Решение проблемы:  
1-вариант: Отключить поддержку кеширования у вкладки. Это самый простой и надежный способ. Больше ничего делать не нужно.  
  
2-вариант:  
Вам нужно открыть в админке в "Менеджере вкладок" вкладку с шорткодом и посмотреть "Идентификатор вкладки". В моем случае он `aktivnost_89`  
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

------------------------------

## Установка/Обновление  

**Установка:**  
Т.к. это дополнение для WordPress плагина WP-Recall, то оно устанавливается через [менеджер дополнений WP-Recall](https://codeseller.ru/obshhie-svedeniya-o-dopolneniyax-wp-recall/)  

1. В админке вашего сайта перейдите на страницу "WP-Recall" -> "Дополнения" и в самом верху нажмите на кнопку "Обзор", выберите .zip архив дополнения на вашем пк и нажмите кнопку "Установить".  
2. В списке загруженных дополнений, на этой странице, найдите это дополнение, наведите на него курсор мыши и нажмите кнопку "Активировать". Или выберите чекбокс и в выпадающем списке действия выберите "Активировать". Нажмите применить.  


**Обновление:**  
Дополнение поддерживает [автоматическое обновление](https://codeseller.ru/avtomaticheskie-obnovleniya-dopolnenij-plagina-wp-recall/) - два раза в день отправляются вашим сервером запросы на обновление.  
Если в течении суток вы не видите обновления (а на странице дополнения вы видите что версия вышла новая), советую ознакомиться с этой [статьёй](https://codeseller.ru/post-group/rabota-wordpress-krona-cron-prinuditelnoe-vypolnenie-kron-zadach-dlya-wp-recall/) 

------------------------------

## Changelog 

**2017-08-15**  
v0.9  
- работа с плагином WP-Recall верси 16.5.0! и выше  
- добавил поддержку рейтинга дополнения [Prime Forum](https://codeseller.ru/products/primeforum/)  
    короткая ссылка на запись (топик)  
- дополнил стили одиночной страницы иконками  
- событие, фиксирующее удаление пользователя (видимое только админу) теперь еще содержит и email удаленного юзера  
- ловим событие загрузки обложки в ЛК. При загрузки другой обложки дата события меняется  
    это событие будет доступно в фильтре "Обновления"  
- ловим событие загрузки аватарки в ЛК  
    это событие будет доступно в фильтре "Обновления"  
- ловим событие удаления аватарки  
    событие видит автор и админ  
    в этом случае удаляем событие загрузки аватарки - т.к. картинки нет, выводить нечего  
    и если есть еще одно событие удаления аватарки - удалим его  
- обложки и аватарки можно просматривать в лайтбоксе. Я работаю с дополнением [Magnific Popup Recall](https://codeseller.ru/products/magnific-popup-recall/)  



**2017-08-10**  
v0.8.1  
- Устранил существующую ошибку  



**2017-08-10**  
v0.8  
- работа над тесной интеграцией с [Universe Activity Extended](https://codeseller.ru/products/universe-activity-extended/)  
- изменение страницы настроек дополнения  
- решение проблемы: когда в менеджере вкладок выставлено кеширование вкладки. Читай в FAQ  


**2017-08-09**  
v0.7  
- добавил хук (action) `una_start_shortcode` - срабатывает на странице с шорткодом. 
- в админке появились настройки. Пока одна "Используем цвета из "основного цвета" WP-Recall?" - выбрав "Да" - цветовая гамма блоков будет формироваться на основе цвета WP-Recall  


**2017-08-07**  
v0.6.1  
- вырубил функцию своего дебага. У вас ее нет и не нужна  

   
**2017-08-07**   
v0.6  
- ООП  
- Исправление уведомлений уровня notice  
- Исправлены найденные баги  
- Пагинация, плавающий блок даты, плавное доведение до верха блока - это отделилось в стороннее решение [Universe Activity Extended](https://codeseller.ru/products/universe-activity-extended/)  
- Пункт выше - т.к. я ядро (сам доп "Universe Activity") решил распространять бесплатно.  
Мотив простой - базовая версия самодостаточна и ее можно использовать как фреймворк. А бесплатное распространение позволит охватить максимум аудитории и сделать базу еще крепче и гибче.  
Отдельными дополнениями будет наращиваться к ней функционал - кому-то нужный (возьмет), а кому дополнительно обвязка не нужна - не будет нагружать сервак.  

**2017-08-03**  
v0.5  
- вывел фильтры  
- переработал стили  
- добавил новые атрибуты в шорткод  

**2017-07-11**  
v0.4  
- ajax пагинация на странице вывода шорткодом.  
- плавное доведение блока вверх.  

**2017-07-07**  
v0.3  
- Постраничная навигация (пагинация) - возможно установить свое кол-во для вывода, или если -1 - выведет все.  

**2017-07-06**  
v0.2  
- Использование класса [Rcl_Query](https://codeseller.ru/post-group/rcl_query-udobnyj-klass-dlya-postroeniya-zaprosov-k-bd-ot-wp-recall/) от WP-Recall  
- На данный момент дополнение позволяет выводить шорткодом данные:  
1. Указать id пользователей которых выводить, или наоборот - которых исключить.  
2. Позволяет указать конкретные действия которые выводить, или наоборот - какие исключить. Поддерживая при этом приватность в зависимости от привилегий пользователя.  
3. Позволяет установить сколько элементов на страницу выводить (для постраничной навигации, ее пока нет еще)  
4. Позволяет установить в шорткоде класс которым будет оборачиваться главный блок - это вам позволит каждый шорткод, к примеру, стилизовать по своему.  
Я подготовил другой внешний вид - как пример возможностей.  

**2017-05-28**  
v0.007  
- Идея, проектирование бд и первый код.  

------------------------------

## Поддержка и контакты  

* Поддержка осуществляется в рамках текущего функционала дополнения  
* При возникновении проблемы, создайте соотвествующую тему на [форуме поддержки](https://codeseller.ru/forum/product-15611/) товара  
* Если вам нужна доработка под ваши нужды - вы можете обратиться ко мне в <a href="https://codeseller.ru/author/otshelnik-fm/?tab=chat" target="_blank">ЛС</a> с техзаданием на платную доработку.  

Полный список моих работ опубликован <a href="http://across-ocean.otshelnik-fm.ru/" target="_blank">на моем демо-сайте</a> и в каталоге магазина <a href="https://codeseller.ru/author/otshelnik-fm/?tab=publics&subtab=type-products" target="_blank">CodeSeller.ru</a>  

------------------------------

## Author

**Wladimir Druzhaev** (Otshelnik-Fm)


