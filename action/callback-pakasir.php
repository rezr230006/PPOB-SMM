// path: action/callback-pakasir.php
<?php
require_once 'library/database.php';
require_once 'library/PakasirService.php';
require_once 'library/function.php';

header('Content-Type: application/json');
header('HTTP/1.1 200 OK');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Get raw POST data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data || !isset($data['order_id'])) {
    http_response_code(400);
    exit('Invalid request');
}

// Verify webhook signature (if implemented by Pakasir)
// if (!$this->verifySignature($data)) {
//     http_response_code(401);
//     exit('Unauthorized');
// }

// Initialize service
$pakasir = new PakasirService();
$db = new Database();

// Get order details
$order_id = $data['order_id'];
$amount = $data['amount'];
$status = $data['status'];
$payment_method = $data['payment_method'];

try {
    // Verify transaction status
    $verification = $pakasir->getTransactionStatus($order_id, $amount);
    
    if ($verification['status'] === 'completed') {
        // Update order status
        $update_query = "UPDATE orders SET status = 'PAID', 
                          payment_method = ?, 
                          paid_at = NOW(),
                          updated_at = NOW()
                          WHERE order_id = ? AND status != 'PAID'";
        
        $stmt = $db->prepare($update_query);
        $stmt->execute([$payment_method, $order_id]);
        
        // Log successful payment
        $log_query = "INSERT INTO payment_logs (order_id, amount, payment_method, status, response_data) 
                      VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($log_query);
        $stmt->execute([$order_id, $amount, $payment_method, 'SUCCESS', $json_data]);
        
        // Add additional business logic (e.g., send notification)
        $this->handleSuccessfulPayment($order_id);
    }
    
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    // Log error
    error_log("Pakasir webhook error: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Processing failed, but order will be verified manually'
    ]);
}

function handleSuccessfulPayment($order_id) {
    // Implement your business logic here
    // Example: send email, update user balance, etc.
}