// path: library/OrderStatusWorker.php
<?php
require_once 'config.php';
require_once 'database.php';
require_once 'ZaynFlazzService.php';

class OrderStatusWorker {
    private $db;
    private $zaynflazz;
    
    public function __construct() {
        $this->db = new Database();
        $this->zaynflazz = new ZaynFlazzService();
    }
    
    public function processPendingOrders() {
        $processed = 0;
        
        // Get pending orders
        $this->db->query("SELECT * FROM orders WHERE status = 'pending' LIMIT 100");
        $orders = $this->db->result();
        
        foreach ($orders as $order) {
            try {
                // Check if order has provider order ID
                if (!empty($order['provider_order_id'])) {
                    // Check status with provider
                    $response = $this->zaynflazz->checkOrderStatus($order['provider_order_id']);
                    
                    if (isset($response['status'])) {
                        $status = $response['status'];
                        
                        // Update order status
                        $this->db->query("UPDATE orders SET status = :status WHERE id = :order_id");
                        $this->db->bind(':status', $status);
                        $this->db->bind(':order_id', $order['id']);
                        $this->db->execute();
                        
                        // Log order status change
                        $this->db->query("INSERT INTO order_logs (order_id, status, message) 
                                          VALUES (:order_id, :status, :message)");
                        $this->db->bind(':order_id', $order['id']);
                        $this->db->bind(':status', $status);
                        $this->db->bind(':message', json_encode($response));
                        $this->db->execute();
                        
                        // Handle completed or partial orders
                        if (in_array($status, ['completed', 'partial'])) {
                            // Deduct balance if not already deducted
                            // Additional logic for order completion
                        }
                        
                        // Handle failed orders
                        if ($status === 'failed') {
                            // Refund balance to user
                            $this->refundOrder($order);
                        }
                        
                        $processed++;
                    }
                } else {
                    // Create order with provider
                    $this->createProviderOrder($order);
                    $processed++;
                }
            } catch (Exception $e) {
                // Log error
                $this->db->query("INSERT INTO order_logs (order_id, status, message) 
                                  VALUES (:order_id, 'error', :message)");
                $this->db->bind(':order_id', $order['id']);
                $this->db->bind(':message', $e->getMessage());
                $this->db->execute();
            }
        }
        
        return $processed;
    }
    
    private function createProviderOrder($order) {
        $response = $this->zaynflazz->createOrder(
            $order['service_code'],
            $order['target'],
            $order['quantity']
        );
        
        if (isset($response['order_id'])) {
            $providerOrderId = $response['order_id'];
            
            // Update order with provider order ID
            $this->db->query("UPDATE orders SET provider_order_id = :provider_id WHERE id = :order_id");
            $this->db->bind(':provider_id', $providerOrderId);
            $this->db->bind(':order_id', $order['id']);
            $this->db->execute();
            
            // Create provider order record
            $this->db->query("INSERT INTO provider_orders (order_id, provider_name, provider_order_id, status) 
                              VALUES (:order_id, 'zaynflazz', :provider_id, 'pending')");
            $this->db->bind(':order_id', $order['id']);
            $this->db->bind(':provider_id', $providerOrderId);
            $this->db->execute();
        }
    }
    
    private function refundOrder($order) {
        // Get user current balance
        $this->db->query("SELECT balance FROM users WHERE id = :user_id");
        $this->db->bind(':user_id', $order['user_id']);
        $user = $this->db->single();
        
        if ($user) {
            $newBalance = $user['balance'] + $order['amount'];
            
            // Update user balance
            $this->db->query("UPDATE users SET balance = :balance WHERE id = :user_id");
            $this->db->bind(':balance', $newBalance);
            $this->bind(':user_id', $order['user_id']);
            $this->db->execute();
            
            // Record refund transaction
            $this->db->query("INSERT INTO transactions (user_id, type, amount, balance_after, reference_id) 
                              VALUES (:user_id, 'refund', :amount, :balance, :reference)");
            $this->db->bind(':user_id', $order['user_id']);
            $this->db->bind(':amount', $order['amount']);
            $this->db->bind(':balance', $newBalance);
            $this->db->bind(':reference', 'refund-' . $order['id']);
            $this->db->execute();
        }
    }
}