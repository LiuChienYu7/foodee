<?php
header('Content-Type: application/json');

$host = 'localhost';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'foodee';

$conn = new mysqli($host, $dbuser, $dbpassword, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);

$r_id = $data['r_id'];
$r_name = $data['r_name'];
$user = $data['user'];
$comment = $data['comment'];

$sql = "INSERT INTO comment (r_id, r_name, user, comment) VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param('isss', $r_id, $r_name, $user, $comment);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add comment: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
