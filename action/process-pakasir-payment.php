// path: action/process-pakasir-payment.php
<?php
require_once 'library/database.php';
require_once 'library/PakasirService.php';
require_once 'library/function.php';

// Get order parameters
$order_id = $_POST['order_id'] ?? '';
$amount = $_POST['amount'] ?? '';
$payment_method = $_POST['payment_method'] ?? 'qris';

if (empty($order_id) || empty($amount)) {
    die('Invalid parameters');
}

// Initialize service
$pakasir = new PakasirService();

try {
    $transaction = $pakasir->createTransaction($order_id, $amount, $payment_method);
    
    if ($transaction['success']) {
        // Store transaction details
        $db = new Database();
        $update_query = "UPDATE orders SET 
                          payment_reference = ?, 
                          payment_method = ?, 
                          payment_data = ?,
                          expires_at = ?,
                          status = 'PENDING'
                          WHERE order_id = ?";
        
        $payment_data = json_encode($transaction);
        $stmt = $db->prepare($update_query);
        $stmt->execute([
            $transaction['payment_number'],
            $payment_method,
            $payment_data,
            date('Y-m-d H:i:s', strtotime($transaction['expired_at'])),
            $order_id
        ]);
        
        // Redirect to payment page
        header('Location: /page/payment-pakasir.php?order_id=' . $order_id);
        exit;
    } else {
        die('Failed to create transaction: ' . ($transaction['message'] ?? 'Unknown error'));
    }
    
} catch (Exception $e) {
    die('Payment processing error: ' . $e->getMessage());
}