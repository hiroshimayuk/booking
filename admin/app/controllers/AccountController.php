<?php
require_once "core/models/Account.php";

class AccountController {
    public function login() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST["username"];
            $password = $_POST["password"];

            $account = new Account();
            if ($account->checkLogin($username, $password)) {
                session_start();
                $_SESSION["user"] = $username;
                header("Location: /booking/admin/web.php?controller=dashboard");
            } else {
                echo "Sai tài khoản hoặc mật khẩu";
            }
        }
        include "views/account/login.php";
    }
}
?>
