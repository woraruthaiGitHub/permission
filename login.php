<?php
session_start();
include 'connect.php'; // เชื่อมต่อฐานข้อมูล

// ตั้งค่า Time Zone ให้ถูกต้อง
date_default_timezone_set('Asia/Bangkok'); // ตั้งค่า Time Zone


// ตัวอย่างการใช้งาน
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์ม
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // ตรวจสอบข้อมูลผู้ใช้ (ใช้ Prepared Statements เพื่อป้องกัน SQL Injection)
    $stmt = $conn->prepare("SELECT * FROM UserRole WHERE Username=? AND Password=?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // เก็บข้อมูลผู้ใช้ในเซสชัน
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $row['Username'];

        // ดึง IP Address ของผู้ใช้
        $ip_address = $_SERVER['REMOTE_ADDR'];

        // บันทึกการเข้าสู่ระบบในตาราง LoginHistory1
        $status = 'success';
        $login_time = date('Y-m-d H:i:s'); // ดึงเวลาปัจจุบัน

        $log_sql = "INSERT INTO LoginHistory1 (username, status, ip_address, login_time) VALUES (?, ?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);

        if ($log_stmt === false) {
            die("การเตรียมคำสั่ง SQL ผิดพลาด: " . $conn->error);
        }

        $log_stmt->bind_param("ssss", $username, $status, $ip_address, $login_time);

        if ($log_stmt->execute() === false) {
            die("การรันคำสั่ง SQL ผิดพลาด: " . $log_stmt->error);
        }

        if ($username === '12344' && $password === 'password123') {
          $_SESSION['username'] = $row['Username'];
          $_SESSION['rolename'] = $row['RoleName']; // เก็บ RoleName ในเซสชัน
          // ตรวจสอบว่าเป็น "ผู้ดูแลระบบ" หรือไม่
        
            header('Location: indexadmin.php'); // เปลี่ยนเส้นทางไปยังหน้า "หน้าหลัก" ของแอดมิน
            exit(); // หยุดการทำงานหลังจากเปลี่ยนเส้นทาง
          }

        header('Location: index.php');


        exit();

    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }

   
      }
    

    
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>เข้าสู่ระบบ</title>
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

  <main>
    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-6 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <div class="d-flex justify-content-center py-4">
                <a href="index.php" class="logo d-flex align-items-center w-auto">
                  <img src="assets/img/logo.png" alt="">
                  <span class="d-none d-lg-block">ระบบทบทวนสิทธิ</span>
                </a>
              </div><!-- End Logo -->

              <h2>Login</h2>
              <form action="" method="POST">
                <div>
                  <div class="pt-10 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">เข้าสู่ระบบ</h5>
                    <p class="text-center small">รหัสผู้ใช้งาน และรหัสผ่าน คือรหัสเข้าใช้งานเครื่องคอมพิวเตอร์ของท่าน</p>
                  </div>
                </div>
                
                <div class="row g-3 needs-validation" novalidate>
                  <div class="col-12">
                    <label for="yourUsername" class="form-label">Username</label>
                    <div class="input-group has-validation">
                      <input type="text" name="username" class="form-control" id="yourUsername" required>
                      <div class="invalid-feedback">กรุณากรอกชื่อผู้ใช้งาน</div>
                    </div>
                  </div>

                  <div class="col-12">
                    <label for="yourPassword" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="yourPassword" required>
                    <div class="invalid-feedback">กรุณากรอกรหัสผ่าน!</div>
                  </div>

                  <div class="col-12">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="remember" value="true" id="rememberMe">
                      <label class="form-check-label" for="rememberMe">จดจำฉัน</label>
                    </div>
                  </div>

                  <div class="col-12">
                    <button class="btn btn-primary w-100" type="submit">Login</button>
                  </div>
                </div>
              </form>

              <?php if (isset($error)): ?>
                <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
              <?php endif; ?>

            </div>
          </div>
        </div>
      </section>
    </div>
  </main>

</body>
</html>
