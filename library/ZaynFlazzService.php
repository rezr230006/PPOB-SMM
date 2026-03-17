// path: library/ZaynFlazzService.php
<?php
require_once 'config.php';

class ZaynFlazzService {
    private $apiKey;
    private $apiUrl;
    
    public function __construct() {
        $this->apiKey = ZAYNFLAZZ_API_KEY;
        $this->apiUrl = ZAYNFLAZZ_API_URL;
    }
    
    public function getServices() {
        $endpoint = '/services';
        $response = $this->makeRequest($endpoint);
        return $response;
    }
    
    public function createOrder($serviceId, $target, $quantity) {
        $endpoint = '/orders';
        $data = [
            'service_id' => $serviceId,
            'target' => $target,
            'quantity' => $quantity
        ];
        
        $response = $this->makeRequest($endpoint, $data, 'POST');
        return $response;
    }
    
    public function checkOrderStatus($orderId) {
        $endpoint = '/orders/' . $orderId;
        $response = $this->makeRequest($endpoint);
        return $response;
    }
    
    public function syncServices() {
        $services = $this->getServices();
        $db = new Database();
        
        foreach ($services['data'] as $service) {
            $db->query("
                INSERT INTO services (service_code, service_name, category, price, provider, available)
                VALUES (:code, :name, :category, :price, 'zaynflazz', 1)
                ON DUPLICATE KEY UPDATE 
                    service_name = :name,
                    price = :price,
                    available = 1
            ");
            
            $db->bind(':code', $service['code']);
            $db->bind(':name', $service['name']);
            $db->bind(':category', $service['category']);
            $db->bind(':price', $service['price']);
            $db->execute();
        }
        
        return $services;
    }
    
    private function makeRequest($endpoint, $data = [], $method = 'GET') {
        $url = $this->apiUrl . $endpoint;
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