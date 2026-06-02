<?php
// 1. Start Output Buffering to prevent "Headers already sent" errors
ob_start(); 

$con = mysqli_connect("localhost", "root", "", "fashion");
// include("../config.php"); 
include('auth_check.php');

/* HELPER FUNCTION FOR REDIRECTING */
function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "<script type='text/javascript'>window.location.href='$url';</script>";
    }
    exit();
}

/* APPROVE / REJECT LOGIC - FIXED */
if(isset($_POST['update_status'])){
    $id = mysqli_real_escape_string($con, $_POST['id']);
    // FIXED: Using 'update_status' to match the button name attribute
    $status = mysqli_real_escape_string($con, $_POST['update_status']); 
    $table = mysqli_real_escape_string($con, $_POST['table_name']);
    
    $allowed_tables = ['bookings', 'makeup_bookings', 'package_bookings'];
    
    if (in_array($table, $allowed_tables)) {
        mysqli_query($con, "UPDATE $table SET status='$status' WHERE id='$id'");
        redirect($_SERVER['PHP_SELF']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Management | Aura Blue Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary-blue: #1e3a8a; --accent-blue: #3b82f6; --bg-light: #f3f4f6;
            --white: #ffffff; --text-dark: #1f2937; --border: #e5e7eb;
            --success: #10b981; --danger: #ef4444;
        }
        
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-light); color: var(--text-dark); margin: 0; padding: 40px; }
        h2 { color: var(--primary-blue); font-weight: 600; border-left: 5px solid var(--accent-blue); padding-left: 15px; margin-bottom: 40px; }
        h3 { font-size: 1.1rem; color: var(--primary-blue); margin: 40px 0 15px 0; display: flex; align-items: center; gap: 10px; }
        .table-container { background: var(--white); border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: var(--primary-blue); color: white; text-align: left; padding: 15px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 12px 15px; border-bottom: 1px solid var(--border); font-size: 0.85rem; }
        tr:hover { background-color: #f9fafb; }

        .badge { padding: 4px 10px; border-radius: 99px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }

        .btn-group { display: flex; gap: 5px; align-items: center; }
        .btn-action { border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; transition: 0.2s; text-decoration: none; display: inline-block; }
        .btn-approve { background: var(--success); color: white; }
        .btn-reject { background: var(--white); border: 1px solid var(--danger); color: var(--danger); }
        .btn-view { background: var(--accent-blue); color: white; }
        .btn-view:hover { background: var(--primary-blue); }
        
        .processed { color: #9ca3af; font-size: 0.75rem; font-style: italic; }
    </style>
</head>
<body>

<div class="container">
    <h2>Admin Booking Management</h2>

    <!-- ================= GOWN BOOKINGS ================= -->
    <h3>👗 Gown Rental Requests</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Gown Name</th>
                    <th>Rental Period</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q = mysqli_query($con,"SELECT b.*, g.name FROM bookings b JOIN gowns g ON g.id = b.gown_id ORDER BY b.id DESC");
                while($r=mysqli_fetch_assoc($q)){
                ?>
                <tr>
                    <td><strong><?php echo $r['name']; ?></strong></td>
                    <td><?php echo date('M d', strtotime($r['date_from'])); ?> - <?php echo date('M d, Y', strtotime($r['date_to'])); ?></td>
                    <td><span class="badge status-<?php echo $r['status']; ?>"><?php echo $r['status']; ?></span></td>
                    <td>
                        <div class="btn-group">
                            <a href="view_details.php?id=<?php echo $r['id']; ?>&type=gown" class="btn-action btn-view">View</a>
                            <?php if($r['status'] == 'pending'): ?>
                            <form method="POST" style="display:flex; gap:5px;">
                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                <input type="hidden" name="table_name" value="bookings">
                                <button name="update_status" value="approved" class="btn-action btn-approve">Approve</button>
                                <button name="update_status" value="rejected" class="btn-action btn-reject">Reject</button>
                            </form>
                            <?php else: ?><span class="processed">Closed</span><?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- ================= MAKEUP BOOKINGS ================= -->
    <h3>💄 Artist Appointments</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Makeup Artist</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q = mysqli_query($con,"SELECT mb.*, ma.name FROM makeup_bookings mb JOIN makeup_artists ma ON ma.id = mb.makeup_artist_id ORDER BY mb.id DESC");
                while($r=mysqli_fetch_assoc($q)){
                ?>
                <tr>
                    <td><strong><?php echo $r['name']; ?></strong></td>
                    <td><?php echo $r['booking_date']; ?> @ <?php echo $r['booking_time']; ?></td>
                    <td><span class="badge status-<?php echo $r['status']; ?>"><?php echo $r['status']; ?></span></td>
                    <td>
                        <div class="btn-group">
                            <a href="view_details.php?id=<?php echo $r['id']; ?>&type=makeup" class="btn-action btn-view">View</a>
                            <?php if($r['status'] == 'pending'): ?>
                            <form method="POST" style="display:flex; gap:5px;">
                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                <input type="hidden" name="table_name" value="makeup_bookings">
                                <button name="update_status" value="approved" class="btn-action btn-approve">Approve</button>
                                <button name="update_status" value="rejected" class="btn-action btn-reject">Reject</button>
                            </form>
                            <?php else: ?><span class="processed">Closed</span><?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- ================= PACKAGE BOOKINGS ================= -->
    <h3>🎁 Exclusive Package Bookings</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Package Name</th>
                    <th>Includes</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q = mysqli_query($con,"SELECT pb.*, p.package_name, g.name as gname, ma.name as mname 
                                        FROM package_bookings pb 
                                        JOIN packages p ON p.id = pb.package_id 
                                        JOIN gowns g ON g.id = pb.gown_id
                                        JOIN makeup_artists ma ON ma.id = pb.makeup_artist_id
                                        ORDER BY pb.id DESC");
                while($r=mysqli_fetch_assoc($q)){
                ?>
                <tr>
                    <td><strong><?php echo $r['package_name']; ?></strong></td>
                    <td style="font-size: 0.75rem; color: #666;">👗 <?php echo $r['gname']; ?><br>💄 <?php echo $r['mname']; ?></td>
                    <td><?php echo $r['date_from']; ?> - <?php echo $r['date_to']; ?></td>
                    <td><span class="badge status-<?php echo $r['status']; ?>"><?php echo $r['status']; ?></span></td>
                    <td>
                        <div class="btn-group">
                            <a href="view_details.php?id=<?php echo $r['id']; ?>&type=package" class="btn-action btn-view">View</a>
                            <?php if($r['status'] == 'pending'): ?>
                            <form method="POST" style="display:flex; gap:5px;">
                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                <input type="hidden" name="table_name" value="package_bookings">
                                <button name="update_status" value="approved" class="btn-action btn-approve">Approve</button>
                                <button name="update_status" value="rejected" class="btn-action btn-reject">Reject</button>
                            </form>
                            <?php else: ?><span class="processed">Closed</span><?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
<?php ob_end_flush(); ?>