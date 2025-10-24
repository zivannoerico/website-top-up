<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin | VannMarket</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <link rel="icon" href="./assets/images/favicon.svg" type="image/x-icon">

  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap">
  <link rel="stylesheet" href="./assets/fonts/tabler-icons.min.css">
  <link rel="stylesheet" href="./assets/fonts/feather.css">
  <link rel="stylesheet" href="./assets/fonts/fontawesome.css">
  <link rel="stylesheet" href="./assets/fonts/material.css">

  <!-- CSS -->
  <link rel="stylesheet" href="./assets/css/style.css">
  <link rel="stylesheet" href="./assets/css/style-preset.css">
</head>

<body data-pc-preset="preset-1" data-pc-direction="ltr" data-pc-theme="light">

<!-- [ Pre-loader ] start -->
<div class="loader-bg">
  <div class="loader-track">
    <div class="loader-fill"></div>
  </div>
</div>
<!-- [ Pre-loader ] End -->

<!-- [ Sidebar Menu ] start -->
<nav class="pc-sidebar">
  <div class="navbar-wrapper">
    <div class="m-header">
      <a href="index.php" class="b-brand text-primary">
        <img src="./assets/images/logo-icon.svg" class="img-fluid logo-lg" alt="logo">
      </a>
    </div>
    <div class="navbar-content">
      <ul class="pc-navbar">
        <li class="pc-item">
          <a href="index.php?page=dashboard" 
             class="pc-link <?= ($_GET['page'] ?? 'dashboard') === 'dashboard' ? 'active' : '' ?>">
            <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
            <span class="pc-mtext">Dashboard Admin User</span>
          </a>
        </li>

        <li class="pc-item">
          <a href="index.php?page=products_game.php" 
             class="pc-link <?= ($_GET['page'] ?? '') === 'products_game.php' ? 'active' : '' ?>">
            <span class="pc-micon"><i class="ti ti-gamepad-2"></i></span>
            <span class="pc-mtext">Product Game</span>
          </a>
        </li>

        <li class="pc-item pc-caption">
          <label>UI Components</label>
          <i class="ti ti-dashboard"></i>
        </li>
        <li class="pc-item"><a href="#" class="pc-link"><span class="pc-micon"><i class="ti ti-typography"></i></span><span class="pc-mtext">Typography</span></a></li>
        <li class="pc-item"><a href="#" class="pc-link"><span class="pc-micon"><i class="ti ti-color-swatch"></i></span><span class="pc-mtext">Color</span></a></li>
        <li class="pc-item"><a href="#" class="pc-link"><span class="pc-micon"><i class="ti ti-plant-2"></i></span><span class="pc-mtext">Icons</span></a></li>
      </ul>
    </div>
  </div>
</nav>
<!-- [ Sidebar Menu ] end -->

<!-- [ Header ] start -->
<header class="pc-header">
  <div class="header-wrapper">
    <div class="me-auto pc-mob-drp">
      <ul class="list-unstyled">
        <li class="pc-h-item pc-sidebar-collapse">
          <a href="#" class="pc-head-link ms-0" id="sidebar-hide"><i class="ti ti-menu-2"></i></a>
        </li>
        <li class="pc-h-item d-none d-md-inline-flex">
          <form class="header-search">
            <i data-feather="search" class="icon-search"></i>
            <input type="search" class="form-control" placeholder="Search here...">
          </form>
        </li>
      </ul>
    </div>

    <div class="ms-auto">
      <ul class="list-unstyled">
        <li class="dropdown pc-h-item header-user-profile">
          <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#">
            <img src="./assets/images/user/avatar-2.jpg" alt="user-image" class="user-avtar">
            <span>Stebin Ben</span>
          </a>
          <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
            <div class="dropdown-header">
              <div class="d-flex mb-1">
                <div class="flex-shrink-0">
                  <img src="./assets/images/user/avatar-2.jpg" alt="user-image" class="user-avtar wid-35">
                </div>
                <div class="flex-grow-1 ms-3">
                  <h6 class="mb-1">Stebin Ben</h6>
                  <span>UI/UX Designer</span>
                </div>
              </div>
            </div>
            <a href="#" class="dropdown-item"><i class="ti ti-edit-circle"></i> Edit Profile</a>
            <a href="#" class="dropdown-item"><i class="ti ti-power"></i> Logout</a>
          </div>
        </li>
      </ul>
    </div>
  </div>
</header>
<!-- [ Header ] end -->

<!-- [ Main Content ] start -->
<div class="pc-container">
  <div class="pc-content">

    <?php
      // === Sistem Routing Dinamis ===
      $page = $_GET['page'] ?? 'dashboard';
      $file = __DIR__ . "/admin-page/" . basename($page) . ".php";

      if (file_exists($file)) {
        include $file;
      } else {
        echo "<div class='alert alert-danger mt-4 text-center'>
                <strong>404:</strong> Halaman <em>" . htmlspecialchars($page) . "</em> tidak ditemukan.
              </div>";
      }
    ?>

  </div>
</div>
<!-- [ Main Content ] end -->

<footer class="pc-footer">
  <div class="footer-wrapper container-fluid">
    <div class="row">
      <div class="col-sm my-1 text-center">
        <p class="m-0">© <?= date('Y') ?> VannMarket Admin Dashboard</p>
      </div>
    </div>
  </div>
</footer>

<!-- Scripts -->
<script src="./assets/js/plugins/popper.min.js"></script>
<script src="./assets/js/plugins/simplebar.min.js"></script>
<script src="./assets/js/plugins/bootstrap.min.js"></script>
<script src="./assets/js/plugins/feather.min.js"></script>
<script src="./assets/js/pcoded.js"></script>

<script>
  layout_change('light');
  change_box_container('false');
  layout_rtl_change('false');
  preset_change('preset-1');
  font_change('Public-Sans');
</script>

</body>
</html>
