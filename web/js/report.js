$(function () {
    initDashboard();

    $(document).on('click', '#refresh-btn', function () {
        refreshStats($(this));
    });
});

/**
 * Стартовая загрузка
 */
function initDashboard() {
    toggleLoader(true);
    loadStats(() => toggleLoader(false));
}

/**
 * Загрузка данных с сервера
 */
function loadStats(callback = null) {
    $.ajax({
        url: '/report/games-stats',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response && response.success) {
                renderAll(response);
            }
            if (callback) callback();
        },
        error: function () {
            alert('Error loading statistics');
            if (callback) callback();
        }
    });
}

/**
 * Глaвный распределитель рендера
 */
function renderAll(response) {
    const d = response.data;
    const labels = response.labels ? response.labels.statuses : {};

    renderCounters(d);
    renderRatingTable(d.reviews_distribution);
    renderSessionsTable(d.popular_games_sessions, labels);
    renderSubscriptionsTable(d.popular_games_subscriptions);
    renderActivityTable(d.recent_activity);
}

function renderCounters(d) {
    $('#total-games').text(d.total_games || 0);
    $('#total-sessions').text(d.total_sessions || 0);
    $('#completed-sessions').text(d.completed_sessions || 0);
    $('#cancelled-sessions').text(d.cancelled_sessions || 0);
    $('#total-subscriptions').text(d.total_subscriptions || 0);
    $('#total-reviews').text(d.total_reviews || 0);
    $('#avg-rating').text(parseFloat(d.avg_rating || 0).toFixed(2));
}

function renderRatingTable(data) {
    renderTable('#reviews-stats-tbody', data, (item) => `
        <tr>
            <td>${item.rating} ★</td>
            <td>${item.count}</td>
            <td>
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar" style="width: ${item.percent}%"></div>
                </div>
                <small>${item.percent}%</small>
            </td>
        </tr>
    `);
}

function renderSessionsTable(data, labels) {
    renderTable('#popular-games-sessions-tbody', data, (item) => {
        const label = labels[item.status] || item.status || '-';
        return `
            <tr>
                <td>${item.title}</td>
                <td>${item.sessions_count}</td>
                <td><span class="badge ${getStatusClass(item.status)}">${label}</span></td>
            </tr>
        `;
    });
}

function renderSubscriptionsTable(data) {
    renderTable('#popular-games-subscriptions-tbody', data, (item) => `
        <tr>
            <td>${item.title}</td>
            <td>${item.total_subs}</td>
            <td>${item.active_subs}</td>
            <td>${item.total_subs > 0 ? ((item.active_subs / item.total_subs) * 100).toFixed(1) : 0}%</td>
        </tr>
    `);
}

function renderActivityTable(data) {
    renderTable('#recent-activity-tbody', data, (item) => `
        <tr>
            <td>${item.day}</td>
            <td class="text-center">${item.new_games}</td>
            <td class="text-center">${item.new_sessions}</td>
            <td class="text-center">${item.new_subscriptions}</td>
            <td class="text-center">${item.new_reviews}</td>
        </tr>
    `);
}

function toggleLoader(show) {
    const $container = $('.data-container');
    const $loader = $('.loading-placeholder');

    if (show) {
        $container.hide();
        $loader.show();
    } else {
        $loader.fadeOut(200, () => $container.fadeIn(200));
    }
}

function refreshStats($btn) {
    const originalText = $btn.text();
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Обновление...');

    $.ajax({
        url: '/report/refresh',
        type: 'POST',
        dataType: 'json',
        data: {[yii.getCsrfParam()]: yii.getCsrfToken()},
        success: function (response) {
            if (response.success) {
                loadStats();
                if (window.toastr) toastr.success(response.message);
            } else {
                alert(response.message);
            }
        },
        error: () => alert('Ошибка сервера'),
        complete: () => $btn.prop('disabled', false).text(originalText)
    });
}

function renderTable(selector, data, rowTemplate) {
    const $tbody = $(selector);
    if (!data || data.length === 0) {
        $tbody.html('<tr><td colspan="10" class="text-center text-muted">No data available</td></tr>');
        return;
    }
    $tbody.html(data.map(item => rowTemplate(item)).join(''));
}

function getStatusClass(status) {
    if (!status) return 'bg-secondary';
    const statusMap = {
        'active': 'bg-success',
        'planned': 'bg-info',
        'completed': 'bg-primary',
        'cancelled': 'bg-danger',
        'deleted': 'bg-secondary',
    };
    return statusMap[status.toLowerCase()] || 'bg-secondary';
}