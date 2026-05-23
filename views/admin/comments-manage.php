<?php
$pageTitle = 'Comments Manager';
$currentPage = 'comments';

ob_start();
?>

<div class="comments-filters">
    <form method="GET" action="/admin/comments" class="filter-form">
        <select name="filter" onchange="this.form.submit()">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Comments</option>
            <option value="approved" <?= $filter === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="flagged" <?= $filter === 'flagged' ? 'selected' : '' ?>>Flagged</option>
            <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
        </select>
        
        <select name="type" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="movie" <?= $mediaType === 'movie' ? 'selected' : '' ?>>Movies</option>
            <option value="tv" <?= $mediaType === 'tv' ? 'selected' : '' ?>>TV Shows</option>
        </select>
        
        <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-primary">Filter</button>
    </form>
</div>

<div class="data-card">
    <div class="data-header">
        <h3 class="data-title">All Comments (<?= number_format($total) ?>)</h3>
        <button class="btn-danger" onclick="bulkDelete()">Delete Selected</button>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAll" onchange="toggleAll(this)"></th>
                <th>ID</th>
                <th>TMDB ID</th>
                <th>Type</th>
                <th>Username</th>
                <th>Comment</th>
                <th>Rating</th>
                <th>Status</th>
                <th>IP Address</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comments as $comment): ?>
            <tr>
                <td><input type="checkbox" class="comment-checkbox" value="<?= $comment['id'] ?>"></td>
                <td><?= $comment['id'] ?></td>
                <td><?= $comment['tmdb_id'] ?></td>
                <td><?= htmlspecialchars($comment['media_type']) ?></td>
                <td><?= htmlspecialchars($comment['username']) ?></td>
                <td>
                    <span class="comment-text"><?= htmlspecialchars(substr($comment['comment_text'], 0, 80)) ?><?= strlen($comment['comment_text']) > 80 ? '...' : '' ?></span>
                </td>
                <td>
                    <?php if ($comment['rating']): ?>
                    <span class="rating-stars"><?= str_repeat('★', $comment['rating']) . str_repeat('☆', 5 - $comment['rating']) ?></span>
                    <?php else: ?>
                    <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($comment['is_flagged']): ?>
                    <span class="status-badge flagged">Flagged</span>
                    <?php elseif ($comment['is_approved']): ?>
                    <span class="status-badge approved">Approved</span>
                    <?php else: ?>
                    <span class="status-badge pending">Pending</span>
                    <?php endif; ?>
                </td>
                <td><code><?= htmlspecialchars($comment['ip_address'] ?? '') ?></code></td>
                <td><?= date('Y-m-d H:i', strtotime($comment['created_at'])) ?></td>
                <td>
                    <div class="action-buttons">
                        <?php if (!$comment['is_approved']): ?>
                        <button class="btn-icon btn-approve" onclick="updateStatus(<?= $comment['id'] ?>, 'approve')" title="Approve">✓</button>
                        <?php endif; ?>
                        <?php if (!$comment['is_flagged']): ?>
                        <button class="btn-icon btn-flag" onclick="updateStatus(<?= $comment['id'] ?>, 'flag')" title="Flag">⚠</button>
                        <?php endif; ?>
                        <button class="btn-icon btn-delete" onclick="deleteComment(<?= $comment['id'] ?>)" title="Delete">🗑</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>&filter=<?= $filter ?>&type=<?= $mediaType ?>&search=<?= urlencode($search) ?>" 
           class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.comments-filters {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.filter-form {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-form select,
.filter-form input {
    background: var(--admin-bg-tertiary);
    border: 1px solid var(--admin-border);
    border-radius: 6px;
    padding: 8px 12px;
    color: var(--admin-text-primary);
    font-size: 14px;
}

.data-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.comment-text {
    max-width: 300px;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.rating-stars {
    color: var(--admin-warning);
    font-size: 14px;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.approved { background: rgba(46, 160, 67, 0.15); color: var(--admin-success); }
.status-badge.flagged { background: rgba(255, 77, 77, 0.15); color: var(--admin-error); }
.status-badge.pending { background: rgba(255, 153, 0, 0.15); color: var(--admin-warning); }

.action-buttons {
    display: flex;
    gap: 4px;
}

.btn-icon {
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.btn-approve { background: rgba(46, 160, 67, 0.2); color: var(--admin-success); }
.btn-flag { background: rgba(255, 153, 0, 0.2); color: var(--admin-warning); }
.btn-delete { background: rgba(255, 77, 77, 0.2); color: var(--admin-error); }

.btn-icon:hover { transform: scale(1.1); }
</style>

<script>
function toggleAll(checkbox) {
    document.querySelectorAll('.comment-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
    });
}

function deleteComment(id) {
    if (!confirm('Are you sure you want to delete this comment?')) return;
    
    fetch('/admin/comments/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id})
    }).then(() => location.reload());
}

function bulkDelete() {
    const ids = Array.from(document.querySelectorAll('.comment-checkbox:checked')).map(cb => cb.value);
    if (ids.length === 0) {
        alert('No comments selected');
        return;
    }
    
    if (!confirm(`Delete ${ids.length} comments?`)) return;
    
    fetch('/admin/comments/bulk-delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ids})
    }).then(() => location.reload());
}

function updateStatus(id, action) {
    // Implement status update logic
    console.log('Update', id, action);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>
