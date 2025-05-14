jQuery(document).ready(function($) {
    // Проверяем доступность объекта mcmpAjax
    if (typeof mcmpAjax === 'undefined') {
        return;
    }

    // Функция добавления фильтров в режим сетки
    function addCustomFiltersToGrid() {
        // Проверяем, что режим сетки активен и фильтры еще не добавлены
        if (!$('.media-frame.mode-grid').length || $('#mcmp-media-type-grid').length) {
            return;
        }

        // Получаем данные из настроек
        var years = wp.media.view.settings.mcmpYears || [];
        var mediaType = wp.media.view.settings.mcmpMediaType || '';
        var year = wp.media.view.settings.mcmpYear || '';

        // Формируем HTML для фильтров
        var html = `
            <label for="mcmp-media-type-grid" class="screen-reader-text">Тип медиа</label>
            <select id="mcmp-media-type-grid" class="attachment-filters">
                <option value="" ${mediaType === '' ? 'selected' : ''}>Все медиа</option>
                <option value="custom" ${mediaType === 'custom' ? 'selected' : ''}>Медиа Кастом постов</option>
            </select>
            <label for="mcmp-year-grid" class="screen-reader-text">Год</label>
            <select id="mcmp-year-grid" class="attachment-filters">
                <option value="" ${year === '' ? 'selected' : ''}>Все годы</option>
        `;

        // Добавляем опции для годов
        years.forEach(function(y) {
            html += `<option value="${y}" ${year === y.toString() ? 'selected' : ''}>${y}</option>`;
        });

        html += `</select>`;

        // Вставляем фильтры в интерфейс
        $('.media-toolbar-secondary').append(html);
    }

    // Инициализация фильтров в режиме сетки
    function initGridFilters() {
        // Проверяем готовность медиа-библиотеки
        if (wp.media && $('.media-frame.mode-grid').length) {
            console.log('Инициализация фильтров в режиме сетки');
            addCustomFiltersToGrid();
        } else {
            setTimeout(initGridFilters, 100); // Повторная попытка через 100 мс
        }
    }

    initGridFilters();

    // Обработчик изменения фильтров
    $(document).on('change', '#mcmp-media-type, #mcmp-year, #mcmp-media-type-grid, #mcmp-year-grid', function() {
        // Получаем значения фильтров
        var mediaType = $('#mcmp-media-type-grid').val() || $('#mcmp-media-type').val() || '';
        var year = $('#mcmp-year-grid').val() || $('#mcmp-year').val() || '';
        var isGrid = $('.media-frame.mode-grid').length > 0;

        // Обновляем URL в истории браузера
        var url = new URL(window.location.href);
        url.searchParams.set('mcmp_media_type', mediaType);
        url.searchParams.set('mcmp_year', year);
        history.pushState({}, '', url);

        // Логика фильтрации в зависимости от режима
        if (isGrid) {
            var frame = wp.media.frame;
            if (frame) {
                var collection = frame.content.get().collection;
                if (collection) {
                    // Устанавливаем параметры для фильтрации
                    collection.props.set({ mcmp_media_type: mediaType, mcmp_year: year });
                    // Обновляем коллекцию через AJAX
                    collection.fetch({ reset: true });
                }
            }
        } else {
            // Перезагрузка страницы в режиме списка
            window.location.href = url.toString();
        }
    });
});