// path: action/callback-pakasir.php
<?php
require_once '../library/PakasirService.php';
require_once '../library/database.php';

header('Content-Type: application/json');

try {
    $pakasir = new PakasirService();
    $payload = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_X_PAKASIR_SIGNATURE'] ?? '';
    
    if (!$pakasir->verifyWebhook($signature, $payload)) {
        throw new Exception('Invalid signature');
    }
    
    $data = json_decode($payload, true);
    $paymentId = $data['payment_id'];
    $status = $data['status'];
    $amount = $data['amount'];
    
    $db = new Database();
    
    // Update deposit status
    $db->query("UPDATE deposits SET status = :status WHERE payment_id = :payment_id");
    $db->bind(':status', $status);
    $db->bind(':payment_id', $paymentId);
    $db->execute();
    
    // Log payment activity
    $db->query("INSERT INTO payment_logs (payment_id, status, message) VALUES (:payment_id, :status, :message)");
    $db->bind(':payment_id', $paymentId);
    $db->bind(':status', $status);
    $db->bind(':message', json_encode($data));
    $db->execute();
    
    // If payment is successful, update user balance
    if ($status === 'paid') {
        $userId = $data['customer_id'];
        
        // Get user current balance
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
            
            // Record transaction
            $db->query("INSERT INTO transactions (user_id, type, amount, balance_after, reference_id) 
                        VALUES (:user_id, 'deposit', :amount, :balance, :reference)");
            $db->bind(':user_id', $userId);
            $db->bind(':amount', $amount);
            $db->bind(':balance', $newBalance);
            $db->bind(':reference', $paymentId);
            $db->execute();
        }
    }
    
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}