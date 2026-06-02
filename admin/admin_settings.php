<?php
// CONNECTION BRIDGE
if (!isset($conn)) {
    include('../db.php');
    if (isset($con) && !isset($conn)) { $conn = $con; }
}

// Assuming your login system stores the admin ID in a session. 
// If not, we will target ID 3 based on your screenshot.
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 3;

$msg = "";
$msg_type = "";

// --- UPDATE LOGIC ---
if (isset($_POST['update_settings'])) {
    $new_user = mysqli_real_escape_string($conn, $_POST['username']);
    $curr_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $conf_pass = $_POST['confirm_password'];

    // 1. Fetch current data to verify password
    $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if (password_verify($curr_pass, $admin['password'])) {
        // Current password is correct
        
        if (!empty($new_pass)) {
            // If user wants to change password
            if ($new_pass === $conf_pass) {
                $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE admin SET username = ?, password = ? WHERE id = ?");
                $update_stmt->bind_param("ssi", $new_user, $hashed_pass, $admin_id);
                $update_stmt->execute();
                $msg = "✅ Username and Password updated successfully!";
                $msg_type = "success";
            } else {
                $msg = "❌ New passwords do not match!";
                $msg_type = "error";
            }
        } else {
            // Update username only
            $update_stmt = $conn->prepare("UPDATE admin SET username = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_user, $admin_id);
            $update_stmt->execute();
            $msg = "✅ Username updated successfully!";
            $msg_type = "success";
        }
    } else {
        $msg = "❌ Current password incorrect!";
        $msg_type = "error";
    }
}

// --- FETCH CURRENT USERNAME ---
$stmt = $conn->prepare("SELECT username FROM admin WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$current_admin = $stmt->get_result()->fetch_assoc();
?>

<style>
    :root { 
        --primary: #1e3a8a; 
        --accent: #3b82f6;  
        --white: #ffffff;
        --border: #e5e7eb;
        --success: #10b981;
        --danger: #ef4444;
    }

    .settings-card {
        max-width: 500px;
        margin: 40px auto;
        background: var(--white);
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--border);
    }

    .settings-header {
        border-bottom: 2px solid var(--border);
        margin-bottom: 25px;
        padding-bottom: 10px;
    }

    .settings-header h2 { color: var(--primary); margin: 0; }

    .form-group { margin-bottom: 20px; }
    .form-group label { 
        display: block; 
        font-size: 0.85rem; 
        font-weight: 600; 
        color: #4b5563; 
        margin-bottom: 8px; 
    }

    .form-group input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 0.95rem;
        transition: 0.2s;
        box-sizing: border-box;
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .btn-save {
        background: var(--primary);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        transition: 0.2s;
    }

    .btn-save:hover { background: #172554; }

    .alert {
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-size: 0.9rem;
        font-weight: 500;
    }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    
    .hint { font-size: 0.75rem; color: #9ca3af; margin-top: 4px; }
</style>

<div class="settings-card">
    <div class="settings-header">
        <h2>Account Settings</h2>
    </div>

    <?php if($msg): ?>
        <div class="alert alert-<?php echo $msg_type; ?>">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($current_admin['username']); ?>" required>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--border); margin: 25px 0;">

        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" placeholder="Verify current password" required>
        </div>

        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" placeholder="Leave blank to keep current">
            <p class="hint">Only fill this if you want to change your password.</p>
        </div>

        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" placeholder="Repeat new password">
        </div>

        <button type="submit" name="update_settings" class="btn-save">Update Profile</button>
    </form>
</div>