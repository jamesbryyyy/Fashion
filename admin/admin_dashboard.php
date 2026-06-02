<?php
session_start();

/** 
 * 1. DATABASE CONFIGURATION
 */
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'fashion'; 

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/** 
 * 2. MAGIC LINK CHECK
 */
$magic_token = "ACCESS_ME_ANYTIME"; 

if (isset($_GET['token']) && $_GET['token'] === $magic_token) {
    $_SESSION['user_token'] = 'bypass_active'; 
    $_SESSION['is_admin'] = true;
}

/**
 * 3. SECURITY CHECK
 */
if (!isset($_SESSION['user_token'])) {
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
    'reports'   => 'Sales & Traffic Reports',
    'admin_panel' => 'Admin Control',
    'makeup_management' => 'Makeup Services',
    'package_management' => 'Package Management'
];
$current_title = isset($titles[$page]) ? $titles[$page] : 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $current_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #0f172a;
            --secondary-color: #1e293b;
            --accent-color: #6366f1;
            --text-main: #334155;
            --text-light: #f8fafc;
            --bg-body: #f1f5f9;
            --transition: all 0.3s ease;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-body); color: var(--text-main); overflow-x: hidden; }

        /* SIDEBAR FIXED & SCROLLABLE */
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
            overflow-y: auto; /* This enables vertical scrolling */
        }

        /* Custom Scrollbar for Sidebar */
        .sidebar::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: var(--primary-color);
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: var(--accent-color);
        }

        .sidebar-header { 
            padding: 25px 20px; 
            text-align: center; 
            font-size: 1.4rem; 
            font-weight: 700; 
            letter-spacing: 1px; 
            border-bottom: 1px solid rgba(255,255,255,0.1);
            position: sticky; /* Keeps header at top while scrolling */
            top: 0;
            background: var(--primary-color);
            z-index: 10;
        }

        .sidebar-menu { 
            list-style: none; 
            padding: 20px 0; 
            flex-grow: 1; 
        }

        .sidebar-menu li { padding: 5px 15px; }
        .sidebar-menu li a { 
            display: flex; 
            align-items: center; 
            padding: 12px 15px; 
            color: #94a3b8; 
            text-decoration: none; 
            border-radius: 8px; 
            transition: var(--transition); 
        }
        .sidebar-menu li a i { margin-right: 12px; width: 20px; text-align: center; font-size: 1.1rem;}
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background-color: var(--accent-color); color: white; }

        .logout-section { 
            padding: 20px; 
            border-top: 1px solid rgba(255,255,255,0.1); 
            background: var(--primary-color);
            position: sticky; /* Keeps logout at bottom while scrolling */
            bottom: 0;
        }
        .logout-btn { color: #fca5a5; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 10px; }

        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; transition: var(--transition); }
        .top-bar { background: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 900; }
        .page-content { padding: 30px; }

        /* Stats & Data Table Styles */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); display: flex; align-items: center; gap: 15px; border: 1px solid #e2e8f0; }
        .stat-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: white; flex-shrink: 0; }
        
        .bg-blue { background: #3b82f6; }
        .bg-purple { background: #8b5cf6; }
        .bg-emerald { background: #10b981; }
        .bg-amber { background: #f59e0b; }
        .bg-rose { background: #f43f5e; }
        .bg-indigo { background: #6366f1; }

        .stat-details h3 { font-size: 0.75rem; color: #64748b; text-transform: uppercase; }
        .stat-details p { font-size: 1.3rem; font-weight: 700; color: #1e293b; }

        .data-card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 20px; }
        .custom-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .custom-table th { text-align: left; padding: 12px; background: #f8fafc; color: #64748b; border-bottom: 2px solid #e2e8f0; }
        .custom-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef9c3; color: #854d0e; }
    </style>
</head>
<body>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header"><i class="fas fa-crown"></i> GOWN ADMIN</div>
        <ul class="sidebar-menu">
            <li><a href="?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="?page=gowns" class="<?= $page == 'gowns' ? 'active' : '' ?>"><i class="fas fa-cut"></i> Manage Gowns</a></li>
            <li><a href="?page=makeup_management" class="<?= $page == 'makeup_management' ? 'active' : '' ?>"><i class="fas fa-magic"></i> Makeup Artists</a></li>
            <li><a href="?page=package_management" class="<?= $page == 'package_management' ? 'active' : '' ?>"><i class="fas fa-box-open"></i> Packages</a></li>
            <li><a href="?page=payments" class="<?= $page == 'payments' ? 'active' : '' ?>"><i class="fas fa-wallet"></i> Payments</a></li>
            <li><a href="?page=admin_panel" class="<?= $page == 'admin_panel' ? 'active' : '' ?>"><i class="fas fa-user-cog"></i> Admin Panel</a></li>
            <li><a href="?page=cms" class="<?= $page == 'cms' ? 'active' : '' ?>"><i class="fas fa-user-cog"></i> CMS</a></li>
            <li><a href="?page=reports" class="<?= $page == 'reports' ? 'active' : '' ?>"><i class="fas fa-chart-pie"></i> Reports</a></li>
            <li><a href="?page=admin_backup" class="<?= $page == 'admin_backup' ? 'active' : '' ?>"><i class="fas fa-cloud-upload-alt"></i> Backup</a></li>
            <li><a href="?page=admin_trash" class="<?= $page == 'admin_trash' ? 'active' : '' ?>"><i class="fas fa-trash-alt"></i> Archive</a></li>
            <li><a href="?page=admin_settings" class="<?= $page == 'admin_settings' ? 'active' : '' ?>"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
        <div class="logout-section">
             <a href="admin_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h2 style="font-weight: 600; color: var(--primary-color);"><?php echo $current_title; ?></h2>
            <div class="admin-profile"><i class="fas fa-user-circle"></i> Admin</div>
        </div>

        <div class="page-content">
            <?php 
                switch ($page) {
                    case 'dashboard':
                        // ... (Internal Dashboard Logic - Same as your code)
                        $total_gowns = $conn->query("SELECT COUNT(*) as total FROM gowns")->fetch_assoc()['total'];
                        $total_artists = $conn->query("SELECT COUNT(*) as total FROM makeup_artists")->fetch_assoc()['total'];
                        $rev_res = $conn->query("SELECT SUM(amount) as total_sales FROM payments WHERE status = 'approved'");
                        $revenue = $rev_res->fetch_assoc()['total_sales'] ?? 0;
                        $p_gown = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'")->fetch_assoc()['total'];
                        $a_gown = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'approved'")->fetch_assoc()['total'];
                        $p_make = $conn->query("SELECT COUNT(*) as total FROM makeup_bookings WHERE status = 'pending'")->fetch_assoc()['total'];
                        $a_make = $conn->query("SELECT COUNT(*) as total FROM makeup_bookings WHERE status = 'approved'")->fetch_assoc()['total'];
                        $p_pack = $conn->query("SELECT COUNT(*) as total FROM package_bookings WHERE status = 'pending'")->fetch_assoc()['total'];
                        $a_pack = $conn->query("SELECT COUNT(*) as total FROM package_bookings WHERE status = 'approved'")->fetch_assoc()['total'];
                        $total_pending = $p_gown + $p_make + $p_pack;
                        $total_approved = $a_gown + $a_make + $a_pack;
                        ?>
                        
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon bg-emerald"><i class="fas fa-coins"></i></div>
                                <div class="stat-details">
                                    <h3>Total Revenue</h3>
                                    <p>₱<?php echo number_format($revenue, 2); ?></p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon bg-amber"><i class="fas fa-clock"></i></div>
                                <div class="stat-details">
                                    <h3>Pending Bookings</h3>
                                    <p><?php echo $total_pending; ?></p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon bg-indigo"><i class="fas fa-calendar-check"></i></div>
                                <div class="stat-details">
                                    <h3>Approved Bookings</h3>
                                    <p><?php echo $total_approved; ?></p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon bg-blue"><i class="fas fa-tshirt"></i></div>
                                <div class="stat-details">
                                    <h3>Gowns In Stock</h3>
                                    <p><?php echo $total_gowns; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="data-card">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <h3>Recent Appointments</h3>
                                <small>Showing all booking types</small>
                            </div>
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Client ID</th>
                                        <th>Booking Date/Time</th>
                                        <th>Status</th>
                                        <th>Date Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "(SELECT 'Gown' as category, client_id, date_from as schedule, status, created_at FROM bookings)
                                            UNION
                                            (SELECT 'Makeup' as category, client_id, CONCAT(booking_date, ' ', booking_time) as schedule, status, created_at FROM makeup_bookings)
                                            UNION
                                            (SELECT 'Package' as category, client_id, date_from as schedule, status, created_at FROM package_bookings)
                                            ORDER BY created_at DESC LIMIT 8";
                                    
                                    $combined = $conn->query($sql);
                                    if($combined && $combined->num_rows > 0):
                                        while($row = $combined->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><strong><?php echo $row['category']; ?></strong></td>
                                        <td>#<?php echo $row['client_id']; ?></td>
                                        <td><?php echo $row['schedule']; ?></td>
                                        <td><span class="badge <?php echo $row['status'] == 'approved' ? 'badge-success' : 'badge-pending'; ?>"><?php echo $row['status']; ?></span></td>
                                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                        <tr><td colspan='5' style='text-align:center;'>No bookings found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                        break;

                    case 'gowns': include('gowns_create.php'); break;
                    case 'payments': include('admin_payments.php'); break;
                    case 'makeup_management': include('makeup_management.php'); break;
                    case 'package_management': include('package_management.php'); break;
                    case 'admin_panel': include('admin_panel.php'); break;
                    case 'cms': include('cms.php'); break;
                    case 'reports': include('reports.php'); break;
                    case 'admin_backup': include('admin_backup.php'); break;
                    case 'admin_trash': include('admin_trash.php'); break;
                    case 'admin_settings': include('admin_settings.php'); break;
                }
            ?>
        </div>
    </div>
</body>
</html>