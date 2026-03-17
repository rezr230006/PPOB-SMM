// path: action/callback-zaynflazz.php
<?php
require_once '../library/ZaynFlazzService.php';
require_once '../library/database.php';

header('Content-Type: application/json');

try {
    $payload = file_get_contents('php://input');
    $data = json_decode($payload, true);
    
    $orderId = $data['order_id'];
    $status = $data['status'];
    $providerOrderId = $data['provider_order_id'];
    
    $db = new Database();
    
    // Update provider order status
    $db->query("UPDATE provider_orders SET status = :status WHERE provider_order_id = :provider_order_id");
    $db->bind(':status', $status);
    $db->bind(':provider_order_id', $providerOrderId);
    $db->execute();
    
    // Get order ID from provider_orders
    $db->query("SELECT order_id FROM provider_orders WHERE provider_order_id = :provider_order_id");
    $db->bind(':provider_order_id', $providerOrderId);
    $result = $db->single();
    
    if ($result) {
        $orderId = $result['order_id'];
        
        // Update main order status
        $db->query("UPDATE orders SET status = :status WHERE id = :order_id");
        $db->bind(':status', $status);
        $db->bind(':order_id', $orderId);
        $db->execute();
        
        // Log order activity
        $db->query("INSERT INTO order_logs (order_id, status, message) VALUES (:order_id, :status, :message)");
        $db->bind(':order_id', $orderId);
        $db->bind(':status', $status);
        $db->bind(':message', json_encode($data));
        $db->execute();
        
        // Handle completed or partial orders
        if (in_array($status, ['completed', 'partial'])) {
            // Deduct balance if not already deducted
            // Additional logic for order completion
        }
        
        // Handle failed orders
        if ($status === 'failed') {
            // Refund balance to user
            $db->query("SELECT user_id, amount FROM orders WHERE id = :order_id");
            $db->bind(':order_id', $orderId);
            $order = $db->single();
            
            if ($order) {
                $userId = $order['user_id'];
                $amount = $order['amount'];
                
                // Get current balance
                $db->query("SELECT balance FROM users WHERE id = :user_id");
                $db->bind(':user_id', $userId);
                $user = $db->single();
                
                if ($user) {
                    $newBalance = $user['balance'] + $amount;
                    
                    // Update user balance
                    $db->query("UPDATE users SET balance = :balance WHERE id = :user_id");
                    $db->bind(':balance', $newBalance);
                    $db->bind(':user_id', $userId);
                    $db->execute();
                    
                    // Record refund transaction
                    $db->query("INSERT INTO transactions (user_id, type, amount, balance_after, reference_id) 
                                VALUES (:user_id, 'refund', :amount, :balance, :reference)");
                    $db->bind(':user_id', $userId);
                    $db->bind(':amount', $amount);
                    $db->bind(':balance', $newBalance);
                    $db->bind(':reference', 'refund-' . $orderId);
                    $db->execute();
                }
            }
        }
    }
    
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}