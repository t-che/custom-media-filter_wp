<?php
// Предотвращаем прямой доступ к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Добавляем кастомные фильтры в медиа-библиотеку
function mcmp_add_custom_media_filters() {
    // Проверяем, что мы на странице медиа-библиотеки
    if ( get_current_screen()->base !== 'upload' ) {
        return;
    }

    global $wpdb; // Подключаем глобальную базу данных
    // Получаем уникальные годы из медиа-файлов
    $years = $wpdb->get_col(
        "SELECT DISTINCT YEAR(post_date) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_date > '1970-01-01' ORDER BY post_date DESC"
    );

    // Получаем текущие значения фильтров из GET-запроса
    $media_type = isset( $_GET['mcmp_media_type'] ) ? sanitize_text_field( $_GET['mcmp_media_type'] ) : '';
    $year = isset( $_GET['mcmp_year'] ) ? sanitize_text_field( $_GET['mcmp_year'] ) : '';
    ?>
    <label for="mcmp-media-type" class="screen-reader-text"><?php _e( 'Тип медиа', MCMP_TEXT_DOMAIN ); ?></label>
    <select name="mcmp_media_type" id="mcmp-media-type">
        <option value="" <?php selected( $media_type, '' ); ?>>Все медиа</option>
        <option value="custom" <?php selected( $media_type, 'custom' ); ?>>Медиа Кастом постов</option>
    </select>

    <label for="mcmp-year" class="screen-reader-text"><?php _e( 'Год', MCMP_TEXT_DOMAIN ); ?></label>
    <select name="mcmp_year" id="mcmp-year">
        <option value="" <?php selected( $year, '' ); ?>>Все годы</option>
        <?php foreach ( $years as $y ) : ?>
            <option value="<?php echo esc_attr( $y ); ?>" <?php selected( $year, $y ); ?>><?php echo esc_html( $y ); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}
add_action( 'restrict_manage_posts', 'mcmp_add_custom_media_filters' );

// Настраиваем данные для интерфейса режима сетки
function mcmp_media_view_settings( $settings ) {
    global $wpdb;
    $settings['mcmpYears'] = $wpdb->get_col(
        "SELECT DISTINCT YEAR(post_date) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_date > '1970-01-01' ORDER BY post_date DESC"
    );
    $settings['mcmpMediaType'] = isset( $_GET['mcmp_media_type'] ) ? sanitize_text_field( $_GET['mcmp_media_type'] ) : '';
    $settings['mcmpYear'] = isset( $_GET['mcmp_year'] ) ? sanitize_text_field( $_GET['mcmp_year'] ) : '';

    return $settings;
}
add_filter( 'media_view_settings', 'mcmp_media_view_settings' );

// Модифицируем запрос медиа-библиотеки для режима списка
function mcmp_modify_media_query( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() || $query->get( 'post_type' ) !== 'attachment' ) {
        return;
    }

    $media_type = isset( $_REQUEST['mcmp_media_type'] ) ? sanitize_text_field( $_REQUEST['mcmp_media_type'] ) : '';
    $year = isset( $_REQUEST['mcmp_year'] ) ? intval( $_REQUEST['mcmp_year'] ) : '';

    if ( $media_type === 'custom' ) {
        $query->set( 'post_parent__in', mcmp_get_custom_post_ids() );
    }
    if ( $year ) {
        $query->set( 'year', $year );
    }
}
add_action( 'pre_get_posts', 'mcmp_modify_media_query' );

// Получаем ID кастомных постов
function mcmp_get_custom_post_ids() {
    $posts = get_posts([
        'post_type'      => 'custom',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post_status'    => 'any',
    ]);
    return $posts ? $posts : [0];
}

// Модифицируем AJAX-запрос для режима сетки
function mcmp_modify_ajax_query( $query ) {
    if ( ! is_admin() || $_REQUEST['action'] !== 'query-attachments' ) {
        return $query;
    }

    $media_type = isset( $_REQUEST['query']['mcmp_media_type'] ) ? sanitize_text_field( $_REQUEST['query']['mcmp_media_type'] ) : '';
    $year = isset( $_REQUEST['query']['mcmp_year'] ) ? intval( $_REQUEST['query']['mcmp_year'] ) : '';

    if ( $media_type === 'custom' ) {
        $query['post_parent__in'] = mcmp_get_custom_post_ids();
    }
    if ( $year ) {
        $query['year'] = $year;
    }

    return $query;
}
add_filter( 'ajax_query_attachments_args', 'mcmp_modify_ajax_query' );