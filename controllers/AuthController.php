<?php
class AuthController {
    public function login() {
        $role = $_GET['role'] ?? ($_POST['role'] ?? null);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifier = $_POST['identifier'];
            $password   = $_POST['password'];

            $user = User::findByEmailOrEmployeeId($identifier);

            if ($user && password_verify($password, $user['password']) && $user['role'] === $role) {
                $_SESSION['user'] = $user;
                redirect('home');
            } else {
                $error = "Invalid credentials for this role.";
                require __DIR__ . '/../views/auth/login.php';
            }
        } else {
            require __DIR__ . '/../views/auth/login.php';
        }
    }

    public function register() {
        echo "Register page (not implemented yet)";
    }

    public function logout() {
        session_destroy();
        redirect('home');
    }
}
