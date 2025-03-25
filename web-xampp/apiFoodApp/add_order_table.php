
<?php
use Dotenv\Dotenv;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$serviceAccount = $_ENV['FIREBASE_SERVICE_ACCOUNT'];
$databaseUri = $_ENV['FIREBASE_DATABASE_URI'];
// Firebase Database URL
$firebaseURL = $databaseUri . "/orders.json";

// Lấy danh sách đơn hàng hiện tại
$ordersData = file_get_contents($firebaseURL);
$orders = json_decode($ordersData, true);

// Nếu chưa có node `orders`, khởi tạo mảng rỗng
if ($orders === null) {
    $orders = [];  // Tạo node `orders`
}

// Xác định ID mới
if (empty($orders)) {
    $newOrderId = 0; // Nếu chưa có đơn hàng nào, bắt đầu từ 0
} else {
    $newOrderId = max(array_keys($orders)) + 1; // Lấy ID lớn nhất +1
}

// Nhận dữ liệu JSON từ Postman
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Dữ liệu không hợp lệ"]);
    exit;
}

// Thêm ID vào đơn hàng
$data["orderId"] = $newOrderId;

// URL để thêm đơn hàng mới
$orderUrl = "https://project275-foodapp-default-rtdb.firebaseio.com/orders/$newOrderId.json";

// Ghi log kiểm tra
file_put_contents("debug_log.txt", json_encode($data));

// Gửi dữ liệu lên Firebase
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $orderUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // Dùng PUT để ghi vào ID cụ thể
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

$response = curl_exec($ch);
curl_close($ch);

// Kiểm tra phản hồi Firebase
    echo json_encode(["status" => "success", "orderId" => $newOrderId, "message" => "thanh cong"]);
?>
