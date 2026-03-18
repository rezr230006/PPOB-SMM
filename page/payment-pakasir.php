// path: page/payment-pakasir.php
<?php
require_once 'library/header.php';
require_once 'library/database.php';
require_once 'library/PakasirService.php';

// Get order details
$order_id = $_GET['order_id'] ?? '';
if (empty($order_id)) {
    die('Order ID is required');
}

$db = new Database();
$query = "SELECT * FROM orders WHERE order_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die('Order not found');
}

// Check if order is already paid
if ($order['status'] === 'PAID') {
    header('Location: /page/receipt.php?order_id=' . $order_id);
    exit;
}

// Load payment data
$payment_data = json_decode($order['payment_data'] ?? '{}', true);
$payment_method = $order['payment_method'] ?? 'qris';
$expires_at = $order['expires_at'] ?? '';
?>

<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <h3>Payment Details</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h4>Order Information</h4>
                    <p><strong>Order ID:</strong> <?= $order['order_id'] ?></p>
                    <p><strong>Amount:</strong> Rp. <?= number_format($order['amount'], 0, ',', '.') ?></p>
                    <p><strong>Payment Method:</strong> <?= ucfirst($payment_method) ?></p>
                    <p><strong>Expires At:</strong> <?= date('Y-m-d H:i:s', strtotime($expires_at)) ?></p>
                </div>
                
                <div class="col-md-6">
                    <?php if ($payment_method === 'qris'): ?>
                        <h4>QR Payment</h4>
                        <div id="qrcode" style="width: 200px; height: 200px;"></div>
                    <?php else: ?>
                        <h4>Virtual Account</h4>
                        <div class="alert alert-info">
                            <strong>VA Number:</strong> <?= $payment_data['payment_number'] ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-4">
                <div class="progress">
                    <div id="countdown-progress" class="progress-bar" role="progressbar" 
                         style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                        00:00
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <button id="btn-check-status" class="btn btn-primary">
                    <i class="fas fa-sync-alt"></i> Check Status
                </button>
                <a href="/" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// Generate QR Code
<?php if ($payment_method === 'qris'): ?>
    new QRCode(document.getElementById("qrcode"), {
        text: "<?= $payment_data['payment_number'] ?>",
        width: 200,
        height: 200
    });
<?php endif; ?>

// Countdown Timer
const endTime = new Date("<?= $expires_at ?>").getTime();
const progressBar = document.getElementById('countdown-progress');

function updateCountdown() {
    const now = new Date().getTime();
    const distance = endTime - now;
    
    if (distance < 0) {
        clearInterval(timerInterval);
        alert('Payment has expired!');
        location.reload();
        return;
    }
    
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    document.getElementById('countdown').innerHTML = 
        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    
    // Update progress bar
    const totalSeconds = <?= strtotime($expires_at) - strtotime($order['created_at']) ?>;
    const elapsedSeconds = totalSeconds - (distance / 1000);
    const percentage = (elapsedSeconds / totalSeconds) * 100;
    progressBar.style.width = percentage + '%';
}

// Auto polling every 5 seconds
function checkPaymentStatus() {
    fetch('/api/payment-status.php?order_id=<?= $order_id ?>')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'PAID') {
                alert('Payment Successful!');
                location.href = '/page/receipt.php?order_id=<?= $order_id ?>';
            }
        })
        .catch(error => console.error('Status check failed:', error));
}

// Start timers
updateCountdown();
const timerInterval = setInterval(updateCountdown, 1000);
setInterval(checkPaymentStatus, 5000);
</script>

<?php require_once 'library/footer.php'; ?>