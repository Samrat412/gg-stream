<?php
$pageTitle = 'Content Requests';
$currentPage = 'requests';

ob_start();
?>

<div class="requests-filters">
    <form method="GET" action="/admin/requests" class="filter-form">
        <select name="status" onchange="this.form.submit()">
            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Status</option>
            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="reviewing" <?= $status === 'reviewing' ? 'selected' : '' ?>>Reviewing</option>
            <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
        
        <select name="type" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="movie" <?= $type === 'movie' ? 'selected' : '' ?>>Movies</option>
            <option value="tv" <?= $type === 'tv' ? 'selected' : '' ?>>TV Shows</option>
        </select>
        
        <button type="submit" class="btn-primary">Filter</button>
    </form>
</div>

<div class="data-card">
    <h3 class="data-title">All Requests (<?= number_format($total) ?>)</h3>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Type</th>
                <th>Description</th>
                <th>Votes</th>
                <th>Status</th>
                <th>Admin Notes</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $request): ?>
            <tr>
                <td><?= $request['id'] ?></td>
                <td><strong><?= htmlspecialchars($request['title']) ?></strong></td>
                <td><?= htmlspecialchars($request['request_type']) ?></td>
                <td>
                    <span class="desc-text"><?= htmlspecialchars(substr($request['description'] ?? '', 0, 60)) ?><?= strlen($request['description'] ?? '') > 60 ? '...' : '' ?></span>
                </td>
                <td><span class="vote-count">👍 <?= $request['votes'] ?></span></td>
                <td>
                    <select class="status-select" data-id="<?= $request['id'] ?>" onchange="updateStatus(this)">
                        <option value="pending" <?= $request['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="reviewing" <?= $request['status'] === 'reviewing' ? 'selected' : '' ?>>Reviewing</option>
                        <option value="approved" <?= $request['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="completed" <?= $request['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="rejected" <?= $request['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="notes-input" 
                           value="<?= htmlspecialchars($request['admin_notes'] ?? '') ?>" 
                           placeholder="Add notes..."
                           data-id="<?= $request['id'] ?>"
                           onchange="saveNotes(this)">
                </td>
                <td><?= date('Y-m-d', strtotime($request['created_at'])) ?></td>
                <td>
                    <button class="btn-icon btn-delete" onclick="deleteRequest(<?= $request['id'] ?>)" title="Delete">🗑</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>&status=<?= $status ?>&type=<?= $type ?>" 
           class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.requests-filters {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.filter-form {
    display: flex;
    gap: 12px;
}

.filter-form select {
    background: var(--admin-bg-tertiary);
    border: 1px solid var(--admin-border);
    border-radius: 6px;
    padding: 8px 12px;
    color: var(--admin-text-primary);
    font-size: 14px;
}

.desc-text {
    max-width: 200px;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--admin-text-secondary);
}

.vote-count {
    background: rgba(0, 168, 225, 0.15);
    color: var(--admin-accent);
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 13px;
}

.status-select {
    background: var(--admin-bg-tertiary);
    border: 1px solid var(--admin-border);
    border-radius: 4px;
    padding: 4px 8px;
    color: var(--admin-text-primary);
    font-size: 12px;
    cursor: pointer;
}

.notes-input {
    width: 150px;
    background: var(--admin-bg-tertiary);
    border: 1px solid var(--admin-border);
    border-radius: 4px;
    padding: 4px 8px;
    color: var(--admin-text-primary);
    font-size: 12px;
}

.pagination {
    display: flex;
    gap: 8px;
    justify-content: center;
    margin-top: 20px;
}

.page-link {
    padding: 8px 12px;
    background: var(--admin-bg-tertiary);
    border-radius: 4px;
    color: var(--admin-text-primary);
    text-decoration: none;
}

.page-link.active {
    background: var(--admin-accent);
}
</style>

<script>
function updateStatus(select) {
    const id = select.dataset.id;
    const status = select.value;
    
    fetch('/admin/requests/status', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id, status})
    }).then(() => {
        // Show success feedback
        select.style.borderColor = 'var(--admin-success)';
        setTimeout(() => select.style.borderColor = '', 1000);
    });
}

function saveNotes(input) {
    const id = input.dataset.id;
    const admin_notes = input.value;
    
    fetch('/admin/requests/status', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id, admin_notes})
    });
}

function deleteRequest(id) {
    if (!confirm('Are you sure you want to delete this request?')) return;
    
    fetch('/admin/requests/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id})
    }).then(() => location.reload());
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>
