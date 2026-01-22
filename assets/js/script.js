/**
 * =============================================
 * CUSTOM JAVASCRIPT - APLIKASI PARKIR
 * =============================================
 */

// Auto dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    // Auto close alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Confirm delete
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                e.preventDefault();
            }
        });
    });
});

/**
 * Format input sebagai rupiah
 * @param {HTMLInputElement} input 
 */
function formatRupiahInput(input) {
    let value = input.value.replace(/\D/g, '');
    value = new Intl.NumberFormat('id-ID').format(value);
    input.value = value;
}

/**
 * Print struk parkir
 */
function printStruk() {
    window.print();
}

/**
 * Hitung biaya parkir otomatis
 */
function hitungBiaya() {
    const waktuMasuk = document.getElementById('waktu_masuk').value;
    const waktuKeluar = document.getElementById('waktu_keluar').value;
    const tarifPerJam = parseInt(document.getElementById('tarif_per_jam').value) || 0;
    
    if (waktuMasuk && waktuKeluar && tarifPerJam) {
        const masuk = new Date(waktuMasuk);
        const keluar = new Date(waktuKeluar);
        const diffMs = keluar - masuk;
        const diffJam = Math.ceil(diffMs / (1000 * 60 * 60));
        
        const durasi = Math.max(1, diffJam);
        const biaya = durasi * tarifPerJam;
        
        document.getElementById('durasi_jam').value = durasi;
        document.getElementById('biaya_total').value = biaya;
        document.getElementById('biaya_display').textContent = 
            'Rp ' + new Intl.NumberFormat('id-ID').format(biaya);
    }
}
