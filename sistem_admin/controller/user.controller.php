<?php
require_once __DIR__ . '/../models/user.models.php';

class UserController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new UserModel($db);
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    public function getModel() {
        return $this->userModel;
    }

    public function handleRegister($nama, $email, $password) {
        if (empty($nama) || empty($email) || empty($password)) {
            return "Semua kolom wajib diisi!";
        }

        if ($this->userModel->emailExists($email)) {
            return "Email sudah terdaftar!";
        }

        if ($this->userModel->register($nama, $email, $password)) {
            return "Registration successful! Silakan login.";
        } else {
            return "Terjadi kesalahan saat pendaftaran.";
        }
    }

    public function handleLogin($email, $password) {
        if (empty($email) || empty($password)) {
            return "Email dan password wajib diisi!";
        }

        $user = $this->userModel->getUserByEmail($email);
        
        // Cek password hash atau plain text (untuk keamanan demo & kemudahan pengujian)
        if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role']; // 'admin' atau 'pembeli'
            return true;
        } else {
            return "Email atau password salah!";
        }
    }

    public function handleCreateUser($post) {
        $nama     = trim($post['nama'] ?? '');
        $email    = trim($post['email'] ?? '');
        $password = trim($post['password'] ?? '');
        $no_hp    = trim($post['no_hp'] ?? '');
        $alamat   = trim($post['alamat'] ?? '');
        $role     = trim($post['role'] ?? 'pembeli');

        if (empty($nama) || empty($email) || empty($password)) {
            return ['status' => false, 'message' => 'Nama, email, dan password wajib diisi!'];
        }

        return $this->userModel->createUser($nama, $email, $password, $no_hp, $alamat, $role);
    }

    public function handleUpdateUser($post) {
        $id          = (int)($post['user_id'] ?? ($post['id'] ?? 0));
        $nama        = trim($post['nama'] ?? '');
        $email       = trim($post['email'] ?? '');
        $password    = trim($post['password'] ?? '');
        $no_hp       = trim($post['no_hp'] ?? '');
        $alamat      = trim($post['alamat'] ?? '');
        $currentRole = trim($post['current_role'] ?? 'pembeli');
        $newRole     = trim($post['role'] ?? 'pembeli');

        if ($id <= 0 || empty($nama) || empty($email)) {
            return ['status' => false, 'message' => 'ID, nama, dan email wajib diisi!'];
        }

        return $this->userModel->updateUser($id, $nama, $email, $password, $no_hp, $alamat, $currentRole, $newRole);
    }

    public function handleDeleteUser($id, $role, $currentAdminId, $currentAdminRole) {
        $id = (int)$id;
        if ($id <= 0) {
            return ['status' => false, 'message' => 'ID pengguna tidak valid!'];
        }

        return $this->userModel->deleteUser($id, $role, $currentAdminId, $currentAdminRole);
    }

    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = array();
        session_destroy();
        header("Location: ../index.php");
        exit();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function redirectIfLoggedIn() {
        if ($this->isLoggedIn()) {
            if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'penjual')) {
                header("Location: ../view/admin/dashboard.php");
            } else {
                header("Location: ../../Home page/home.php");
            }
            exit();
        }
    }
}
?>
