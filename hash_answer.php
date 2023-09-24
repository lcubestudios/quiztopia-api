<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: POST');
require_once 'vendor/autoload.php';

use Hoa\Keccak\Keccak;

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
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get the form data
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        $answers = $data['answers'];

        // Generate a random salt
        $salt = bin2hex(random_bytes(32));

        // Concatenate the answers and the salt into a single string
        $concatenatedAnswersSalt = implode("", $answers) . $salt;

        // Hash the concatenated string using Keccak-256
        $correct_hash_answers = hash('sha3-256', $concatenatedAnswersSalt);

        // Insert the hash and salt into the database
        $stmt = $pdo->prepare("INSERT INTO quiztopia_quiz (correct_hash_answers, salt_used) VALUES (:correct_hash_answers, :salt_used)");
        $stmt->bindParam(':correct_hash_answers', $correct_hash_answers, PDO::PARAM_STR);
        $stmt->bindParam(':salt_used', $salt, PDO::PARAM_STR);
        $stmt->execute();

        // Return the hash and salt as JSON
        $response = [
            'quiz_hash' => $correct_hash_answers,
            'salt_used' => $salt
        ];
        echo json_encode($response);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
