document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const successMsg = document.getElementById('successMsg');

    /**
     * Fungsi untuk memvalidasi input
     * @param {string} id - ID elemen input
     * @param {boolean} condition - Kondisi validasi
     */
    function validate(id, condition) {
      const field = document.getElementById('field-' + id);
      if (!condition) {
        field.classList.add('error');
        return false;
      }
      field.classList.remove('error');
      return true;
    }

    // Menangani pengiriman formulir
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const nama = document.getElementById('nama').value.trim();
      const email = document.getElementById('email').value.trim();
      const pesan = document.getElementById('pesan').value.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      // Jalankan validasi
      const v1 = validate('nama', nama.length > 0);
      const v2 = validate('email', emailRegex.test(email));
      const v3 = validate('pesan', pesan.length > 0);

      // Jika semua valid, tampilkan pesan sukses
      if (v1 && v2 && v3) {
        form.style.display = 'none';
        successMsg.style.display = 'block';
      }
    });

    // Menghapus status error saat pengguna mulai mengetik ulang
    ['nama', 'email', 'pesan'].forEach(function(id) {
      const el = document.getElementById(id);
      el.addEventListener('input', function() {
        document.getElementById('field-' + id).classList.remove('error');
      });
    });
});