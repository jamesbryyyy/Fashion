<?php
include('auth_check.php');
include('../db.php'); // Ensure this file defines $conn or $con

$message_status = null;
$fbAppId = "4310065995909073"; 
$redirectUri = "http://localhost/Fashion/admin/fb-callback.php";

// 1. FETCH FACEBOOK SETTINGS
// Note: Ensure $admin_id is defined in your auth_check.php
$stmt = $conn->prepare("SELECT * FROM facebook_settings WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$fb_result = $stmt->get_result();
$fb_account = $fb_result->fetch_assoc();

// 2. HANDLE POSTING LOGIC
if (isset($_POST['publish_fb']) && $fb_account) {
    $pageToken = $fb_account['access_token'];
    $pageId = $fb_account['page_id'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    $endpoint = "feed";
    $postData = [
        'access_token' => $pageToken,
        'message'      => $message
    ];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $endpoint = "photos";
        $postData['caption'] = $message;
        unset($postData['message']); 
        $postData['source'] = new CURLFile($_FILES['image']['tmp_name'], $_FILES['image']['type']);
    }

    $url = "https://graph.facebook.com/v21.0/$pageId/$endpoint";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);

    if (isset($result['id']) || isset($result['post_id'])) {
        $message_status = ['type' => 'success', 'text' => '✨ Content successfully published to ' . $fb_account['page_name']];
    } else {
        $error_msg = $result['error']['message'] ?? 'Unknown Facebook Error';
        $message_status = ['type' => 'error', 'text' => '❌ FB Error: ' . $error_msg];
    }
}

$permissions = ['pages_show_list', 'pages_read_engagement', 'pages_manage_posts', 'public_profile'];
$loginUrl = "https://www.facebook.com/v21.0/dialog/oauth?" . http_build_query([
    'client_id'     => $fbAppId,
    'redirect_uri'  => $redirectUri,
    'scope'         => implode(',', $permissions),
    'response_type' => 'code'
]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Facebook CMS | Aura Blue</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --primary-blue: #1e3a8a;
            --accent-blue: #3b82f6;
            --fb-blue: #1877F2;
            --bg-light: #f3f4f6;
            --white: #ffffff;
            --text-dark: #1f2937;
            --border: #e5e7eb;
        }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: var(--bg-light); 
            color: var(--text-dark); 
            margin: 0; 
            padding: 40px; 
        }

        .cms-wrapper { max-width: 700px; margin: 0 auto; }

        h2 { 
            color: var(--primary-blue); 
            font-weight: 600; 
            border-left: 5px solid var(--accent-blue);
            padding-left: 15px;
            margin-bottom: 10px;
        }
        
        .subtitle { color: #6b7280; margin-bottom: 30px; font-size: 0.9rem; }

        /* Status Badges */
        .status-badge { 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 25px; 
            font-weight: 600; 
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .status-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .status-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* Card Panels */
        .admin-card { 
            background: var(--white); 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border);
        }

        /* Connection Box */
        .fb-connect-box { 
            text-align: center; 
            padding: 40px 20px; 
        }
        .fb-icon-large { font-size: 3.5rem; color: var(--fb-blue); margin-bottom: 20px; }
        
        .fb-btn { 
            background: var(--fb-blue); 
            color: white; 
            padding: 14px 28px; 
            text-decoration: none; 
            border-radius: 8px; 
            font-weight: bold; 
            display: inline-flex; 
            align-items: center;
            gap: 10px;
            transition: 0.3s;
            box-shadow: 0 4px 12px rgba(24, 119, 242, 0.3);
        }
        .fb-btn:hover { background: #166fe5; transform: translateY(-2px); }

        /* Connected Info Bar */
        .connected-info { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            margin-bottom: 25px; 
            padding: 15px; 
            background: #eff6ff; 
            border-radius: 10px; 
            border: 1px solid #dbeafe;
        }
        .connected-info span { font-size: 0.9rem; color: var(--primary-blue); }

        /* Form Controls */
        .form-group { margin-bottom: 20px; }
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600; 
            font-size: 0.8rem; 
            color: #4b5563; 
            text-transform: uppercase;
        }
        
        textarea, input[type="file"] { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid var(--border); 
            border-radius: 8px; 
            box-sizing: border-box; 
            font-family: inherit;
            font-size: 0.9rem;
        }
        
        textarea:focus { border-color: var(--accent-blue); outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }

        .btn-submit { 
            background: var(--primary-blue); 
            color: white; 
            border: none; 
            padding: 15px; 
            width: 100%; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: bold; 
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .btn-submit:hover { background: var(--accent-blue); }

        .switch-link { 
            margin-left: auto; 
            font-size: 0.75rem; 
            color: #ef4444; 
            text-decoration: none; 
            font-weight: 600;
        }
        .switch-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="cms-wrapper">
    <div class="header-section">
        <h2>Facebook CMS</h2>
        <p class="subtitle">Broadcast your luxury collection directly to social media</p>
    </div>

    <!-- NOTIFICATION AREA -->
    <?php if ($message_status): ?>
        <div class="status-badge status-<?= $message_status['type'] ?>">
            <i class="fas <?= $message_status['type'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i>
            <?= $message_status['text'] ?>
        </div>
    <?php endif; ?>

    <?php if (!$fb_account): ?>
        <!-- DISCONNECTED STATE -->
        <div class="admin-card">
            <div class="fb-connect-box">
                <i class="fab fa-facebook fb-icon-large"></i>
                <p style="color: #4b5563; margin-bottom: 25px;">Connect your Aura Luxury Rentals Facebook Page to start posting.</p>
                <a href="<?= htmlspecialchars($loginUrl) ?>" class="fb-btn">
                    <i class="fab fa-facebook-f"></i> Connect Facebook Account
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- CONNECTED STATE -->
        <div class="connected-info">
            <i class="fas fa-link" style="color: var(--accent-blue);"></i>
            <span>Linked Page: <strong><?= htmlspecialchars($fb_account['page_name']) ?></strong></span>
            <a href="<?= $loginUrl ?>" class="switch-link"><i class="fas fa-sync-alt"></i> Switch Page</a>
        </div>

        <div class="admin-card">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Post Content</label>
                    <textarea name="message" rows="5" placeholder="Write something captivating about your new gown or artist..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Visual Media (Optional Photo)</label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <button type="submit" name="publish_fb" class="btn-submit">
                    <i class="fas fa-share-square"></i> Publish to Feed
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>