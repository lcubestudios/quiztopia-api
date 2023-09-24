<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET');
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
if ($method == "GET") {
    try {
        // Create a PDO instance for the database connection
        $pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get the trivia_id from the URL
        $triviaId = $_GET['trivia_id'];

        // Query the database to retrieve the hash based on trivia_id
        $stmt = $pdo->prepare("SELECT correct_hash_answers FROM quiztopia_quiz WHERE trivia_id = :trivia_id");
        $stmt->bindParam(':trivia_id', $triviaId, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch the hash from the database
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $correctHashAnswers = $row['correct_hash_answers'];

            // Return the hash as JSON
            $response = [
                'answer_hash' => $correctHashAnswers
            ];
            echo json_encode($response);
        } else {
            // Handle the case where no matching record was found
            echo json_encode(['error' => 'No matching record found']);
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
