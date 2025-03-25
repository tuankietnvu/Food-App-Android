<?php
require 'vendor/autoload.php';

use Kreait\Firebase\Factory;
use Dotenv\Dotenv;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$serviceAccount = $_ENV['FIREBASE_SERVICE_ACCOUNT'];
$databaseUri = $_ENV['FIREBASE_DATABASE_URI'];

$factory = (new Factory)
    ->withServiceAccount($serviceAccount)
    ->withDatabaseUri($databaseUri);

$database = $factory->createDatabase();

$foodId = $_GET['id'];

if ($foodId) {
    $database->getReference('Foods/' . $foodId)->remove();
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}
?>
