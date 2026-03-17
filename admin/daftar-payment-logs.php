// path: admin/daftar-payment-logs.php
<?php
require_once '../library/session_login_admin.php';
require_once '../library/database.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$db = new Database();

// Get total records
$db->query("SELECT COUNT(*) as total FROM payment_logs");
$total = $db->single()['total'];
$totalPages = ceil($total / $limit);

// Get payment logs
$db->query("SELECT pl.*, d.user_id, d.amount, d.payment_id 
            FROM payment_logs pl 
            LEFT JOIN deposits d ON pl.payment_id = d.payment_id 
            ORDER BY pl.created_at DESC 
            LIMIT :offset, :limit");
$db->bind(':offset', $offset);
$db->bind(':limit', $limit);
$logs = $db->result();

?>
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Payment Logs</h5>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Payment ID</th>
                        <th>User ID</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Message</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= $log['id'] ?></td>
                        <td><?= htmlspecialchars($log['payment_id']) ?></td>
                        <td><?= $log['user_id'] ?></td>
                        <td><?= number_format($log['amount'], 2) ?></td>
                        <td>
                            <span class="badge badge-<?= 
                                $log['status'] == 'success' ? 'success' : 
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