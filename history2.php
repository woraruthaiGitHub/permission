<?php
session_start();
include 'connect.php'; // เชื่อมต่อฐานข้อมูล

if (!isset($_SESSION['username'])) {
    header('Location: login.php'); 
    exit();
}

// ตรวจสอบว่ามีการลบข้อมูลหรือไม่
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // ลบข้อมูลจาก loginhistory1 ตาม ID
    $delete_sql = "DELETE FROM loginhistory1 WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $delete_id);
    $delete_stmt->execute();

    // เปลี่ยนเส้นทางไปยังหน้า history เพื่อรีเฟรชข้อมูล
    header('Location: history.php');
    exit();
}

// ดึงประวัติการเข้าสู่ระบบจาก loginhistory1
$log_sql = "SELECT * FROM loginhistory1 WHERE username = ? ORDER BY login_time DESC";
$log_stmt = $conn->prepare($log_sql);
$log_stmt->bind_param("s", $_SESSION['username']);
$log_stmt->execute();
$log_result = $log_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>ประวัติการเข้าสู่ระบบ</title>
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

  <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
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
      <a href="indexadmin.php" class="logo d-flex align-items-center">
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
  

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

<ul class="sidebar-nav" id="sidebar-nav">

  <li class="nav-item">
    <a class="nav-link collapsed" href="indexadmin.php">
      <i class="bi bi-grid"></i>
      <span>หน้าหลัก</span>
    </a>
  </li><!-- End Dashboard Nav -->

  <li class="nav-item">
    <a class="nav-link " data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
      <i class="bi bi-menu-button-wide"></i><span>ประวัติ</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="components-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
      
      <li>
        <a href="history2.php"class="active">
          <i class="bi bi-circle"></i><span>ประวัติการเข้าใช้</span>
        </a>
      </li>
      </a>
      </li>
      </ul>
      
      <li class="nav-item">
        <a class="nav-link collapsed"  href="ประวัติการเปลี่ยนแปลง2.php">
          <i class="bi bi-envelope"></i>
          <span>ข้อความ</span>
        </a>
      </li><!-- End Contact Page Nav -->
      
        </a>
      </li>
    </ul>


</aside><!-- End Sidebar-->


<head>
    <meta charset="utf-8">
    <title>ประวัติการเข้าสู่ระบบ</title>
    <!-- เพิ่ม CSS ที่ต้องการ -->
</head>

<main id="main" class="main">

    <div class="pagetitle">
        <h1 style="font-size: 28px;">ประวัติการเข้าสู่ระบบ</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" style="font-size: 20px;">หน้าหลัก</a></li>
                <li class="breadcrumb-item active" style="font-size: 20px;">ประวัติการเข้าใช้</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <table class="table table-bordered datatable" style="width: 100%; text-align: center; font-size: 18px;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="text-align: center; font-weight: bold; width: 5%;">ลำดับ</th>
                            <th style="text-align: center; font-weight: bold; width: 15%;">รหัสพนักงาน</th>
                            <th style="text-align: center; font-weight: bold; width: 15%;">สถานะ</th>
                            <th style="text-align: center; font-weight: bold; width: 20%;">วันที่</th>
                            <th style="text-align: center; font-weight: bold; width: 15%;">เวลา</th>
                            <th style="text-align: center; font-weight: bold; width: 10%;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $count = 1;
                            $fmt = new IntlDateFormatter('th_TH', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Asia/Bangkok', IntlDateFormatter::GREGORIAN, 'd MMMM yyyy');

                            while ($row = $log_result->fetch_assoc()) {
                                $date_time = new DateTime($row['login_time']);
                                $date = $fmt->format($date_time);
                                $time = $date_time->format('H:i:s');

                                echo "<tr>";
                                echo "<td style='text-align: right;'>" . $count++ . "</td>";
                                echo "<td style='text-align: center; color: blue;'>" . htmlspecialchars($row['username']) . "</td>";
                                echo "<td style='text-align: center; color: green;'>" . htmlspecialchars($row['status']) . "</td>";
                                echo "<td style='text-align: center;'>" . htmlspecialchars($date) . "</td>";
                                echo "<td style='text-align: center;'>" . htmlspecialchars($time) . "</td>";
                                echo "<td style='text-align: center;'>
                                        <a href='history.php?delete_id=" . htmlspecialchars($row['id']) . "' 
                                        onclick=\"return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนี้?');\" 
                                        style='color: red; text-decoration: none;'>ลบ</a>
                                      </td>";
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

</main>



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


  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>


  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

  </footer>
  </body>

</html>