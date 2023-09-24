<?php
// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection parameters
$dbHost = $_ENV['DB_HOST'];
$dbPort = $_ENV['DB_PORT'];
$dbName = $_ENV['DB_NAME'];
$dbUser = $_ENV['DB_USER'];
$dbPassword = $_ENV['DB_PASSWORD'];

try {
    // Create a PDO instance for the database connection
    $pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

     // Get the form data
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    $inputToVerify = $data['input'];

    // Retrieve the salt from the database based on the provided hash
    $stmt = $pdo->prepare("SELECT salt_used FROM quiztopia_quiz WHERE correct_hash_answers = :provided_hash");
    $stmt->bindParam(':provided_hash', $providedHash, PDO::PARAM_STR);
    $stmt->execute();

    // Fetch the salt from the database
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $saltFromDatabase = $row['salt_used'];

    // Concatenate the input and retrieved salt
    $concatenatedInput = $inputToVerify . $saltFromDatabase;

    // Hash the concatenated input using Keccak-256
    $hashedInput = hash('sha3-256', $concatenatedInput);

    // Compare the provided hash with the computed hash
    $verificationResult = hash_equals($providedHash, $hashedInput);

    if ($verificationResult) {
        echo "Hash verification successful. The provided hash matches the computed hash.";
    } else {
        echo "Hash verification failed. The provided hash does not match the computed hash.";
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
