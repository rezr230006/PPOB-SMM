// path: library/PakasirService.php
<?php
require_once 'config.php';

class PakasirService {
    private $apiKey;
    private $secretKey;
    private $merchantId;
    private $baseUrl = 'https://api.pakasir.com/v1';
    
    public function __construct() {
        $this->apiKey = PAKASIR_API_KEY;
        $this->secretKey = PAKASIR_SECRET_KEY;
        $this->merchantId = PAKASIR_MERCHANT_ID;
    }
    
    public function createPayment($userId, $amount, $method = 'VA') {
        $data = [
            'merchant_id' => $this->merchantId,
            'amount' => $amount,
            'customer_name' => 'Customer-' . $userId,
            'customer_email' => 'customer@example.com',
            'customer_phone' => '08123456789',
            'method' => $method,
            'callback_url' => PAKASIR_WEBHOOK_URL,
            'expired_time' => 3600
        ];
        
        $response = $this->makeRequest('/payments', $data, 'POST');
        return $response;
    }
    
    public function checkPaymentStatus($paymentId) {
        $response = $this->makeRequest('/payments/' . $paymentId, [], 'GET');
        return $response;
    }
    
    public function verifyWebhook($signature, $payload) {
        $expectedSignature = hash_hmac('sha256', $payload, $this->secretKey);
        return hash_equals($expectedSignature, $signature);
    }
    
    private function makeRequest($endpoint, $data = [], $method = 'GET') {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();
        
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ];
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}