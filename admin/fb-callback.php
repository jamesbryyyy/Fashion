<?php
include('../db.php');

if (!isset($_SESSION['admin_id'])) { die("Unauthorized"); }

$fbAppId = "4310065995909073";
$fbAppSecret = "c256d303deba702ec50f8b99bc5b15f7"; 
$redirectUri = "http://localhost/Fashion/admin/fb-callback.php";

if (isset($_GET['code'])) {
    // 1. Get User Token
    $tokenUrl = "https://graph.facebook.com/v21.0/oauth/access_token?" . http_build_query([
        'client_id' => $fbAppId,
        'redirect_uri' => $redirectUri,
        'client_secret' => $fbAppSecret,
        'code' => $_GET['code']
    ]);
    $resp = json_decode(file_get_contents($tokenUrl), true);
    $userToken = $resp['access_token'];

    // 2. Get Page Access Token and Name
    $pagesUrl = "https://graph.facebook.com/v21.0/me/accounts?access_token=" . $userToken;
    $pagesResp = json_decode(file_get_contents($pagesUrl), true);

    if (!empty($pagesResp['data'])) {
        $page = $pagesResp['data'][0]; // Gets the first page found
        $pageId = $page['id'];
        $pageName = $page['name'];
        $pageToken = $page['access_token'];
        $adminId = $_SESSION['admin_id'];

        // 3. Save or Update to Database
        $stmt = $conn->prepare("INSERT INTO facebook_settings (admin_id, access_token, page_id, page_name) 
                                VALUES (?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE access_token=?, page_id=?, page_name=?");
        $stmt->bind_param("issssss", $adminId, $pageToken, $pageId, $pageName, $pageToken, $pageId, $pageName);
        $stmt->execute();

        header("Location: admin_dashboard.php?page=cms&success=connected");
    }
}