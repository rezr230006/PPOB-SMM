// path: ajax/api-payment-status.php
<?php
require_once 'library/database.php';
require_once 'library/PakasirService.php';

header('Content-Type: application/json');

$order_id = $_GET['order_id'] ?? '';
if (empty($order_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Order ID required']);
    exit;
}

$db = new Database();
$query = "SELECT status FROM orders WHERE order_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'status' => $order ? $order['status'] : 'not_found'
]);