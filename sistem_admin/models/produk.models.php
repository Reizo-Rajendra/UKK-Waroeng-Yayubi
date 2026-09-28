<?php
require_once __DIR__ . '/../database/koneksi.php';

class ProdukModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // fungsi tambah produk baru ke database
    public function createProduk($nama_produk, $kategori, $harga, $stok, $deskripsi, $gambar, $id_penjual = 1) {
        // query insert dengan prepared statement untuk cegah sql injection
        $stmt = $this->db->prepare("INSERT INTO produk (id_penjual, nama_produk, kategori, harga, stok, deskripsi, gambar) VALUES (?, ?, ?, ?, ?, ?, ?)");
        // bind parameter: i = integer, s = string, d = double/desimal
        $stmt->bind_param("issdiss", $id_penjual, $nama_produk, $kategori, $harga, $stok, $deskripsi, $gambar);
        return $stmt->execute();
    }

    // fungsi ambil semua data produk AKTIF saja (status=1, bisa dengan filter search dan kategori)
    public function getAllProduk($search = '', $kategori = '') {
        $searchParam = "%" . $search . "%";
        
        // kalau kategori dipilih, filter berdasarkan nama dan kategori (hanya status aktif)
        if (!empty($kategori)) {
            $stmt = $this->db->prepare("SELECT * FROM produk WHERE status = 1 AND (nama_produk LIKE ? OR deskripsi LIKE ?) AND kategori = ? ORDER BY id_produk DESC");
            $stmt->bind_param("sss", $searchParam, $searchParam, $kategori);
        } else {
            // kalau tidak ada filter kategori, cari dari nama atau deskripsi saja (hanya status aktif)
            $stmt = $this->db->prepare("SELECT * FROM produk WHERE status = 1 AND (nama_produk LIKE ? OR deskripsi LIKE ?) ORDER BY id_produk DESC");
            $stmt->bind_param("ss", $searchParam, $searchParam);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        // ubah hasil query jadi array asosiatif
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // fungsi ambil semua produk yang sudah di-nonaktifkan / soft delete (status=0)
    public function getProdukTerhapus() {
        $stmt = $this->db->prepare("SELECT * FROM produk WHERE status = 0 ORDER BY id_produk DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // fungsi ambil data 1 produk berdasarkan id_produk
    public function getProdukById($id_produk) {
        $stmt = $this->db->prepare("SELECT * FROM produk WHERE id_produk = ?");
        $stmt->bind_param("i", $id_produk);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // fungsi update data produk
    public function updateProduk($id_produk, $nama_produk, $kategori, $harga, $stok, $deskripsi, $gambar = null) {
        // jika gambar ikut diganti
        if ($gambar) {
            $stmt = $this->db->prepare("UPDATE produk SET nama_produk = ?, kategori = ?, harga = ?, stok = ?, deskripsi = ?, gambar = ? WHERE id_produk = ?");
            $stmt->bind_param("ssdissi", $nama_produk, $kategori, $harga, $stok, $deskripsi, $gambar, $id_produk);
        } else {
            // jika gambar tidak diganti, pakai gambar lama
            $stmt = $this->db->prepare("UPDATE produk SET nama_produk = ?, kategori = ?, harga = ?, stok = ?, deskripsi = ? WHERE id_produk = ?");
            $stmt->bind_param("ssdisi", $nama_produk, $kategori, $harga, $stok, $deskripsi, $id_produk);
        }
        return $stmt->execute();
    }

    // fungsi soft delete: nonaktifkan produk (status=0) — data & gambar tetap ada di database
    public function deleteProduk($id_produk) {
        // cukup set status = 0, data tidak benar-benar dihapus agar bisa di-restore
        $stmt = $this->db->prepare("UPDATE produk SET status = 0 WHERE id_produk = ?");
        $stmt->bind_param("i", $id_produk);
        return $stmt->execute();
    }

    // fungsi restore: aktifkan kembali produk yang sudah di-nonaktifkan (status=1)
    public function restoreProduk($id_produk) {
        $stmt = $this->db->prepare("UPDATE produk SET status = 1 WHERE id_produk = ?");
        $stmt->bind_param("i", $id_produk);
        return $stmt->execute();
    }

    // fungsi hapus permanen: benar-benar hapus data + file gambar dari server
    public function deleteProdukPermanent($id_produk) {
        // hapus juga file gambar fisiknya dari folder uploads kalau ada
        $produk = $this->getProdukById($id_produk);
        if ($produk && !empty($produk['gambar']) && $produk['gambar'] !== 'default.png') {
            $path = __DIR__ . '/../uploads/' . $produk['gambar'];
            if (file_exists($path)) {
                @unlink($path); // fungsi unlink untuk hapus file di server
            }
        }

        // query hapus data produk berdasarkan id
        $stmt = $this->db->prepare("DELETE FROM produk WHERE id_produk = ?");
        $stmt->bind_param("i", $id_produk);
        return $stmt->execute();
    }

    // STATISTIK DASHBOARD (Untuk KPI Cards Dropify style)
    public function getStatistik() {
        $stats = [
            'total_menu' => 0,
            'total_pesanan' => 0,
            'total_omset' => 0,
            'total_pelanggan' => 0,
            'pesanan_pending' => 0
        ];

        // Total Menu (hanya yang aktif / status=1)
        $res = $this->db->query("SELECT COUNT(*) AS total FROM produk WHERE status = 1");
        if ($row = $res->fetch_assoc()) $stats['total_menu'] = (int)$row['total'];

        // Total Pesanan
        $res = $this->db->query("SELECT COUNT(*) AS total FROM pesanan");
        if ($row = $res->fetch_assoc()) $stats['total_pesanan'] = (int)$row['total'];

        // Total Omset
        $res = $this->db->query("SELECT SUM(total_harga) AS total FROM pesanan WHERE status != 'Batal'");
        if ($row = $res->fetch_assoc()) $stats['total_omset'] = (float)($row['total'] ?? 0);

        // Total Pelanggan
        $res = $this->db->query("SELECT COUNT(*) AS total FROM pembeli");
        if ($row = $res->fetch_assoc()) $stats['total_pelanggan'] = (int)$row['total'];

        // Pesanan Pending
        $res = $this->db->query("SELECT COUNT(*) AS total FROM pesanan WHERE status = 'Pending'");
        if ($row = $res->fetch_assoc()) $stats['pesanan_pending'] = (int)$row['total'];

        return $stats;
    }

    // PESANAN: Ambil daftar pesanan pelanggan (lengkap dengan data pembayaran & pembeli)
    public function getAllPesanan($status = '') {
        $sql = "
            SELECT p.*, b.nama AS nama_pembeli, b.no_hp AS no_hp_pembeli, b.alamat AS alamat_pembeli, 
                   pr.nama_produk, pr.harga AS harga_satuan,
                   py.metode_bayar, py.status_pembayaran, py.tanggal_bayar
            FROM pesanan p
            LEFT JOIN pembeli b ON p.id_pembeli = b.id_pembeli
            LEFT JOIN produk pr ON p.id_produk = pr.id_produk
            LEFT JOIN pembayaran py ON p.id_pesanan = py.id_pesanan
        ";
        if (!empty($status)) {
            $sql .= " WHERE p.status = ? ORDER BY p.id_pesanan DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $status);
        } else {
            $sql .= " ORDER BY p.id_pesanan DESC";
            $stmt = $this->db->prepare($sql);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // PESANAN: Update status
    public function updateStatusPesanan($id_pesanan, $status) {
        $stmt = $this->db->prepare("UPDATE pesanan SET status = ? WHERE id_pesanan = ?");
        $stmt->bind_param("si", $status, $id_pesanan);
        return $stmt->execute();
    }

    // PELANGGAN: Ambil daftar pelanggan
    public function getAllPelanggan() {
        $res = $this->db->query("
            SELECT b.*, 
                   (SELECT COUNT(*) FROM pesanan WHERE id_pembeli = b.id_pembeli) AS jumlah_order,
                   (SELECT COALESCE(SUM(total_harga), 0) FROM pesanan WHERE id_pembeli = b.id_pembeli AND status != 'Batal') AS total_belanja
            FROM pembeli b
            ORDER BY b.id_pembeli DESC
        ");
        $data = [];
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
}
?>
