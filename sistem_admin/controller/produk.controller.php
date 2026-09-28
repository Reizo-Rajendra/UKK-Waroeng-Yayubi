<?php
require_once __DIR__ . '/../models/produk.models.php';

class ProdukController {
    private $produkModel;

    public function __construct($db) {
        $this->produkModel = new ProdukModel($db);
    }

    public function getModel() {
        return $this->produkModel;
    }

    // Handle Upload File Gambar
    private function uploadGambar($file) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return 'default.png';
        }

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedExt)) {
            return false; // format tidak didukung
        }

        // Batasi ukuran maksimal 5MB
        if ($fileSize > 5 * 1024 * 1024) {
            return false;
        }

        $newFileName = 'menu_' . time() . '_' . rand(100, 999) . '.' . $fileExt;
        $targetDir = __DIR__ . '/../uploads/';

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (move_uploaded_file($fileTmp, $targetDir . $newFileName)) {
            return $newFileName;
        }

        return 'default.png';
    }

    // CREATE Produk
    public function handleCreate($post, $files, $id_penjual = 1) {
        $nama = trim($post['nama_produk'] ?? '');
        $kategori = trim($post['kategori'] ?? 'Nasi Box');
        $harga = floatval($post['harga'] ?? 0);
        $stok = intval($post['stok'] ?? 0);
        $deskripsi = trim($post['deskripsi'] ?? '');

        if (empty($nama) || $harga <= 0) {
            return ['status' => false, 'message' => 'Nama menu dan harga valid wajib diisi!'];
        }

        $gambar = 'default.png';
        if (isset($files['gambar']) && $files['gambar']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->uploadGambar($files['gambar']);
            if ($uploaded === false) {
                return ['status' => false, 'message' => 'Format gambar harus JPG/PNG/WEBP dan maksimal 5MB!'];
            }
            $gambar = $uploaded;
        }

        $created = $this->produkModel->createProduk($nama, $kategori, $harga, $stok, $deskripsi, $gambar, $id_penjual);
        if ($created) {
            return ['status' => true, 'message' => 'Menu catering baru berhasil ditambahkan!'];
        } else {
            return ['status' => false, 'message' => 'Gagal menambahkan menu ke database.'];
        }
    }

    // UPDATE Produk
    public function handleUpdate($post, $files) {
        $id = intval($post['id_produk'] ?? 0);
        $nama = trim($post['nama_produk'] ?? '');
        $kategori = trim($post['kategori'] ?? 'Nasi Box');
        $harga = floatval($post['harga'] ?? 0);
        $stok = intval($post['stok'] ?? 0);
        $deskripsi = trim($post['deskripsi'] ?? '');

        if ($id <= 0 || empty($nama) || $harga <= 0) {
            return ['status' => false, 'message' => 'Data menu tidak valid!'];
        }

        $gambar = null;
        if (isset($files['gambar']) && $files['gambar']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->uploadGambar($files['gambar']);
            if ($uploaded === false) {
                return ['status' => false, 'message' => 'Format gambar harus JPG/PNG/WEBP dan maksimal 5MB!'];
            }
            $gambar = $uploaded;
        }

        $updated = $this->produkModel->updateProduk($id, $nama, $kategori, $harga, $stok, $deskripsi, $gambar);
        if ($updated) {
            return ['status' => true, 'message' => 'Data menu catering berhasil diperbarui!'];
        } else {
            return ['status' => false, 'message' => 'Gagal memperbarui menu.'];
        }
    }

    // SOFT DELETE Produk (ubah status=0, data tetap ada)
    public function handleDelete($id) {
        $id = intval($id);
        if ($id <= 0) {
            return ['status' => false, 'message' => 'ID menu tidak valid!'];
        }

        $deleted = $this->produkModel->deleteProduk($id);
        if ($deleted) {
            return ['status' => true, 'message' => 'Menu berhasil dinonaktifkan (dapat di-restore kapan saja)!'];
        } else {
            return ['status' => false, 'message' => 'Gagal menonaktifkan menu.'];
        }
    }

    // RESTORE Produk (aktifkan kembali, status=1)
    public function handleRestore($id) {
        $id = intval($id);
        if ($id <= 0) {
            return ['status' => false, 'message' => 'ID menu tidak valid!'];
        }

        $restored = $this->produkModel->restoreProduk($id);
        if ($restored) {
            return ['status' => true, 'message' => 'Menu catering berhasil diaktifkan kembali!'];
        } else {
            return ['status' => false, 'message' => 'Gagal mengaktifkan kembali menu.'];
        }
    }

    // DELETE PERMANEN Produk (hapus benar-benar dari database + file gambar)
    public function handleDeletePermanent($id) {
        $id = intval($id);
        if ($id <= 0) {
            return ['status' => false, 'message' => 'ID menu tidak valid!'];
        }

        $deleted = $this->produkModel->deleteProdukPermanent($id);
        if ($deleted) {
            return ['status' => true, 'message' => 'Menu catering berhasil dihapus permanen!'];
        } else {
            return ['status' => false, 'message' => 'Gagal menghapus menu secara permanen.'];
        }
    }

    // UPDATE Status Pesanan
    public function handleUpdateStatusPesanan($id_pesanan, $status) {
        $id_pesanan = intval($id_pesanan);
        $allowed = ['Pending', 'Dibayar', 'Diproses', 'Selesai', 'Batal'];
        if (!in_array($status, $allowed)) {
            return ['status' => false, 'message' => 'Status pesanan tidak valid!'];
        }

        $updated = $this->produkModel->updateStatusPesanan($id_pesanan, $status);
        if ($updated) {
            return ['status' => true, 'message' => 'Status pesanan #' . $id_pesanan . ' berhasil diubah menjadi ' . $status . '!'];
        } else {
            return ['status' => false, 'message' => 'Gagal mengubah status pesanan.'];
        }
    }
}
?>
