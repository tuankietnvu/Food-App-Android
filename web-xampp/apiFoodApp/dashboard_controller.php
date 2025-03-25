<?php
require 'vendor/autoload.php'; // Make sure to install firebase/php-jwt and kreait/firebase-php via Composer

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Database;
use Dotenv\Dotenv;

class DashboardController
{
    private $auth;
    private $database;

    public function __construct()
    {
        try {

            // Load environment variables from .env file
            $dotenv = Dotenv::createImmutable(__DIR__);
            $dotenv->load();


            $databaseUri = $_ENV['FIREBASE_DATABASE_URI'];
            // Path to your Firebase service account JSON file
            $serviceAccountPath = $_ENV['FIREBASE_SERVICE_ACCOUNT'];

            // Initialize Firebase
            $factory = (new Factory)
                ->withServiceAccount($serviceAccountPath)
                ->withDatabaseUri($databaseUri);

            $this->auth = $factory->createAuth();
            $this->database = $factory->createDatabase();
        } catch (Exception $e) {
            error_log('Firebase initialization error: ' . $e->getMessage());
        }
    }

    // 1. Count Users
    public function countUsers()
    {
        try {
            $users = $this->auth->listUsers($defaultMaxResults = 1000);
            return iterator_count($users);
        } catch (Exception $e) {
            error_log('Error counting users: ' . $e->getMessage());
            return 0;
        }
    }

    // 2. Count Orders
    public function countOrders()
    {
        try {
            $ordersRef = $this->database->getReference('orders');
            $orders = $ordersRef->getSnapshot();
            return $orders->numChildren();
        } catch (Exception $e) {
            error_log('Error counting orders: ' . $e->getMessage());
            return 0;
        }
    }

    // 3. Count Foods
    public function countFoods()
    {
        try {
            $foodsRef = $this->database->getReference('Foods');
            $foods = $foodsRef->getSnapshot();
            return $foods->numChildren();
        } catch (Exception $e) {
            error_log('Error counting foods: ' . $e->getMessage());
            return 0;
        }
    }

    // 4. Calculate Total Revenue
    public function calculateTotalRevenue()
    {
        try {
            $ordersRef = $this->database->getReference('orders');
            $orders = $ordersRef->getSnapshot();

            $totalRevenue = 0.0;
            foreach ($orders->getValue() as $order) {
                // Assumes each order has a 'total_price' field
                if (isset($order['total_price'])) {
                    $totalRevenue += floatval($order['total_price']);
                }
            }

            return $totalRevenue;
        } catch (Exception $e) {
            error_log('Error calculating revenue: ' . $e->getMessage());
            return 0.0;
        }
    }

    // Get All Dashboard Metrics
    public function getDashboardMetrics()
    {
        return [
            'userCount' => $this->countUsers(),
            'orderCount' => $this->countOrders(),
            'foodCount' => $this->countFoods(),
            'totalRevenue' => $this->calculateTotalRevenue()
        ];
    }
}


?>