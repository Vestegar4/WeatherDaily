<?php

namespace App\Controllers;

use App\Models\User;

class UserController
{
    public function updateCity()
    {
        session_start();

        $city = $_POST['city'];
        $userId = $_SESSION['user']['id'];

        $user = new User();
        $user->updateCity($userId, $city);

        $_SESSION['user']['city'] = $city;

        header("Location: /dashboard");
        exit();
    }
}