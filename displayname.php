<?php
session_start();
include 'connect.php'; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // ถ้ายังไม่ได้ล็อกอิน ให้เปลี่ยนเส้นทางไปยังหน้า login
    exit();
}

// รับข้อมูลผู้ใช้จากฐานข้อมูล
$username = $_SESSION['username'];
$sql = "SELECT * FROM UserRole WHERE Username='$username'";
$result = $conn->query($sql);
$userData = $result->fetch_assoc();

// ดึงข้อมูลแผนกของผู้ใช้
$department = $userData['Department'];

// ดึงข้อมูลพนักงานในแผนกนั้น รวมถึงตำแหน่ง (Position)
$employeesSql = "
    SELECT EmployeeID, FirstName, LastName, Position 
    FROM EmployeesAll 
    WHERE Department='$department'
    ORDER BY CASE 
        WHEN Position = 'ผู้อำนวยการฝ่าย' THEN 1
        ELSE 2
    END, Position ASC
";


$employeesResult = $conn->query($employeesSql);

// จัดเก็บข้อมูลพนักงานในอาเรย์
$employees = [];
while ($row = $employeesResult->fetch_assoc()) {
    $employees[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>รายชื่อพนักงานทั้งหมด</title>
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

  <link href="assets/css/style.css" rel="stylesheet">


</head>

<body>

  
<?php

// ตรวจสอบและดึงข้อมูลจาก session
$username = isset($_SESSION['username']) ? $_SESSION['username'] : ''; // ดึงชื่อผู้ใช้จาก session ถ้าไม่มีให้เป็นค่าว่าง
$position = isset($_SESSION['position']) ? $_SESSION['position'] : ''; // ดึงตำแหน่งจาก session ถ้าไม่มีให้เป็นค่าว่าง
?>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.html" class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt="">
        <span class="d-none d-lg-block">ทบทวนสิทธิ</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>

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
        <a class="dropdown-item d-flex align-items-center" href="users-profile.php">
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
  

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link " href="index.php"class="active">
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
            <a href="history.php">
              <i class="bi bi-circle"></i><span>ประวัติการเข้าใช้</span>
            </a>
          </li>
          <li>
            <a href="ประวัติการเปลี่ยนแปลง.php">
              <i class="bi bi-circle"></i><span>ประวัติการเปลี่ยนแปลง</span>
            </a>
          </li>
      
        </a>
      </li><!-- End Blank Page Nav -->
 
    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

  <div class="pagetitle">
    <h1 style="font-size: 28px;">รายชื่อพนักงานทั้งหมด</h1> <!-- กำหนดขนาดตัวอักษร -->
    <nav>
    <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php" style="font-size: 20px;">หน้าหลัก</a></li>
    <li class="breadcrumb-item active" style="font-size: 20px;">รายชื่อพนักงานในแผนก</li>
</ol>

    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <h1 style="font-size: 20px;">รายชื่อพนักงานในแผนก: <?php echo htmlspecialchars($department); ?></h1>

            <table class="table datatable" id="employeesTable" style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
                <thead>
                    <tr>
                        <th style="text-align: center; border: 1px solid #ddd;">ลำดับ</th>
                        <th style="text-align: center; border: 1px solid #ddd;">รหัสพนักงาน</th>
                        <th style="text-align: center; border: 1px solid #ddd;">ชื่อ</th>
                        <th style="text-align: center; border: 1px solid #ddd;">ตำแหน่ง</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                if (count($employees) > 0) {
                    foreach ($employees as $index => $employee) {
                        echo "<tr>";
                        
                        // จัดตำแหน่งให้ชิดขวา
                        echo "<td style='text-align: right; border: 1px solid #ddd;'>" . ($index + 1) . "</td>";
                        
                        // เพิ่มลิงก์ที่คลิกได้ใน EmployeeID ไปยัง 55021.php
                        echo "<td style='text-align: center; border: 1px solid #ddd;'><a href='55021.php?employeeID=" . urlencode($employee['EmployeeID']) . "'>" 
                        . htmlspecialchars($employee['EmployeeID']) . "</a></td>";
                        
                        // รวมชื่อและนามสกุลเข้าด้วยกัน
                        echo "<td style='text-align: left; border: 1px solid #ddd;'>" . htmlspecialchars($employee['FirstName'] . " " . $employee['LastName']) . "</td>";
                        
                        echo "<td style='text-align: left; border: 1px solid #ddd;'>" . htmlspecialchars($employee['Position']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align: center; border: 1px solid #ddd;'>ไม่มีข้อมูลพนักงานในแผนกนี้</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</section>





  <!-- ======= Footer ======= -->
  
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; สำนักงานหลักประกันสุขภาพแห่งชาติ <strong><span>ทบทวนสิทธิ</span>
    </div>
    <div class="credits">
      <!-- All the links in the footer should remain intact. -->
      <!-- You can delete the links only if you purchased the pro version. -->
      <!-- Licensing information: https://bootstrapmade.com/license/ -->
      <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ -->
     
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>