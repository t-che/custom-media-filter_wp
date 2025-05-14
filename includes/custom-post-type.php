<?php
// Предотвращаем прямой доступ к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Регистрируем кастомный тип поста
function mcmp_register_custom_post_type() {
    // Метки для интерфейса
    $labels = [
        'name'          => __( 'Кастомные посты', MCMP_TEXT_DOMAIN ),
        'singular_name' => __( 'Кастомный пост', MCMP_TEXT_DOMAIN ),
        'all_items'     => __( 'Все кастомные посты', MCMP_TEXT_DOMAIN ),
        'add_new'       => __( 'Добавить новый', MCMP_TEXT_DOMAIN ),
        'add_new_item'  => __( 'Добавить новый кастомный пост', MCMP_TEXT_DOMAIN ),
    ];

    // Аргументы для регистрации
    $args = [
        'labels'    => $labels,
        'public'    => true,           // Доступен публично
        'show_ui'   => true,           // Показывать в админке
        'show_in_menu' => true,        // Показывать в меню
        'supports'  => ['title', 'thumbnail'], // Поддерживаемые поля
    ];

    register_post_type( 'custom', $args ); // Регистрируем тип поста
}
add_action( 'init', 'mcmp_register_custom_post_type' ); // Запускаем при инициализации