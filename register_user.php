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

      // Load form data
      $privy_id = $data['privy_id'];
      $age = $data['age'];
      $gender = $data['gender'];
      $region = $data['region'];
      $occupation = $data['occupation'];
      $web3_experience = $data['web3_experience'];
      $wallet_address = $data['wallet_address'];

      // SQL query to insert data into the table
      $sql = 'INSERT INTO quiztopia_users (privy_id, age, gender, region, occupation, web3_experience, wallet_address) 
              VALUES (:privy_id, :age, :gender, :region, :occupation, :web3_experience, :wallet_address)';
      
      // Prepare the SQL statement
      $statement = $pdo->prepare($sql);

      // Bind parameters
      $statement->bindParam(':privy_id', $privy_id);
      $statement->bindParam(':age', $age);
      $statement->bindParam(':gender', $gender);
      $statement->bindParam(':region', $region);
      $statement->bindParam(':occupation', $occupation);
      $statement->bindParam(':web3_experience', $web3_experience);
      $statement->bindParam(':wallet_address', $wallet_address);

      // Execute the SQL statement
      $result = $statement->execute();

      if ($result) {
          $output = array(
              'status_code' => 200,
              'message' => 'Data inserted successfully.'
          );
      } else {
          $output = array(
              'status_code' => 500,
              'message' => 'Error inserting data.'
          );
      }

      echo json_encode($output);
  } catch (PDOException $e) {
      die("Error: " . $e->getMessage());
  }
}




