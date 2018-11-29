<?php

return [
    'caption'  => 'Административная часть сайта',
    'logo_url' => '/packages/vis/builder/img/logo.png',
    'favicon_url' => '/packages/vis/builder/img/favicon/favicon.ico',
    'uri' => '/admin',
    'limitation_of_ip' => [],
    'menu' => [

        /* array(
             'title' => 'Главная',
             'icon'  => 'home',
             'link'  => '/',
             'check' => function() {
                 return true;
             }
         ),*/

        [
            'title' => 'Структура сайта',
            'icon'  => 'sitemap',
            'link'  => '/tree',
            'check' => function () {
                return true;
            },
        ],

        [
            'title' => 'Статьи',
            'icon'  => 'building',
            'link'  => '/articles',
            'check' => function () {
                return true;
            },
        ],

        [
            'title' => 'Настройки',
            'icon'  => 'cog',
            'submenu' => [
                [
                    'title' => 'Управление',
                    'submenu' => [
                        [
                            'title' => 'Общее',
                            'link'  => '/settings/settings_all?group=general',
                            'check' => function () {
                                return true;
                            },
                        ],
                        /*  array(
                             'title' => 'SEO',
                             'link'  => '/settings/settings_all?group=seo',
                             'check' => function() {
                                 return true;
                             }
                         ),
                        array(
                             'title' => 'Изображения',
                             'link'  => '/settings/settings_all?group=graphics',
                             'check' => function() {
                                 return true;
                             }
                         ),
                         array(
                             'title' => 'Безопасность',
                             'link'  => '/settings/settings_all?group=security',
                             'check' => function() {
                                 return true;
                             }
                         ),
                         array(
                             'title' => 'Цены',
                             'link'  => '/settings/settings_all?group=price',
                             'check' => function() {
                                 return true;
                             }
                         ),*/
                    ],
                    'check' => function () {
                        return true;
                    },
                ],
                [
                    'title' => 'Переводы CMS',
                    'link'  => '/translations_cms/phrases',
                    'check' => function () {
                        return true;
                    },
                ],
                [
                    'title' => 'Контроль изменений',
                    'link'  => '/revisions',
                    'check' => function () {
                        return true;
                    },
                ],
            ],
        ],

        [
            'title' => 'Упр. пользователями',
            'icon'  => 'user',
            'submenu' => [
                [
                    'title' => 'Пользователи',
                    'link'  => '/users',
                    'check' => function () {
                        return true;
                    },
                ],
                [
                    'title' => 'Группы',
                    'link'  => '/groups',
                    'check' => function () {
                        return true;
                    },
                ],
            ],
        ],
    ],
];
