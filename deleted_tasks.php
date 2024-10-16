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

        // SQL query สำหรับดึงข้อมูลงานที่จะลบ
        $sqlSelect = "SELECT ID, TaskName, Status FROM tasktransactions WHERE TaskName = ? AND EmployeeID = ?"; 

        // เตรียม statement
        $stmtSelect = $conn->prepare($sqlSelect);
        $stmtSelect->bind_param("ss", $taskName, $employeeId); // เปลี่ยนเป็น "ss" เนื่องจาก EmployeeID เป็น VARCHAR
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();

        // เช็คว่ามีข้อมูลที่จะลบหรือไม่
        if ($result->num_rows > 0) {
            // ลบข้อมูลจากตาราง tasktransactions เฉพาะ taskName และ EmployeeID ที่เลือก
            $sqlDelete = "DELETE FROM tasktransactions WHERE TaskName = ? AND EmployeeID = ?";
            $stmtDelete = $conn->prepare($sqlDelete);
            $stmtDelete->bind_param("ss", $taskName, $employeeId); // เปลี่ยนเป็น "ss"
            $stmtDelete->execute();

            /// ตรวจสอบผลลัพธ์ของการลบ
if ($stmtDelete->affected_rows > 0) {
    // บันทึกข้อมูลที่ถูกลบในตาราง deleted_tasks
    $sqlInsertDeleted = "INSERT INTO deleted_tasks (TaskName, Status, EmployeeID, DeletedAt) VALUES (?, 'N', ?, NOW())";
    $stmtInsertDeleted = $conn->prepare($sqlInsertDeleted);
    $stmtInsertDeleted->bind_param("ss", $taskName, $employeeId);
    $stmtInsertDeleted->execute();

    // ปิด statement ของการลบ
    $stmtDelete->close();
    $stmtInsertDeleted->close();

    // รีเฟรชหน้าไปที่ 55021.php ตาม EmployeeID
    header('Location: 55021.php?employeeID=' . urlencode($employeeId)); // เปลี่ยน URL เป็น 55021.php
    exit();
} else {
    $error = "ไม่สามารถลบข้อมูลได้";
}

        } else {
            $error = "ไม่มีข้อมูลที่จะลบสำหรับ EmployeeID นี้";
        }

        // ปิด statement ของการดึงข้อมูล
        $stmtSelect->close();
    } else {
        $error = "ไม่มีข้อมูลที่ต้องการลบ";
    }
}

$conn->close(); // ปิดการเชื่อมต่อฐานข้อมูล
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลบข้อมูล</title>
    <link rel="stylesheet" href="path/to/your/bootstrap.css"> <!-- ลิงก์ไปยัง Bootstrap CSS -->
</head>
<body>
    <div class="container mt-5">
        <h1>ลบข้อมูลงาน</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <!-- ปุ่มกลับไปหน้าแรก -->
        <div class="mt-3">
            <a href="index.php" class="btn btn-primary">กลับไปหน้าแรก</a>
        </div>
    </div>
</body>
</html>
