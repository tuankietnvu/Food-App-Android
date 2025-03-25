<?php

use Dotenv\Dotenv;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$serviceAccount = $_ENV['FIREBASE_SERVICE_ACCOUNT'];
$databaseUri = $_ENV['FIREBASE_DATABASE_URI'];

// Lấy orderId từ URL hoặc query string
if (!isset($_POST['orderId'])) {
    echo json_encode(["status" => "error", "message" => "Thiếu orderId"]);
    exit;
}

$orderId = $_POST['orderId'];
$firebaseURL = $databaseUri . "/orders/$orderId.json"; 

// Cấu hình cURL để gửi yêu cầu DELETE
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $firebaseURL);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Thực hiện request
$response = curl_exec($ch);
curl_close($ch);

// Trả về phản hồi
if ($response === "null") {
    echo json_encode(["status" => "success", "message" => "Xóa đơn hàng $orderId thành công"]);
} else {
    echo json_encode(["status" => "error", "message" => "Không thể xóa đơn hàng"]);
}
?>
