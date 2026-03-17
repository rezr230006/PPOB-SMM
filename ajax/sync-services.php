// path: ajax/sync-services.php
<?php
require_once '../library/ZaynFlazzService.php';
require_once '../library/database.php';

header('Content-Type: application/json');

try {
    $zaynflazz = new ZaynFlazzService();
    $result = $zaynflazz->syncServices();
    
    echo json_encode(['status' => 'success', 'message' => 'Services synced successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}