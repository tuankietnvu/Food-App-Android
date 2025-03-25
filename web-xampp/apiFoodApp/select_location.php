<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

use Dotenv\Dotenv;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$serviceAccount = $_ENV['FIREBASE_SERVICE_ACCOUNT'];
$databaseUri = $_ENV['FIREBASE_DATABASE_URI'];

$firebase_url =$databaseUri . "/Location.json"; // Thay thế bằng URL Firebase của bạn

// Lấy dữ liệu từ Firebase
$response = file_get_contents($firebase_url);
$data = json_decode($response, true);

// Trả về kết quả JSON
echo json_encode(["Location" => $data]);
?>