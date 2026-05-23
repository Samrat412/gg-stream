<?php
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

ob_start();
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon total">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-label">Total Visitors</span>
            <span class="stat-value"><?= number_format($totalVisitors) ?></span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon today">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-label">Today's Visitors</span>
            <span class="stat-value"><?= number_format($todayVisitors) ?></span>
            <span class="stat-change <?= $changePercent >= 0 ? 'positive' : 'negative' ?>">
                <?= $changePercent >= 0 ? '+' : '' ?><?= $changePercent ?>% vs yesterday
            </span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon live">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-label">Online Now</span>
            <span class="stat-value"><?= number_format($onlineNow) ?></span>
            <span class="stat-sub">Last 5 minutes</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon views">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-label">Today's Page Views</span>
            <span class="stat-value"><?= number_format($todayPageViews) ?></span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon week">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 20V10M12 20V4M6 20v-6"/>
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-label">This Week</span>
            <span class="stat-value"><?= number_format($weekTotal) ?></span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon month">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-label">This Month</span>
            <span class="stat-value"><?= number_format($monthTotal) ?></span>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="charts-row">
    <div class="chart-card">
        <h3 class="chart-title">Hourly Traffic (Today)</h3>
        <canvas id="hourlyChart"></canvas>
    </div>
    
    <div class="chart-card">
        <h3 class="chart-title">Last 7 Days Trend</h3>
        <canvas id="trendChart"></canvas>
    </div>
</div>

<!-- Device & Country Breakdown -->
<div class="breakdown-row">
    <div class="breakdown-card">
        <h3 class="breakdown-title">Device Breakdown</h3>
        <canvas id="deviceChart"></canvas>
    </div>
    
    <div class="breakdown-card">
        <h3 class="breakdown-title">Top Countries</h3>
        <div class="country-list">
            <?php foreach (array_slice($countryBreakdown, 0, 10) as $country): ?>
            <div class="country-item">
                <span class="country-code"><?= htmlspecialchars($country['country_code'] ?? 'XX') ?></span>
                <span class="country-count"><?= number_format($country['count']) ?></span>
                <div class="country-bar" style="width: <?= max(5, min(100, ($country['count'] / max(1, $countryBreakdown[0]['count'] ?? 1)) * 100)) ?>%"></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Top Pages Table -->
<div class="data-card">
    <h3 class="data-title">Top Pages Today</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Rank</th>
                <th>Page Path</th>
                <th>Views</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($topPages as $index => $page): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><code><?= htmlspecialchars($page['page_path']) ?></code></td>
                <td><?= number_format($page['views']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Recent Activity & Active Sessions -->
<div class="activity-row">
    <div class="activity-card">
        <h3 class="activity-title">Recent Activity</h3>
        <div class="activity-list">
            <?php foreach ($recentActivity as $activity): ?>
            <div class="activity-item">
                <span class="activity-path"><?= htmlspecialchars($activity['page_path']) ?></span>
                <span class="activity-meta">
                    <span class="activity-device"><?= htmlspecialchars($activity['device_type'] ?? 'desktop') ?></span>
                    <?php if (!empty($activity['country_code'])): ?>
                    <span class="activity-country"><?= htmlspecialchars($activity['country_code']) ?></span>
                    <?php endif; ?>
                    <span class="activity-time"><?= date('H:i', strtotime($activity['created_at'])) ?></span>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="activity-card">
        <h3 class="activity-title">Active Sessions</h3>
        <div class="sessions-list">
            <?php foreach ($activeSessions as $session): ?>
            <div class="session-item">
                <span class="session-path"><?= htmlspecialchars($session['page_path']) ?></span>
                <span class="session-meta">
                    <span class="session-device"><?= htmlspecialchars($session['device_type'] ?? 'desktop') ?></span>
                    <span class="session-seen"><?= date('H:i:s', strtotime($session['last_seen'])) ?></span>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <h3 class="actions-title">Quick Actions</h3>
    <div class="actions-grid">
        <a href="/admin/cache" class="action-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/>
                <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/>
                <path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>
            </svg>
            Clear Cache
        </a>
        <a href="/admin/seo" class="action-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
            SEO Audit
        </a>
        <a href="/admin/seo/indexnow" class="action-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Trigger IndexNow
        </a>
        <a href="/admin/settings" class="action-btn <?= $maintenanceMode ? 'active' : '' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
            </svg>
            <?= $maintenanceMode ? 'Maintenance Mode ON' : 'Maintenance Mode' ?>
        </a>
    </div>
</div>

<script>
// Hourly Chart
const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
new Chart(hourlyCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(fn($h) => sprintf('%02d:00', $h['stat_hour']), $hourlyStats)) ?>,
        datasets: [{
            label: 'Page Views',
            data: <?= json_encode(array_column($hourlyStats, 'page_views_count')) ?>,
            backgroundColor: '#00A8E1',
            borderRadius: 4
        }, {
            label: 'Unique Visitors',
            data: <?= json_encode(array_column($hourlyStats, 'unique_visitors')) ?>,
            backgroundColor: '#006699',
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: true, position: 'top' }
        },
        scales: {
            y: { beginAtZero: true, grid: { color: '#2A3A4A' } },
            x: { grid: { display: false } }
        }
    }
});

// Trend Chart
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($last7Days, 'visit_date')) ?>,
        datasets: [{
            label: 'Visitors',
            data: <?= json_encode(array_column($last7Days, 'visit_count')) ?>,
            borderColor: '#00A8E1',
            backgroundColor: 'rgba(0, 168, 225, 0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true, grid: { color: '#2A3A4A' } },
            x: { grid: { display: false } }
        }
    }
});

// Device Chart
const deviceCtx = document.getElementById('deviceChart').getContext('2d');
new Chart(deviceCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($deviceBreakdown, 'device_type')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($deviceBreakdown, 'count')) ?>,
            backgroundColor: ['#00A8E1', '#FF9900', '#2EA043'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: true, position: 'bottom' }
        }
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>
