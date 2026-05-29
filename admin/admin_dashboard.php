<?php
session_start();

/** 
 * 1. CONFIGURATION
 * Change 'ACCESS_ME_ANYTIME' to a very long secret word.
 */
$magic_token = "ACCESS_ME_ANYTIME"; 

/**
 * 2. MAGIC LINK CHECK
 * If you visit: admin_dashboard.php?token=ACCESS_ME_ANYTIME
 * This "fakes" the login even if you are logged out.
 */
if (isset($_GET['token']) && $_GET['token'] === $magic_token) {
    $_SESSION['user_token'] = 'bypass_active'; // Sets the session variable your script expects
    $_SESSION['is_admin'] = true;
}

/**
 * 3. SECURITY CHECK
 * We check if 'user_token' exists. 
 * This is the variable your fb-callback.php sets.
 */
if (!isset($_SESSION['user_token'])) {
    // If no session and no magic token, go back to login
    header("Location: admin_login.php"); 
    exit();
}

/**
 * 4. ROUTING LOGIC
 */
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$titles = [
    'dashboard' => 'Admin Dashboard',
    'gowns'     => 'Manage Gowns',
    'cms'       => 'Facebook CMS',
    'payments'  => 'Payment Management',
    'reports'   => 'Sales & Traffic Reports'
];
$current_title = isset($titles[$page]) ? $titles[$page] : 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $current_title; ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #0f172a; /* Slate 900 */
            --secondary-color: #1e293b; /* Slate 800 */
            --accent-color: #6366f1; /* Indigo 500 */
            --text-main: #334155;
            --text-light: #f8fafc;
            --bg-body: #f1f5f9;
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-color);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            color: var(--text-light);
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
            flex-grow: 1;
        }

        .sidebar-menu li {
            padding: 5px 15px;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
        }

        .sidebar-menu li a i { 
            margin-right: 12px; 
            width: 20px; 
            text-align: center; 
            font-size: 1.1rem;
        }

        .sidebar-menu li a:hover, 
        .sidebar-menu li a.active {
            background-color: var(--accent-color);
            color: white;
        }

        .logout-section {
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .logout-btn {
            color: #fca5a5;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: var(--transition);
        }

        .top-bar {
            background: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .menu-toggle {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--primary-color);
        }

        .page-content {
            padding: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        /* Status & Mobile Classes */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        /* RESPONSIVE DESIGN */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .menu-toggle {
                display: block;
            }
            .sidebar-overlay.active {
                display: block;
            }
        }

        @media (max-width: 480px) {
            .top-bar {
                padding: 15px;
            }
            .page-content {
                padding: 15px;
            }
            .top-bar h2 {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>

    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="overlay"></div>

    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-crown"></i> GOWN ADMIN
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="?page=gowns" class="<?= $page == 'gowns' ? 'active' : '' ?>">
                    <i class="fas fa-cut"></i> Manage Gowns
                </a>
            </li>
            <li>
                <a href="?page=cms" class="<?= $page == 'cms' ? 'active' : '' ?>">
                    <i class="fab fa-facebook-square"></i> Facebook CMS
                </a>
            </li>
            <li>
                <a href="?page=payments" class="<?= $page == 'payments' ? 'active' : '' ?>">
                    <i class="fas fa-wallet"></i> Payments
                </a>
            </li>
            <li>
                <a href="?page=reports" class="<?= $page == 'reports' ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i> Reports
                </a>
            </li>
        </ul>
        <div class="logout-section">
             <a href="admin_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div class="menu-toggle" id="menu-btn">
                    <i class="fas fa-bars"></i>
                </div>
                <h2 style="font-weight: 600; color: var(--primary-color);"><?php echo $current_title; ?></h2>
            </div>
            <div class="admin-profile" style="font-size: 0.9rem; font-weight: 500;">
                <i class="fas fa-user-circle" style="margin-right: 5px;"></i> Admin
            </div>
        </div>

        <div class="page-content">
            <div class="card">
            <?php 
                // DYNAMIC CONTENT LOADER
                switch ($page) {
                    case 'gowns':
                        include('gowns_create.php');
                        break;
                    case 'cms':
                        include('cms.php');
                        break;
                    case 'payments':
                        include('admin_payments.php');
                        break;
                    case 'reports':
                        include('reports.php');
                        break;
                    default:
                        echo "<h3>Welcome to the Dashboard</h3><p>Select an option from the sidebar to begin.</p>";
                        break;
                }
            ?>
            </div>
        </div>
    </div>

    <script>
        const menuBtn = document.getElementById('menu-btn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleMenu() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        menuBtn.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);

        // Close sidebar if window is resized above mobile breakpoint
        window.addEventListener('resize', () => {
            if (window.innerWidth > 992) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        });
    </script>
</body>
</html>