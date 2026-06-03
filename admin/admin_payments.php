<?php
$con = mysqli_connect("localhost","root","","fashion");

// --- 1. HANDLE QR CODE UPDATE ---
if(isset($_POST['update_qr'])){
    $target_dir = "uploads/"; 
    if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    
    $file_name = "qr_" . time() . "_" . basename($_FILES["qr_file"]["name"]);
    $target_file = $target_dir . $file_name;

    if(move_uploaded_file($_FILES["qr_file"]["tmp_name"], $target_file)){
        $check = mysqli_query($con, "SELECT id FROM settings WHERE id=1");
        if(mysqli_num_rows($check) > 0){
            $query = "UPDATE settings SET qr_code='$file_name' WHERE id=1";
        } else {
            $query = "INSERT INTO settings (id, qr_code) VALUES (1, '$file_name')";
        }

        if(mysqli_query($con, $query)){
            echo "<script>alert('✅ QR Code updated successfully!'); window.location='?page=admin_payments';</script>";
        }
    }
}

// --- 2. HANDLE APPROVE / REJECT ---
if(isset($_POST['approve'])){
    $id = $_POST['id'];
    $type = $_POST['type'];

    if($type == 'gown'){
        mysqli_query($con,"UPDATE bookings SET payment_status='paid' WHERE id='$id'");
        mysqli_query($con,"UPDATE payments SET status='paid' WHERE booking_id='$id'");
    } elseif($type == 'makeup'){
        mysqli_query($con,"UPDATE makeup_bookings SET payment_status='paid' WHERE id='$id'");
        mysqli_query($con,"UPDATE payments SET status='paid' WHERE makeup_bookings_id='$id'");
    } elseif($type == 'package'){
        mysqli_query($con,"UPDATE package_bookings SET payment_status='paid' WHERE id='$id'");
        mysqli_query($con,"UPDATE payments SET status='paid' WHERE package_bookings_id='$id'");
    }
    echo "<script>window.location='?page=admin_payments';</script>";
    exit();
}

if(isset($_POST['reject'])){
    $id = $_POST['id'];
    $type = $_POST['type'];

    if($type == 'gown'){
        mysqli_query($con,"UPDATE bookings SET payment_status='unpaid' WHERE id='$id'");
        mysqli_query($con,"UPDATE payments SET status='rejected' WHERE booking_id='$id'");
    }
    echo "<script>window.location='?page=admin_payments';</script>";
    exit();
}

// Fetch current QR
$qr_res = mysqli_query($con, "SELECT qr_code FROM settings WHERE id=1");
$current_qr = mysqli_fetch_assoc($qr_res)['qr_code'] ?? 'placeholder.png';

// Fetch Pending Payments
$q = mysqli_query($con,"
    SELECT 
        p.*, 
        g.name as gown_name,
        ma.name as makeup_name,
        pk.package_name
    FROM payments p
    LEFT JOIN bookings b ON p.booking_id = b.id
    LEFT JOIN gowns g ON b.gown_id = g.id
    LEFT JOIN makeup_bookings mb ON p.makeup_bookings_id = mb.id
    LEFT JOIN makeup_artists ma ON mb.makeup_artist_id = ma.id
    LEFT JOIN package_bookings pb ON p.package_bookings_id = pb.id
    LEFT JOIN packages pk ON pb.package_id = pk.id
    WHERE p.status = 'pending'
    ORDER BY p.id DESC
");
?>

<style>
    :root { 
        --primary-blue: #1e3a8a; 
        --accent-blue: #3b82f6;  
        --bg-light: #f3f4f6;    
        --white: #ffffff;
        --text-dark: #1f2937;
        --border: #e5e7eb;
        --danger: #ef4444;
        --success: #10b981;
    }

    .payment-container { font-family: 'Inter', sans-serif; }
    
    h3 { color: var(--primary-blue); font-weight: 600; border-left: 5px solid var(--accent-blue); padding-left: 15px; margin-bottom: 25px; }

    .admin-card { background: var(--white); padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-bottom: 40px; }
    
    .qr-flex { display: flex; align-items: center; gap: 30px; flex-wrap: wrap; }
    .qr-preview { border: 2px solid var(--border); border-radius: 8px; padding: 5px; background: #fff; width: 150px; height: 150px; object-fit: contain; }
    
    .upload-box { flex: 1; min-width: 250px; }
    .btn-upload { background: var(--primary-blue); color: white; font-weight: bold; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-transform: uppercase; margin-top: 10px; }
    .btn-upload:hover { background: var(--accent-blue); }

    .table-container { background: var(--white); border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; }
    table { width: 100%; border-collapse: collapse; }
    th { background: var(--primary-blue); color: white; text-align: left; padding: 15px; font-size: 0.8rem; text-transform: uppercase; }
    td { padding: 15px; border-bottom: 1px solid var(--border); font-size: 0.9rem; vertical-align: middle; }
    tr:hover { background-color: #f9fafb; }
    
    .receipt-img { width: 80px; height: 110px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border); cursor: zoom-in; }
    
    .btn-approve { background: var(--white); border: 1px solid var(--success); color: var(--success); padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600; transition: 0.3s; }
    .btn-approve:hover { background: var(--success); color: white; }
    
    .btn-reject { background: #fee2e2; border: 1px solid var(--danger); color: var(--danger); padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600; text-decoration: none; }
    .btn-reject:hover { background: var(--danger); color: white; }

    .type-tag { font-size: 0.7rem; background: #dbeafe; color: #1e40af; padding: 3px 10px; border-radius: 99px; font-weight: bold; text-transform: uppercase; }
    .amount-text { font-weight: bold; color: var(--primary-blue); font-size: 1.1rem; }
    .actions-cell { display: flex; gap: 10px; }
</style>

<div class="payment-container">
    <h3>Payment Gateway Settings</h3>
    <div class="admin-card">
        <div class="qr-flex">
            <div>
                <label style="display:block; margin-bottom:10px; font-weight:bold; color:#6b7280; font-size:0.75rem; text-transform:uppercase;">Current GCash QR</label>
                <img src="uploads/<?php echo $current_qr; ?>" class="qr-preview" alt="QR Code">
            </div>
            <div class="upload-box">
                <form method="POST" enctype="multipart/form-data">
                    <label style="display:block; margin-bottom:10px; font-weight:bold; color:#6b7280; font-size:0.75rem; text-transform:uppercase;">Update QR Code Image</label>
                    <input type="file" name="qr_file" required style="display:block; margin-bottom:15px;">
                    <button type="submit" name="update_qr" class="btn-upload">Update QR Code</button>
                </form>
            </div>
        </div>
    </div>

    <h3>Pending Verifications</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Customer Booking Details</th>
                    <th>Amount Paid</th>
                    <th>Proof of Payment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($r = mysqli_fetch_assoc($q)){ 
                    $displayName = ""; $type = ""; $original_id = "";

                    if($r['booking_id']){
                        $displayName = "Gown: " . $r['gown_name'];
                        $type = "gown";
                        $original_id = $r['booking_id'];
                    } elseif($r['makeup_bookings_id']){
                        $displayName = "Makeup Artist: " . $r['makeup_name'];
                        $type = "makeup";
                        $original_id = $r['makeup_bookings_id'];
                    } elseif($r['package_bookings_id']){
                        $displayName = "Bundle: " . $r['package_name'];
                        $type = "package";
                        $original_id = $r['package_bookings_id'];
                    }
                ?>
                <tr>
                    <td><span class="type-tag"><?php echo $type; ?></span></td>
                    <td>
                        <div style="font-weight: 600;"><?php echo $displayName; ?></div>
                        <div style="font-size: 0.75rem; color: #6b7280;">Ref: #PAY-<?php echo $r['id']; ?></div>
                    </td>
                    <td class="amount-text">₱<?php echo number_format($r['amount'], 2); ?></td>
                    <td>
                        <a href="../client/<?php echo $r['receipt_image']; ?>" target="_blank">
                            <img src="../client/<?php echo $r['receipt_image']; ?>" class="receipt-img">
                        </a>
                    </td>
                    <td>
                        <form method="POST" class="actions-cell">
                            <input type="hidden" name="id" value="<?php echo $original_id; ?>">
                            <input type="hidden" name="type" value="<?php echo $type; ?>">
                            <button type="submit" name="approve" class="btn-approve">Approve</button>
                            <button type="submit" name="reject" class="btn-reject" onclick="return confirm('Are you sure you want to REJECT this payment?')">Reject</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
                <?php if(mysqli_num_rows($q) == 0): ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding: 40px; color: #9ca3af;">No pending payments found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>