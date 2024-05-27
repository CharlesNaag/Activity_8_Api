<?php
header("Content-Type: application/json");

$host = 'localhost';
$db = 'auto_shops';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$pdo = new PDO($dsn, $user, $pass, $options);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT app_ID, app_date, app_time, status FROM appointment");
    $appointments = $stmt->fetchAll();
    echo json_encode($appointments);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $appointmentDate = strtotime($input['appointmentDate']);
    $app_date = date('Y-m-d', $appointmentDate);
    $app_time = date('H:i:s', $appointmentDate);
    $status = 'Pending'; // Default status for new appointments

    // Convert app_time to integer representation (e.g., total seconds or a format compatible with the constraint)
    $app_time_seconds = strtotime($app_time) - strtotime('TODAY');

    // Ensure the time representation is valid for the constraint
    if ($app_time_seconds < 0 || $app_time_seconds > 86400) {
        echo json_encode(['error' => 'Invalid time value']);
        exit;
    }
    $sql = "INSERT INTO appointment (app_date, app_time, status) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$app_date, $app_time_seconds, $status]);

    echo json_encode(['message' => 'Appointment added successfully']);
}
?>
