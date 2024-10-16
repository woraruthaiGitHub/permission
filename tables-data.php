<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>รายชื่อ </title>
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
session_start();

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
    </div><!-- End Logo -->


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
        <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
            <i class="bi bi-person"></i>
            <span>โปรไฟล์</span>
        </a>
    </li>

    <li>
        <hr class="dropdown-divider">
    </li>

    <li>
        <a class="dropdown-item d-flex align-items-center" href="pages-login.html">
            <i class="bi bi-box-arrow-right"></i>
            <span>ออกจากระบบ</span>
        </a>
    </li>

</ul><!-- End Profile Dropdown Items -->
</li><!-- End Profile Nav -->


      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link " href="index.php">
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
            <a href="ประวัติการใช้งาน.php">
              <i class="bi bi-circle"></i><span>ประวัติการเข้าใช้</span>
            </a>
          </li>
          <li>
            <a href="ประวัติการเปลี่ยนแปลง.php">
              <i class="bi bi-circle"></i><span>ประวัติการเปลี่ยนแปลง</span>
            </a>
          </li>
          
        </ul>
      </li><!-- End Components Nav -->
      

      <li class="nav-item">
        <a class="nav-link " data-bs-target="#tables-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-layout-text-window-reverse"></i><span>รายชื่อ</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="tables-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
        
          <li>
            <a href="tables-data.html" class="active">
              <i class="bi bi-circle"></i><span>รายชื่อพนักงานทั้งหมด</span>
            </a>
          </li>
        </ul>
      </li><!-- End Tables Nav -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-error-404.html">
          <i class="bi bi-dash-circle"></i>
          <span>Error 404</span>
        </a>
      </li><!-- End Error 404 Page Nav -->

      
        </a>
      </li><!-- End Blank Page Nav -->

    </ul>

  </aside><!-- End Sidebar-->
  <main id="main" class="main">

    <div class="pagetitle">
      <h1>รายชื่อพนักงานทั้งหมด</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
          <li class="breadcrumb-item">รายชื่อ</li>
          <li class="breadcrumb-item active">รายชื่อพนักงานทั้งหมด</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
        <div class="container">
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label">ฝ่าย</label>
            <div class="col-sm-10">
                <select class="form-select" aria-label="Default select example" onchange="navigateToPage(this)">
                    <option selected>--สำนักงาน--</option>
                    <?php
                    session_start();
                    if (isset($_SESSION['departments']) && !empty($_SESSION['departments'])) {
                        foreach ($_SESSION['departments'] as $department) {
                            echo '<option value="' . htmlspecialchars($department->id) . '">' . htmlspecialchars($department->name) . '</option>';
                        }
                    } else {
                        echo '<option value="">No departments available</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>

    <script>

                      function navigateToPage(selectElement) {
                          var selectedValue = selectElement.value;
                          if (selectedValue !== "0001 - สำนักตรวจสอบ") {
                              window.location.href = selectedValue;
                          }
                      }
                      </script>

                  </select>
                </div>
              </div>
              <!-- Table with stripped rows -->
     
            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; สำนักงานหลักประกันสุขภาพแห่งชาติ <strong><span>ทบทวนสิทธิ</span>
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