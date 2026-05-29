<?php
session_start();

$fbAppId = "4310065995909073";
$redirectUri = "http://localhost/Fashion/admin/fb-callback.php";

// Construct the Login URL
$loginUrl = "https://www.facebook.com/v21.0/dialog/oauth?" . http_build_query([
    'client_id'     => $fbAppId,
    'redirect_uri'  => $redirectUri,
    'scope'         => 'pages_show_list,pages_read_engagement,pages_manage_posts',
    'response_type' => 'code'
]);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body { font-family: sans-serif; text-align: center; margin-top: 100px; }
        .fb-btn { background: #1877F2; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Fashion Admin Panel</h2>
    <p>Please log in to manage your Facebook Pages</p><br>
    <a href="<?php echo htmlspecialchars($loginUrl); ?>" class="fb-btn">Login with Facebook</a>
</body>
</html>