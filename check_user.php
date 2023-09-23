<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: POST');

require_once 'vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection parameters
$dbHost = $_ENV['DB_HOST'];
$dbPort = $_ENV['DB_PORT'];
$dbName = $_ENV['DB_NAME'];
$dbUser = $_ENV['DB_USER'];
$dbPassword = $_ENV['DB_PASSWORD'];

$method = $_SERVER['REQUEST_METHOD'];

// Check if the form has been submitted
if ($method == "POST") {
    try {
        // Create a PDO instance for the database connection
        $pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPassword);

        // Get the form data
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        // Load Privy id
        $privy_id = $data['privy_id'];

        // Query to check if the user exists
        $sql = 'SELECT COUNT(*) FROM quiztopia_users WHERE privy_id = :privy_id';
        $statement = $pdo->prepare($sql);
        $statement->bindParam(':privy_id', $privy_id); // Corrected binding parameter
        $statement->execute();

        $count = $statement->fetchColumn();

        if ($count > 0) {
            $output = array(
                'status_code' => 200,
                'message' => 'User Exist.'
            );
        } else {
            $output = array(
                'status_code' => 300,
                'message' => 'User does not exist'
            );
        }
        echo json_encode($output);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
