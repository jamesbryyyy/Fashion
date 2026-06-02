<?php
$con = mysqli_connect("localhost","root","","fashion");

// Handle Approval
if(isset($_POST['approve'])){
    $p_id = $_POST['payment_id'];
    
    $check = mysqli_query($con, "SELECT * FROM payments WHERE id='$p_id'");
    $pay = mysqli_fetch_assoc($check);

    mysqli_query($con, "UPDATE payments SET status='approved' WHERE id='$p_id'");

    if($pay['booking_id']) {
        mysqli_query($con, "UPDATE bookings SET payment_status='paid' WHERE id='{$pay['booking_id']}'");
    } elseif($pay['makeup_bookings_id']) {
        mysqli_query($con, "UPDATE makeup_bookings SET payment_status='paid' WHERE id='{$pay['makeup_bookings_id']}'");
    } elseif($pay['package_bookings_id']) {
        mysqli_query($con, "UPDATE package_bookings SET payment_status='paid' WHERE id='{$pay['package_bookings_id']}'");
    }

    echo "<script>alert('Payment Approved'); window.location.href='admin_payments.php';</script>"; 
    exit();
}

// Handle Rejection
if(isset($_POST['reject'])){
    $p_id = $_POST['payment_id'];
    $check = mysqli_query($con, "SELECT * FROM payments WHERE id='$p_id'");
    $pay = mysqli_fetch_assoc($check);

    mysqli_query($con, "UPDATE payments SET status='rejected' WHERE id='$p_id'");

    if($pay['booking_id']) {
        mysqli_query($con, "UPDATE bookings SET payment_status='unpaid' WHERE id='{$pay['booking_id']}'");
    } elseif($pay['makeup_bookings_id']) {
        mysqli_query($con, "UPDATE makeup_bookings SET payment_status='unpaid' WHERE id='{$pay['makeup_bookings_id']}'");
    } elseif($pay['package_bookings_id']) {
        mysqli_query($con, "UPDATE package_bookings SET payment_status='unpaid' WHERE id='{$pay['package_bookings_id']}'");
    }

    echo "<script>alert('Payment Rejected'); window.location.href='admin_payments.php';</script>"; 
    exit();
}

// FETCH ALL PAYMENTS
$q = mysqli_query($con, "
    SELECT p.*, 
    g.name as gown_name, 
    ma.name as artist_name, 
    pk.package_name 
    FROM payments p
    LEFT JOIN bookings b ON p.booking_id = b.id
    LEFT JOIN gowns g ON b.gown_id = g.id
    LEFT JOIN makeup_bookings mb ON p.makeup_bookings_id = mb.id
    LEFT JOIN makeup_artists ma ON mb.makeup_artist_id = ma.id
    LEFT JOIN package_bookings pb ON p.package_bookings_id = pb.id
    LEFT JOIN packages pk ON pb.package_id = pk.id
    ORDER BY p.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Review | Aura Blue Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary-blue: #1e3a8a;
            --accent-blue: #3b82f6;
            --bg-light: #f3f4f6;
            --white: #ffffff;
            --text-dark: #1f2937;
            --border: #e5e7eb;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: var(--bg-light); 
            color: var(--text-dark); 
            margin: 0; 
            padding: 40px; 
        }

        h2 { 
            color: var(--primary-blue); 
            font-weight: 600; 
            border-left: 5px solid var(--accent-blue);
            padding-left: 15px;
            margin-bottom: 30px;
        }

        .table-container { 
            background: var(--white); 
            border-radius: 8px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; }

        th { 
            background: var(--primary-blue); 
            color: white; 
            text-align: left; 
            padding: 18px 15px; 
            font-size: 0.8rem; 
            text-transform: uppercase; 
            letter-spacing: 1px;
        }

        td { 
            padding: 15px; 
            border-bottom: 1px solid var(--border); 
            font-size: 0.9rem; 
            vertical-align: middle;
        }

        tr:hover { background-color: #f9fafb; }

        /* Receipt Image Styling */
        .receipt-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid var(--border);
            cursor: pointer;
            transition: 0.3s;
        }
        .receipt-thumb:hover { transform: scale(1.1); border-color: var(--accent-blue); }

        /* Status Badges */
        .badge {
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }

        /* Buttons */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            transition: 0.2s;
            margin-right: 5px;
        }
        .btn-approve { background: var(--success); color: white; }
        .btn-approve:hover { background: #059669; }
        
        .btn-reject { background: var(--white); border: 1px solid var(--danger); color: var(--danger); }
        .btn-reject:hover { background: var(--danger); color: white; }

        .processed-text { color: #9ca3af; font-style: italic; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="container">
    <h2>Payment Management</h2>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Reference Item</th>
                    <th>Amount</th>
                    <th>Payment Proof</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($r=mysqli_fetch_assoc($q)){ 
                    $desc = $r['gown_name'] ?? $r['artist_name'] ?? $r['package_name'] ?? "Unknown Item";
                    
                    // Assign class based on status
                    $statusClass = "status-" . $r['status'];
                ?>
                <tr>
                    <td style="font-weight: 600;"><?php echo $desc; ?></td>
                    <td style="color: var(--primary-blue); font-weight: bold;">
                        ₱<?php echo number_format($r['amount'], 2); ?>
                    </td>
                    <td>
                        <a href="../client/<?php echo $r['receipt_image']; ?>" target="_blank" title="View Full Receipt">
                            <img src="../client/<?php echo $r['receipt_image']; ?>" class="receipt-thumb">
                        </a>
                    </td>
                    <td>
                        <span class="badge <?php echo $statusClass; ?>">
                            <?php echo $r['status']; ?>
                        </span>
                    </td>
                    <td>
                        <?php if($r['status'] == 'pending'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="payment_id" value="<?php echo $r['id']; ?>">
                                <button type="submit" name="approve" class="btn btn-approve">Approve</button>
                                <button type="submit" name="reject" class="btn btn-reject">Reject</button>
                            </form>
                        <?php else: ?>
                            <span class="processed-text">Cleared</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>