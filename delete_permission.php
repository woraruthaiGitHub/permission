<?php
session_start();
include 'connect.php'; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // ถ้ายังไม่ได้ล็อกอิน ให้เปลี่ยนเส้นทางไปยังหน้า login
    exit();
}

// ตรวจสอบว่ามีการส่งข้อมูลมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['taskName']) && isset($_POST['employeeID'])) {
        $taskName = $_POST['taskName'];
        $employeeId = $_POST['employeeID'];

        // SQL query สำหรับดึงข้อมูลงานที่ถูกลบเฉพาะ EmployeeID
        $sqlSelect = "SELECT ID, TaskName, Status FROM deleted_tasks WHERE TaskName = ? AND EmployeeID = ?"; 

        // เตรียม statement
        $stmtSelect = $conn->prepare($sqlSelect);
        if ($stmtSelect === false) {
            die("Error preparing statement: " . $conn->error);
        }

        // Binding parameters
        $stmtSelect->bind_param("si", $taskName, $employeeId); // Assuming EmployeeID is an integer

        // Execute the select query
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();

        // เช็คว่ามีข้อมูลที่จะลบหรือไม่
        if ($result->num_rows > 0) {
            // ลบข้อมูลจากตาราง tasktransactions เฉพาะ taskName และ EmployeeID ที่เลือก
            $sqlDelete = "DELETE FROM tasktransactions WHERE TaskName = ? AND EmployeeID = ?";
            $stmtDelete = $conn->prepare($sqlDelete);
            if ($stmtDelete === false) {
                die("Error preparing delete statement: " . $conn->error);
            }
            $stmtDelete->bind_param("si", $taskName, $employeeId);
            $stmtDelete->execute();

            // เก็บข้อมูลที่ถูกลบลงใน session
            $deletedTasks = [];
            while ($row = $result->fetch_assoc()) {
                $deletedId = $row['ID'];
                $status = 'N'; // เปลี่ยนสถานะเป็น 'N'

                // เพิ่มข้อมูลที่ถูกลบใน array
                $deletedTasks[] = [
                    'ID' => $deletedId,
                    'TaskName' => $taskName,
                    'Status' => $status
                ];
            }

            // บันทึกข้อมูลที่ถูกลบใน session
            $_SESSION['deletedTasks'] = $deletedTasks;

            // ปิด statement ของการลบ
            $stmtDelete->close();

            // เปลี่ยนเส้นทางไปยังหน้าที่จะแสดงข้อมูลที่ถูกลบ
            header('Location: deleted_tasks.php');
            exit();
        } else {
            $error = "ไม่มีข้อมูลที่จะลบสำหรับ EmployeeID นี้";
        }

        // ปิด statement ของการดึงข้อมูล
        $stmtSelect->close();
    } else {
        $error = "ไม่มีข้อมูลที่ต้องการลบ";
    }
} else {
    $error = "ไม่สามารถเรียกใช้งานได้";
}

$conn->close(); // ปิดการเชื่อมต่อฐานข้อมูล
?>
