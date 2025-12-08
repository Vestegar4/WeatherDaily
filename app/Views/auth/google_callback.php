<?php
session_start();

require_once '../../../config/google_init.php';
require_once '../../../config/database.php';

if (isset($_GET['code'])) {
    
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        
        $google_oauth = new Google\Service\Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        
        $email = $google_account_info->email;
        $name = $google_account_info->name;
        $google_id = $google_account_info->id;
        $avatar = $google_account_info->picture;

        $database = new Database();
        $pdo = $database->getConnection();

        if (!$pdo) { die("Koneksi Database Gagal."); }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            if (empty($user['google_id'])) {
                $update = $pdo->prepare("UPDATE users SET google_id = ?, avatar = ? WHERE id = ?");
                $update->execute([$google_id, $avatar, $user['id']]);
            }
            
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'city' => $user['city'] ?? 'Jakarta',
                'avatar' => $avatar
            ];

        } else {
            $random_pass = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT);
            
            $insert = $pdo->prepare("INSERT INTO users (name, email, google_id, avatar, password, city) VALUES (?, ?, ?, ?, ?, 'Jakarta')");
            $insert->execute([$name, $email, $google_id, $avatar, $random_pass]);
            
            $new_user_id = $pdo->lastInsertId();
            $_SESSION['user'] = [
                'id' => $new_user_id,
                'name' => $name,
                'email' => $email,
                'city' => 'Jakarta',
                'avatar' => $avatar
            ];
        }

        header("Location: ../dashboard/index.php");
        exit();

    } else {
        header("Location: login.php?error=google_rejected");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>