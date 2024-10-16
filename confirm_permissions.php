<?php
session_start();
include 'connect.php'; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // ถ้ายังไม่ได้ล็อกอิน ให้เปลี่ยนเส้นทางไปยังหน้า login
    exit();
}

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ดึง username ของผู้ใช้ที่ล็อกอิน
$username = $_SESSION['username'];
if (!isset($_GET['employeeid'])) {
    echo "ไม่พบ EmployeeID";
    exit();
}

$employeeid = $_GET['employeeid'];

// ดึงข้อมูลของ Employee ที่ต้องการยืนยันสิทธิ
$sql = "SELECT * FROM employeesall WHERE EmployeeID = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("เกิดข้อผิดพลาดในการเตรียมคำสั่ง: " . $conn->error);
}
$stmt->bind_param("s", $employeeid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employee = $result->fetch_assoc();
} else {
    echo "ไม่พบข้อมูลพนักงาน";
    exit();
}

// กำหนดตัวแปรสำหรับงาน
$tasknames = []; // ใช้สำหรับเก็บชื่อของงานทั้งหมด

// ตรวจสอบการยืนยันการอนุมัติ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // อัปเดตสถานะการอนุมัติในฐานข้อมูลของ tasktransactions
    $sql_update = "UPDATE tasktransactions SET Status = 'approved' WHERE EmployeeID = ?";

    $stmt_update = $conn->prepare($sql_update);
    if ($stmt_update === false) {
        die("เกิดข้อผิดพลาดในการเตรียมคำสั่งอัปเดต: " . $conn->error);
    }
    $stmt_update->bind_param("s", $employeeid);

    if ($stmt_update->execute()) {
        echo "ยืนยันการอนุมัติสิทธิสำเร็จ!<br>";
    } else {
        echo "เกิดข้อผิดพลาดในการยืนยันสิทธิ";
    }

    // ดึงข้อมูล TaskName จากตาราง tasktransactions
    $sql_tasks = "SELECT TaskName FROM tasktransactions WHERE EmployeeID = ?";
    $stmt_tasks = $conn->prepare($sql_tasks);
    if ($stmt_tasks === false) {
        die("เกิดข้อผิดพลาดในการเตรียมคำสั่งดึงข้อมูลงาน: " . $conn->error);
    }
    $stmt_tasks->bind_param("s", $employeeid);
    $stmt_tasks->execute();
    $result_tasks = $stmt_tasks->get_result();

    // ตรวจสอบว่าพบ TaskName หรือไม่
    while ($task = $result_tasks->fetch_assoc()) {
        $tasknames[] = $task['TaskName']; // เพิ่ม TaskName เข้าไปในอาเรย์
    }

    // แสดงชื่อของงานทั้งหมด
    echo "ชื่อของงานที่ต้องอนุมัติ: " . htmlspecialchars(implode(", ", $tasknames));
    
    // เพิ่มการ redirect ไปยังหน้า indexadmin.php หลังจากยืนยัน
    header("Location: indexadmin.php");
    exit();
}

// ดึงข้อมูล TaskName ที่เชื่อมโยงกับ EmployeeID
$sql_tasks = "SELECT TaskName FROM tasktransactions WHERE EmployeeID = ?";
$stmt_tasks = $conn->prepare($sql_tasks);
if ($stmt_tasks === false) {
    die("เกิดข้อผิดพลาดในการเตรียมคำสั่งดึงข้อมูลงาน: " . $conn->error);
}
$stmt_tasks->bind_param("s", $employeeid);
$stmt_tasks->execute();
$result_tasks = $stmt_tasks->get_result();

// กำหนดค่าเริ่มต้นให้กับ $tasknames
$tasknames = []; // ใช้สำหรับเก็บชื่อของงานทั้งหมด

// ตรวจสอบว่าพบ TaskName หรือไม่
while ($task = $result_tasks->fetch_assoc()) {
    $tasknames[] = $task['TaskName']; // เพิ่ม TaskName เข้าไปในอาเรย์
}

$sql_director = "SELECT * FROM UserRole WHERE RoleName = 'ผู้ดูแลระบบ'"; // ปรับเปลี่ยนตามความเหมาะสม
$stmt_director = $conn->prepare($sql_director);
if ($stmt_director === false) {
    die("เกิดข้อผิดพลาดในการเตรียมคำสั่งดึงข้อมูลผู้อำนวยการ: " . $conn->error);
}
$stmt_director->execute();
$result_director = $stmt_director->get_result();

$directors = [];
while ($director = $result_director->fetch_assoc()) {
    $directors[] = $director['FirstName'] . " " . $director['LastName']; // เพิ่มชื่อผู้อำนวยการลงในอาเรย์
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>ยืนยันการอนุมัติสิทธิ์</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

</head> 


<body>
  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt="">
        <span class="d-none d-lg-block">ทบทวนสิทธิ</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <!-- <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div> End Search Bar -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
          </a>
        </li><!-- End Search Icon-->
    
        <li class="nav-item dropdown">

        <!--    <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="badge bg-primary badge-number">4</span>
          </a>

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications" style="max-height: 300px; overflow-y: auto;">
            <li class="dropdown-header">
             4 รายการ 
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-exclamation-circle text-warning"></i>
              <a href="components-cards.html">
              <div>
                
                <h4>แจ้งเตือนรายงาน</h4>
                <p>รายงานการขอเข้าใช้สิทธิ</p>
                <p>30 นาที. ที่ผ่านมา</p>
              
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

          <!--     <li class="notification-item">
              <i class="bi bi-exclamation-circle text-warning"></i>
              <div>
                <h4>การแจ้งเตือนระบบ</h4>
                <p>............</p>
                <p>1 hr. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-exclamation-circle text-warning"></i>
              <div>
                <h4>แจ้งเตือน......</h4>
                <p>............</p>
                <p>2 hrs. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-exclamation-circle text-warning"></i>
              <div>
                <h4>ระบบ.............</h4>
                <p>..............</p>
                <p>4 hrs. ago</p>
              </div>
            </li>
          </a>
            <li>
              <hr class="dropdown-divider">
            </li>

          </ul><!-- End Notification Dropdown Items 

        </li><!-- End Notification Nav 
</a> -->

<li class="nav-item dropdown pe-3">

<a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
    <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle">
    <!-- แสดงชื่อผู้ใช้ ถ้าไม่มีจะแสดง "ชื่อผู้ใช้" -->
    <span class="d-none d-md-block dropdown-toggle ps-2">
        <?php 
        if (!empty($username)) { 
            echo htmlspecialchars($username); 
        } else { 
            echo 'ชื่อผู้ใช้';  // ข้อความเริ่มต้น ถ้าไม่มีข้อมูล username
        } 
        ?>
    </span>
</a><!-- End Profile Image Icon -->

<ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
    <li class="dropdown-header">
        <!-- แสดงชื่อผู้ใช้ ถ้าไม่มีจะแสดง "ชื่อผู้ใช้" -->
        <h6>
            <?php 
            if (!empty($username)) { 
                echo htmlspecialchars($username); 
            } else { 
                echo 'ชื่อผู้ใช้';  // ข้อความเริ่มต้น ถ้าไม่มีข้อมูล username
            } 
            ?>
        </h6>
        <!-- แสดงตำแหน่ง ถ้าไม่มีจะแสดง "ตำแหน่ง" -->
        <span>
            <?php 
            if (!empty($position)) { 
                echo htmlspecialchars($position); 
            } else { 
                echo 'ตำแหน่ง';  // ข้อความเริ่มต้น ถ้าไม่มีข้อมูล position
            } 
            ?>
        </span>
    </li>
    <li>
        <hr class="dropdown-divider">
    </li>
    <li>
        <a class="dropdown-item d-flex align-items-center" href="users-profile2.php">
            <i class="bi bi-person"></i>
            <span>โปรไฟล์</span>
        </a>
    </li>

    <li>
        <hr class="dropdown-divider">
    </li>

    <li>
        <a class="dropdown-item d-flex align-items-center" href="login.php">
            <i class="bi bi-box-arrow-right"></i>
            <span>ออกจากระบบ</span>
        </a>
    </li>

</ul><!-- End Profile Dropdown Items -->
</li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header>

<body>

  <!-- ======= Header ======= -->
  

  <aside id="sidebar" class="sidebar">

<ul class="sidebar-nav" id="sidebar-nav">

  <li class="nav-item">
    <a class="nav-link collapsed" href="indexadmin.php"class="active">
      <i class="bi bi-grid"></i>
      <span>หน้าหลัก</span>
    </a>
  </li><!-- End Dashboard Nav -->

  <li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
      <i class="bi bi-menu-button-wide"></i><span>ประวัติ</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
      
    
      <li>
        <a href="history2.php">
          <i class="bi bi-circle"></i><span>ประวัติการเข้าใช้</span>
        </a>
      </li>
   
    </a>
  </li>
  </ul>
  
  <li class="nav-item">
    <a class="nav-link " href="ประวัติการเปลี่ยนแปลง2.php">
      <i class="bi bi-envelope"></i>
      <span>ข้อความ </span>
    </a>
  </li><!-- End Contact Page Nav -->
  
    </a>
  </li>
</ul>

</aside>
<head>
    <meta charset="UTF-8">
    <title>ยืนยันการอนุมัติสิทธิ</title>
</head>
<body>
<main id="main" class="main">

    <div class="container">
        <div class="card shadow-lg my-5">
            <div class="card-header bg-primary text-white">
                <h1 class="text-center">ยืนยันการอนุมัติสิทธิ</h1>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>พนักงาน:</strong> <?php echo htmlspecialchars($employee['FirstName'] . " " . $employee['LastName']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>ตำแหน่ง:</strong> <?php echo htmlspecialchars($employee['Position']); ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ฝ่าย:</strong> <?php echo htmlspecialchars($employee['Department']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>งานที่ต้องอนุมัติ:</strong> <?php echo htmlspecialchars(implode(", ", $tasknames)); ?></p>
                    </div>
                    <div class="row">
    <div class="col-md-6">
        <p><strong>ผู้อนุมัติ:</strong> <?php echo htmlspecialchars(implode(", ", $directors)); ?></p>
    </div>
</div>
                </div>
                <div class="text-center">
                    <form method="POST" action="">
                        <input type="submit" class="btn btn-success btn-lg mt-3" value="ยืนยันการอนุมัติสิทธิ">
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>\
 <!-- ======= Footer ======= -->
 <footer id="footer" class="footer">
      <div class="copyright">&copy; สำนักงานหลักประกันสุขภาพแห่งชาติ <strong><span>ทบทวนสิทธิ</span></strong></div>
    </footer><!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
  </main>
</body>

</html>