// path: admin/daftar-order-logs.php
<?php
require_once '../library/session_login_admin.php';
require_once '../library/database.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$db = new Database();

// Get total records
$db->query("SELECT COUNT(*) as total FROM order_logs");
$total = $db->single()['total'];
$totalPages = ceil($total / $limit);

// Get order logs
$db->query("SELECT ol.*, o.user_id, o.service_name 
            FROM order_logs ol 
            LEFT JOIN orders o ON ol.order_id = o.id 
            ORDER BY ol.created_at DESC 
            LIMIT :offset, :limit");
$db->bind(':offset', $offset);
$db->bind(':limit', $limit);
$logs = $db->result();

?>
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Order Logs</h5>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Order ID</th>
                        <th>User ID</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Message</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= $log['id'] ?></td>
                        <td><?= $log['order_id'] ?></td>
                        <td><?= $log['user_id'] ?></td>
                        <td><?= htmlspecialchars($log['service_name']) ?></td>
                        <td>
                            <span class="badge badge-<?= 
                                $log['status'] == 'completed' ? 'success' : 
                                ($log['status'] == 'pending' ? 'warning' : 'danger')
                            ?>">
                                <?= ucfirst($log['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars(substr($log['message'], 0, 100)) ?>...</td>
                        <td><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>