<?php
include 'connect.php'; // เชื่อมต่อฐานข้อมูล

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="tasks.csv"');

$output = fopen('php://output', 'w');

// เขียนหัวตาราง
fputcsv($output, ['ID', 'EmployeeID', 'TaskName', 'Start', 'End', 'Status']);

// ดึงข้อมูลจากตาราง tasktransactions
$sql_tasks = "SELECT ID, EmployeeID, TaskName, Start, End, Status FROM tasktransactions";
$result_tasks = $conn->query($sql_tasks);

// เขียนข้อมูลลงในไฟล์ CSV
while ($row = $result_tasks->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
$conn->close();
?>
