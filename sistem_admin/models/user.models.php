<?php
require_once __DIR__ . '/../database/koneksi.php';

class UserModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Cek apakah email sudah terdaftar di pembeli atau penjual
    public function emailExists($email, $excludeId = 0, $excludeTable = '') {
        // Cek di pembeli
        if ($excludeTable === 'pembeli' && $excludeId > 0) {
            $stmt = $this->db->prepare("SELECT id_pembeli FROM pembeli WHERE email = ? AND id_pembeli != ?");
            $stmt->bind_param("si", $email, $excludeId);
        } else {
            $stmt = $this->db->prepare("SELECT id_pembeli FROM pembeli WHERE email = ?");
            $stmt->bind_param("s", $email);
        }
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            return true;
        }

        // Cek di penjual (admin)
        if ($excludeTable === 'penjual' && $excludeId > 0) {
            $stmt = $this->db->prepare("SELECT id_penjual FROM penjual WHERE email = ? AND id_penjual != ?");
            $stmt->bind_param("si", $email, $excludeId);
        } else {
            $stmt = $this->db->prepare("SELECT id_penjual FROM penjual WHERE email = ?");
            $stmt->bind_param("s", $email);
        }
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // registrasi akun pembeli baru (password di-hash agar aman di database)
    public function register($nama, $email, $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO pembeli (nama, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama, $email, $hashed_password);
        return $stmt->execute();
    }

    // cari user berdasarkan email untuk proses login
    public function getUserByEmail($email) {
        // cek dulu di tabel penjual (sebagai admin)
        $stmt = $this->db->prepare("SELECT id_penjual AS id, nama, email, password, no_hp, alamat, 'admin' AS role FROM penjual WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            return $user;
        }

        // kalau tidak ditemukan di admin, cek di tabel pembeli
        $stmt = $this->db->prepare("SELECT id_pembeli AS id, nama, email, password, no_hp, alamat, 'pembeli' AS role FROM pembeli WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            return $user;
        }

        return null;
    }

    // READ: Ambil semua data akun user (Admin & Pembeli) dengan pencarian & filter role
    public function getAllUsers($search = '', $roleFilter = '') {
        $search = trim($search);
        $searchParam = "%" . $search . "%";
        $users = [];

        // 1. Ambil Akun Admin / Penjual
        if (empty($roleFilter) || $roleFilter === 'admin' || $roleFilter === 'penjual') {
            $sqlPenjual = "
                SELECT id_penjual AS id, nama, email, no_hp, alamat, created_at, 'admin' AS role,
                       0 AS jumlah_order, 0 AS total_belanja
                FROM penjual
            ";
            if (!empty($search)) {
                $sqlPenjual .= " WHERE nama LIKE ? OR email LIKE ? OR no_hp LIKE ?";
                $stmt = $this->db->prepare($sqlPenjual);
                $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
            } else {
                $stmt = $this->db->prepare($sqlPenjual);
            }
            if ($stmt) {
                $stmt->execute();
                $res = $stmt->get_result();
                while ($r = $res->fetch_assoc()) {
                    $users[] = $r;
                }
            }
        }

        // 2. Ambil Akun Pembeli (Pelanggan)
        if (empty($roleFilter) || $roleFilter === 'pembeli') {
            $sqlPembeli = "
                SELECT b.id_pembeli AS id, b.nama, b.email, b.no_hp, b.alamat, b.created_at, 'pembeli' AS role,
                       (SELECT COUNT(*) FROM pesanan WHERE id_pembeli = b.id_pembeli) AS jumlah_order,
                       (SELECT COALESCE(SUM(total_harga), 0) FROM pesanan WHERE id_pembeli = b.id_pembeli AND status != 'Batal') AS total_belanja
                FROM pembeli b
            ";
            if (!empty($search)) {
                $sqlPembeli .= " WHERE b.nama LIKE ? OR b.email LIKE ? OR b.no_hp LIKE ?";
                $stmt = $this->db->prepare($sqlPembeli);
                $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
            } else {
                $stmt = $this->db->prepare($sqlPembeli);
            }
            if ($stmt) {
                $stmt->execute();
                $res = $stmt->get_result();
                while ($r = $res->fetch_assoc()) {
                    $users[] = $r;
                }
            }
        }

        // Urutkan berdasarkan tanggal terbaru
        usort($users, function($a, $b) {
            $timeA = strtotime($a['created_at'] ?? '2026-01-01');
            $timeB = strtotime($b['created_at'] ?? '2026-01-01');
            return $timeB - $timeA;
        });

        return $users;
    }

    // CREATE: Tambah akun user baru (Admin atau Pembeli)
    public function createUser($nama, $email, $password, $no_hp = '', $alamat = '', $role = 'pembeli') {
        if ($this->emailExists($email)) {
            return ['status' => false, 'message' => 'Email ' . htmlspecialchars($email) . ' sudah terdaftar!'];
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = ($role === 'admin' || $role === 'penjual') ? 'admin' : 'pembeli';

        if ($role === 'admin') {
            $stmt = $this->db->prepare("INSERT INTO penjual (nama, email, password, no_hp, alamat) VALUES (?, ?, ?, ?, ?)");
        } else {
            $stmt = $this->db->prepare("INSERT INTO pembeli (nama, email, password, no_hp, alamat) VALUES (?, ?, ?, ?, ?)");
        }

        if ($stmt) {
            $stmt->bind_param("sssss", $nama, $email, $hashed_password, $no_hp, $alamat);
            if ($stmt->execute()) {
                return ['status' => true, 'message' => 'Akun ' . ($role === 'admin' ? 'Admin' : 'Pembeli') . ' berhasil ditambahkan!'];
            }
        }

        return ['status' => false, 'message' => 'Gagal menambahkan akun ke database.'];
    }

    // UPDATE: Perbarui data akun user
    public function updateUser($id, $nama, $email, $password = null, $no_hp = '', $alamat = '', $currentRole = 'pembeli', $newRole = 'pembeli') {
        $id = (int)$id;
        $currentRole = ($currentRole === 'admin' || $currentRole === 'penjual') ? 'admin' : 'pembeli';
        $newRole = ($newRole === 'admin' || $newRole === 'penjual') ? 'admin' : 'pembeli';
        $currentTable = ($currentRole === 'admin') ? 'penjual' : 'pembeli';

        // Cek jika email dipakai akun lain
        if ($this->emailExists($email, $id, $currentTable)) {
            return ['status' => false, 'message' => 'Email ' . htmlspecialchars($email) . ' sudah dipakai akun lain!'];
        }

        // Skenario 1: Role sama (hanya update fields)
        if ($currentRole === $newRole) {
            $idCol = ($currentRole === 'admin') ? 'id_penjual' : 'id_pembeli';

            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("UPDATE $currentTable SET nama = ?, email = ?, password = ?, no_hp = ?, alamat = ? WHERE $idCol = ?");
                $stmt->bind_param("sssssi", $nama, $email, $hashed, $no_hp, $alamat, $id);
            } else {
                $stmt = $this->db->prepare("UPDATE $currentTable SET nama = ?, email = ?, no_hp = ?, alamat = ? WHERE $idCol = ?");
                $stmt->bind_param("ssssi", $nama, $email, $no_hp, $alamat, $id);
            }

            if ($stmt && $stmt->execute()) {
                return ['status' => true, 'message' => 'Data akun berhasil diperbarui!'];
            }
        } else {
            // Skenario 2: Role berubah (pindahkan akun antar tabel)
            $oldTable = ($currentRole === 'admin') ? 'penjual' : 'pembeli';
            $oldIdCol = ($currentRole === 'admin') ? 'id_penjual' : 'id_pembeli';
            $newTable = ($newRole === 'admin') ? 'penjual' : 'pembeli';

            // Ambil password lama jika tidak diubah
            $stmtOld = $this->db->prepare("SELECT password FROM $oldTable WHERE $oldIdCol = ?");
            $stmtOld->bind_param("i", $id);
            $stmtOld->execute();
            $oldUser = $stmtOld->get_result()->fetch_assoc();
            $finalPass = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : ($oldUser['password'] ?? password_hash('123456', PASSWORD_DEFAULT));

            // Insert ke tabel baru
            $stmtNew = $this->db->prepare("INSERT INTO $newTable (nama, email, password, no_hp, alamat) VALUES (?, ?, ?, ?, ?)");
            $stmtNew->bind_param("sssss", $nama, $email, $finalPass, $no_hp, $alamat);
            if ($stmtNew->execute()) {
                // Hapus dari tabel lama
                $stmtDel = $this->db->prepare("DELETE FROM $oldTable WHERE $oldIdCol = ?");
                $stmtDel->bind_param("i", $id);
                $stmtDel->execute();
                return ['status' => true, 'message' => 'Data dan role akun berhasil diubah menjadi ' . ucfirst($newRole) . '!'];
            }
        }

        return ['status' => false, 'message' => 'Gagal memperbarui data akun.'];
    }

    // DELETE: Hapus akun user
    public function deleteUser($id, $role, $currentAdminId, $currentAdminRole) {
        $id = (int)$id;
        $role = ($role === 'admin' || $role === 'penjual') ? 'admin' : 'pembeli';

        // Cegah admin menghapus akun miliknya sendiri yang sedang login
        if ($role === 'admin' && $id === (int)$currentAdminId) {
            return ['status' => false, 'message' => 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login!'];
        }

        if ($role === 'admin') {
            $stmt = $this->db->prepare("DELETE FROM penjual WHERE id_penjual = ?");
        } else {
            $stmt = $this->db->prepare("DELETE FROM pembeli WHERE id_pembeli = ?");
        }

        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                return ['status' => true, 'message' => 'Akun pengguna berhasil dihapus!'];
            }
        }

        return ['status' => false, 'message' => 'Gagal menghapus akun pengguna.'];
    }
}
?>
