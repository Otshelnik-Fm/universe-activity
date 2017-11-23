<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function una_settings($options){

    $opt = new Rcl_Options(__FILE__);

// Настройки для Universe Activity Extended
    $my_adv = '';
    $options_una_extended = '';
    if(rcl_exist_addon('universe-activity-extended')){
        $options_una_extended = $opt->option_block(
            array(
                $opt->title('Настройки Universe Activity Extended'),

                $opt->label('Анимация загрузки'),
                $opt->option('select', array(
                    'name' => 'unae_animations',
                    'options' => array(0 => 'Нет', 1 => 'Включить')
                )),
                $opt->help('Анимация. Плавное появление нижних блоков при скролле страницы вниз'),
                $opt->notice('По умолчанию: <strong>Нет</strong><br/><hr>'),

                $opt->label('Хук - перед каким событием по счету:'),
                $opt->option('number',array('name'=>'unae_num_item')),
                $opt->help('Установите число. Например "10" - перед 10-м событием сработает хук (filter) "una_before_item" (смотри <a href="https://codeseller.ru/products/universe-activity-extended/" target="_blank">в документации</a> в вкладке FAQ). Вы на него сможете повесить свою функцию - которая, например, выведет рекламный баннер или информационный блок. <br/>Если вы не поняли о чем я - оставьте эту настройку. Вам она не нужна.'),
                $opt->notice('По умолчанию 10<br/><hr>'),
            )
        );
    } else {
        $my_adv = '<div id="una_info">Вы можете расширить базовые возможности "Universe Activity" <br/>установив дополнение <a href="https://codeseller.ru/products/universe-activity-extended/" title="Перейти к описанию" target="_blank">"Universe Activity Extended"</a></div>';
    }
// END Настройки для Universe Activity Extended

    if(!rcl_exist_addon('universe-activity-modal')){
        $my_adv .= '<div id="una_info">Вы можете расширить базовые возможности "Universe Activity" <br/>установив дополнение <a href="https://codeseller.ru/products/universe-activity-modal/" title="Перейти к описанию" target="_blank">"Universe Activity Modal"</a></div>';
    }
    if(!rcl_exist_addon('universe-activity-comments')){
        $my_adv .= '<div id="una_info">Вы можете расширить базовые возможности "Universe Activity" <br/>установив дополнение <a href="https://codeseller.ru/products/universe-activity-comments/" title="Перейти к описанию" target="_blank">"Universe Activity Comments"</a></div>';
    }

    $options .= $opt->options(
        'Настройки Universe Activity',array(
        $opt->option_block(
            array(
                $opt->title('Настройки Universe Activity'),

                $opt->label('Используем цвета из "основного цвета" WP-Recall?'),
                $opt->option('select',array(
                    'name'=>'una_rcl_color',
                    'options'=>array('0'=>'Нет','1'=>'Да',)
                )),
                $opt->help('В "Общих настройках" WP-Recall, вы выбираете цвет кнопок - пункт "Оформление". Включив эту опцию - блок комментариев и тени будут в этом же стиле'
                        . '<br/>Подробно, со скриншотами, в этой статье: '
                        . '<a href="https://codeseller.ru/post-group/ispolzuem-cvet-rekoll-kotorym-my-stilizuem-knopki-dlya-svoix-dopolnenij/" target="_blank" title="Перейти. Откроется в новом окне">'
                        . 'Используем цвет реколл, которым мы стилизуем кнопки, для своих дополнений'
                        . '</a>'),
            )
        ),
        $options_una_extended,
        $my_adv, // my adv

    ));

    return $options;
}
add_filter('admin_options_wprecall','una_settings');
