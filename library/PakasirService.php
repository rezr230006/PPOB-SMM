// path: library/PakasirService.php
<?php
class PakasirService {
    private $api_url = 'https://app.pakasir.com';
    private $api_key;
    private $project_slug;

    public function __construct() {
        $this->api_key = getenv('PAKASIR_API_KEY');
        $this->project_slug = getenv('PAKASIR_PROJECT_SLUG');
    }

    public function createTransaction($order_id, $amount, $method = 'qris') {
        $endpoint = $this->api_url . '/api/transactioncreate/' . $method;
        
        $data = [
            'project' => $this->project_slug,
            'order_id' => $order_id,
            'amount' => $amount,
            'api_key' => $this->api_key
        ];

        $response = $this->sendRequest($endpoint, 'POST', $data);
        
        return [
            'success' => $response['status'] === 'success',
            'payment_number' => $response['payment_number'] ?? null,
            'total_payment' => $response['total_payment'] ?? $amount,
            'expired_at' => $response['expired_at'] ?? null,
            'redirect_url' => $response['redirect_url'] ?? null
        ];
    }

    public function getTransactionStatus($order_id, $amount) {
        $endpoint = $this->api_url . '/api/transactiondetail';
        
        $data = [
            'project' => $this->project_slug,
            'order_id' => $order_id,
            'amount' => $amount,
            'api_key' => $this->api_key
        ];

        return $this->sendRequest($endpoint, 'GET', $data);
    }

    public function generateRedirectUrl($order_id, $amount, $qris_only = false) {
        $url = $this->api_url . '/pay/' . $this->project_slug . '/' . $amount . '?order_id=' . $order_id;
        
        if ($qris_only) {
            $url .= '&qris_only=1';
        }
        
        return $url;
    }

    private function sendRequest($url, $method, $data = null) {
        $ch = curl_init();
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT => 30
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
            $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("API request failed with HTTP code $httpCode");
        }

        return json_decode($response, true);
    }
}