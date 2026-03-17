// path: admin/daftar-services-sync.php
<?php
require_once '../library/session_login_admin.php';
require_once '../library/database.php';

$db = new Database();

// Get services
$db->query("SELECT * FROM services ORDER BY category, service_name");
$services = $db->result();

?>
<div class="card">
    <div class="card-body">
        <h5 class="card-title">SMM Services</h5>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Service Code</th>
                        <th>Service Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Provider</th>
                        <th>Available</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?= htmlspecialchars($service['service_code']) ?></td>
                        <td><?= htmlspecialchars($service['service_name']) ?></td>
                        <td><?= htmlspecialchars($service['category']) ?></td>
                        <td><?= number_format($service['price'], 2) ?></td>
                        <td><?= htmlspecialchars($service['provider']) ?></td>
                        <td>
                            <span class="badge badge-<?= $service['available'] ? 'success' : 'danger' ?>">
                                <?= $service['available'] ? 'Yes' : 'No' ?>
                            </span>
                        </td>
                        <td><?= date('Y-m-d H:i:s', strtotime($service['updated_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <button class="btn btn-primary" onclick="syncServices()">
                <i class="fas fa-sync-alt"></i> Sync Services Now
            </button>
        </div>
    </div>
</div>

<script>
function syncServices() {
    if (confirm('Are you sure you want to sync services now?')) {
        $.post('../ajax/sync-services.php', function(response) {
            alert('Services synced successfully!');
            location.reload();
        }).fail(function() {
            alert('Error syncing services');
        });
    }
}
</script>