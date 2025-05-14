<?php
/*
 * Plugin Name: My Custom Media Plugin
 * Description: Добавляет кастомные фильтры в медиа-библиотеку WordPress и кастомный тип постов.
 * Version: 1.1.0
 * Author: t_che
 * Text Domain: my-custom-media-plugin
 */

// Предотвращаем прямой доступ к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Определяем константы для путей и текста
define( 'MCMP_TEXT_DOMAIN', 'my-custom-media-plugin' );
define( 'MCMP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Подключаем файлы с функциональностью
require_once MCMP_PLUGIN_DIR . 'includes/custom-post-type.php';
require_once MCMP_PLUGIN_DIR . 'includes/admin-filters.php';

// Регистрируем скрипты и стили для админки
function mcmp_enqueue_scripts() {
    // Проверяем, что мы на странице медиа-библиотеки
    if ( get_current_screen()->base !== 'upload' ) {
        return;
    }

    // Регистрируем JavaScript
    wp_enqueue_script(
        'mcmp-admin-script',
        plugins_url( 'assets/js/admin-script.js', __FILE__ ),
        ['jquery'], // Зависимость от jQuery
        '1.1.0',    // Версия скрипта
        true        // Подключение в футере
    );

    // Локализуем данные для JavaScript
    wp_localize_script(
        'mcmp-admin-script',
        'mcmpAjax',
        ['ajaxurl' => admin_url( 'admin-ajax.php' )] // URL для AJAX-запросов
    );

    // Регистрируем стили
    wp_enqueue_style(
        'mcmp-admin-style',
        plugins_url( 'assets/css/admin-style.css', __FILE__ ),
        [],
        '1.1.0'
    );
}
add_action( 'admin_enqueue_scripts', 'mcmp_enqueue_scripts' );