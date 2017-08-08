<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function una_settings($options){

    $opt = new Rcl_Options(__FILE__);

// Настройки для Universe Activity Extended
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
                                        $opt->notice('По умолчанию: <strong>Нет</strong><br/>'),
                                    )
                                );
    }
// END Настройки для Universe Activity Extended

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
        '<div id="una_info"></div>', // my adv

    ));

    return $options;
}
add_filter('admin_options_wprecall','una_settings');
