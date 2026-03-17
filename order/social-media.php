// path: order/social-media.php
<?php
require_once '../library/session_user.php';
require_once '../library/database.php';
require_once '../library/ZaynFlazzService.php';
require_once '../library/function.php';

$db = new Database();
$zaynflazz = new ZaynFlazzService();

// Get user services
$services = $zaynflazz->getServices();

// Process order form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = $_POST['service_id'];
    $target = $_POST['target'];
    $quantity = $_POST['quantity'];
    
    // Check user balance
    $db->query("SELECT balance FROM users WHERE id = :user_id");
    $db->bind(':user_id', $_SESSION['user_id']);
    $user = $db->single();
    
    $service = null;
    foreach ($services['data'] as $s) {
        if ($s['id'] == $serviceId) {
            $service = $s;
            break;
        }
    }
    
    if (!$service) {
        die('Invalid service');
    }
    
    $totalCost = $service['price'] * $quantity;
    
    if ($user['balance'] < $totalCost) {
        die('Insufficient balance');
    }
    
    // Deduct balance
    $newBalance = $user['balance'] - $totalCost;
    $db->query("UPDATE users SET balance = :balance WHERE id = :user_id");
    $db->bind(':balance', $newBalance);
    $db->bind(':user_id', $_SESSION['user_id']);
    $db->execute();
    
    // Create order
    $db->query("INSERT INTO orders (user_id, service_id, service_name, target, quantity, amount, status) 
                VALUES (:user_id, :service_id, :service_name, :target, :quantity, :amount, 'pending')");
    $db->bind(':user_id', $_SESSION['user_id']);
    $db->bind(':service_id', $serviceId);
    $db->bind(':service_name', $service['name']);
    $db->bind(':target', $target);
    $db->bind(':quantity', $quantity);
    $db->bind(':amount', $totalCost);
    $db->execute();
    
    $orderId = $db->lastInsertId();
    
    // Create provider order
    $response = $zaynflazz->createOrder($serviceId, $target, $quantity);
    
    if (isset($response['order_id'])) {
        $providerOrderId = $response['order_id'];
        
        // Update order with provider order ID
        $db->query("UPDATE orders SET provider_order_id = :provider_id WHERE id = :order_id");
        $db->bind(':provider_id', $providerOrderId);
        $db->bind(':order_id', $orderId);
        $db->execute();
        
        // Create provider order record
        $db->query("INSERT INTO provider_orders (order_id, provider_name, provider_order_id, status) 
                    VALUES (:order_id, 'zaynflazz', :provider_id, 'pending')");
        $db->bind(':order_id', $orderId);
        $db->bind(':provider_id', $providerOrderId);
        $db->execute();
    }
    
    header('Location: ../history/order.php');
    exit;
}
?>
<div class="container mt-4">
    <h2>Social Media Services</h2>
    
    <form method="post" class="mt-4">
        <div class="form-group">
            <label for="service_id">Service</label>
            <select class="form-control" id="service_id" name="service_id" required>
                <?php foreach ($services['data'] as $service): ?>
                <option value="<?= $service['id'] ?>" data-price="<?= $service['price'] ?>">
                    <?= htmlspecialchars($service['name']) ?> - $<?= $service['price'] ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="target">Target (Username/URL)</label>
            <input type="text" class="form-control" id="target" name="target" required>
        </div>
        
        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" value="100" min="1" required>
        </div>
        
        <div class="form-group">
            <label for="total">Total Cost</label>
            <input type="text" class="form-control" id="total" name="total" readonly>
        </div>
        
        <button type="submit" class="btn btn-primary">Place Order</button>
    </form>
</div>

<script>
document.getElementById('service_id').addEventListener('change', function() {
    const price = this.options[this.selectedIndex].dataset.price;
    const quantity = document.getElementById('quantity').value;
    const total = price * quantity;
    document.getElementById('total').value = '$' + total.toFixed(2);
});

document.getElementById('quantity').addEventListener('input', function() {
    const price = document.getElementById('service_id').options[document.getElementById('service_id').selectedIndex].dataset.price;
    const quantity = this.value;
    const total = price * quantity;
    document.getElementById('total').value = '$' + total.toFixed(2);
});
</script>