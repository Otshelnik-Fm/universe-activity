<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


add_filter( 'rcl_options', 'una_settings' );
function una_settings( $options ) {

    //создаем блок опций
    $options->add_box( 'una_box_id', array(
        'title' => 'Настройки Universe Activity',
        'icon'  => 'fa-bullseye'
    ) );

    // создаем группу
    $options->box( 'una_box_id' )->add_group( 'una_group_1', array(
        'title' => 'Universe Activity:'
    ) );
    //добавляем опции
    $options->box( 'una_box_id' )->group( 'una_group_1' )->add_options( array(
        [
            'title'   => 'Используем цвета из "основного цвета" WP-Recall?',
            'type'    => 'radio',
            'slug'    => 'una_rcl_color',
            'values'  => [ '0' => 'Нет', '1' => 'Да' ],
            'default' => '0',
            'help'    => 'В "Общих настройках" WP-Recall, вы выбираете цвет кнопок: пункт "Оформление".<br><br>'
            . 'Включив эту опцию - блок комментариев и тени будут в этом же стиле.<br>'
            . 'Подробно, со скриншотами, в этой статье: '
            . '<a href="https://codeseller.ru/?p=12724" target="_blank" title="Перейти. Откроется в новом окне">'
            . 'Используем цвет реколл, которым мы стилизуем кнопки, для своих дополнений'
            . '</a>.',
        ],
    ) );

// Настройки для Universe Activity Extended
    $my_adv = '';

    if ( rcl_exist_addon( 'universe-activity-extended' ) ) {
        // создаем группу
        $options->box( 'una_box_id' )->add_group( 'una_group_2', array(
            'title' => 'Universe Activity Extended:'
        ) );
        //добавляем опции
        $options->box( 'una_box_id' )->group( 'una_group_2' )->add_options( array(
            [
                'title'   => 'Анимация загрузки',
                'type'    => 'radio',
                'slug'    => 'unae_animations',
                'values'  => [ 0 => 'Нет', 1 => 'Включить' ],
                'default' => 0,
                'help'    => 'Анимация. Плавное появление нижних блоков при скролле страницы вниз',
                'notice'  => 'По умолчанию: Нет',
            ],
            [
                'title'  => 'Хук - перед каким событием по счету:',
                'type'   => 'number',
                'slug'   => 'unae_num_item',
                'help'   => 'Установите число. Например "10" - перед 10-м событием сработает хук (filter) "una_before_item" '
                . '(смотри <a href="https://codeseller.ru/products/universe-activity-extended/" target="_blank">в документации</a> '
                . 'в вкладке FAQ).<br>Вы на него сможете повесить свою функцию - которая, например, '
                . 'выведет рекламный баннер или информационный блок.<br><br>'
                . 'Если вы не поняли о чем я - оставьте эту настройку. Вам она не нужна.',
                'notice' => 'По умолчанию: 10',
            ],
        ) );
    } else {

        $text = '<a href="https://codeseller.ru/products/universe-activity-extended/" title="Перейти к описанию" target="_blank">'
            . '"Universe Activity Extended"</a> - расширяет базовые возможности и добавляет постраничную навигацию и ajax, '
            . 'анимацию, блок даты и многое другое.';

        $args   = [
            'type'  => 'success', // info,success,warning,error,simple
            'icon'  => 'fa-check-square-o',
            'title' => '"UNA Extended" - больше возможностей',
            'text'  => $text,
        ];
        $my_adv = rcl_get_notice( $args );
    }

    if ( ! rcl_exist_addon( 'universe-activity-modal' ) ) {
        $text   = '<a href="https://codeseller.ru/products/universe-activity-modal/" title="Перейти к описанию" target="_blank">'
            . '"Universe Activity Modal"</a> - добавляет возможность просмотра полной записи в всплывающем (модальное) окне. И крутая галерея реколл.';
        $args   = [
            'type'  => 'warning', // info,success,warning,error,simple
            'icon'  => 'fa-clone',
            'title' => 'Словно в Instagram',
            'text'  => $text,
        ];
        $my_adv .= rcl_get_notice( $args );
    }
    if ( ! rcl_exist_addon( 'una-subscriptions' ) ) {
        $text   = '<a href="https://codeseller.ru/products/una-subscriptions/" title="Перейти к описанию" target="_blank">'
            . '"UNA Subscriptions"</a> - организует уведомления пользователю об активности его друзей (Friends Recall) или на кого он подписан (FEED) '
            . 'и он сам выбирает о чём его уведомлять.';
        $args   = [
            'type'  => 'error', // info,success,warning,error,simple
            'icon'  => 'fa-bell-o',
            'title' => 'Подписки и уведомления',
            'text'  => $text,
        ];
        $my_adv .= rcl_get_notice( $args );
    }
    if ( ! rcl_exist_addon( 'universe-activity-comments' ) ) {
        $text   = '<a href="https://codeseller.ru/products/universe-activity-comments/" title="Перейти к описанию" target="_blank">'
            . '"Universe Activity Comments"</a> - выводит полные комментарии в ленте активности.';
        $args   = [
            'type'  => 'info', // info,success,warning,error,simple
            'icon'  => 'fa-comments-o',
            'title' => 'Комментарии как на ладони',
            'text'  => $text,
        ];
        $my_adv .= rcl_get_notice( $args );
    }

    if ( ! empty( $my_adv ) ) {
        $style = '<style>#options-group-una_group_3 .rcl-notice__text{text-align:left;margin-left:18px;}</style>';

        // создаем группу 3
        $options->box( 'una_box_id' )->add_group( 'una_group_3', array(
            'title' => 'Расширьте функционал "Universe Activity"'
        ) );

        $options->box( 'una_box_id' )->group( 'una_group_3' )->add_options( array(
            [
                'type'    => 'custom',
                'content' => $my_adv . $style
            ],
        ) );
    }

    return $options;
}
