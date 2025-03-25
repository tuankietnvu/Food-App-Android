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
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    try {
        if (isset($data['Price'])) {
            $data['Price'] = floatval($data['Price']); 
        }
        
        $reference = $database->getReference('Foods/' . $foodId) ->update($data);
        // $snapshot = $reference->getSnapshot();
        
        // if ($snapshot->exists()) {
        //     $reference->update($data);
            echo json_encode([
                "status" => "success",
                "message" => "Food updated successfully"
            ]);
        // } else {
        //     echo json_encode([
        //         "status" => "error",
        //         "message" => "Food not found"
        //     ]);
        // }
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid data or ID"
    ]);
}
?>
