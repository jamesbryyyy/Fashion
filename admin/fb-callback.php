<?php
session_start();

$fbAppId = "4310065995909073";
$fbAppSecret = "adca9925f27ae437340069c1dab7c637"; 
$redirectUri = "http://localhost/Fashion/admin/fb-callback.php";

if (!isset($_GET['code'])) {
    die("Error: No code received. Please try logging in again.");
}

// 1. Exchange Code for Access Token using cURL
$tokenUrl = "https://graph.facebook.com/v21.0/oauth/access_token?" . http_build_query([
    'client_id'     => $fbAppId,
    'client_secret' => $fbAppSecret,
    'redirect_uri'  => $redirectUri,
    'code'          => $_GET['code']
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);

if (isset($data['error'])) {
    echo "<h3>Token Error</h3>" . $data['error']['message'];
    echo "<br><a href='admin_login.php'>Go Back</a>";
    exit;
}

$userAccessToken = $data['access_token'];

// 2. Fetch User's Pages
$pageUrl = "https://graph.facebook.com/v21.0/me/accounts?access_token=" . $userAccessToken;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $pageUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$pageResponse = curl_exec($ch);
$pagesData = json_decode($pageResponse, true);
curl_close($ch);

if (!isset($pagesData['data'])) {
    die("Error fetching pages. Check your app permissions.");
}

// 3. Store in session
$_SESSION['user_token'] = $userAccessToken;
$_SESSION['pages'] = $pagesData['data'];

// 4. Redirect to Dashboard
header("Location: admin_dashboard.php");
exit();