document.addEventListener("DOMContentLoaded", () => {
  // Mendapatkan referensi ke elemen-elemen DOM
  const taskForm = document.getElementById("task-form");
  const taskInput = document.getElementById("task-input");
  const errorAlert = document.querySelector('[role="alert"]');

  // Menambahkan event listener untuk pengiriman formulir
  taskForm.addEventListener("submit", (e) => {
    // Pengiriman formulir ditangani oleh PHP, tapi kita bisa menambahkan validasi sisi klien
    if (taskInput.value.trim() === "") {
      e.preventDefault();
      alert("Silakan masukkan tugas!");
    }
  });

  // Fokus pada kolom input saat halaman dimuat
  taskInput.focus();

  // Menambahkan event listener untuk tombol delete untuk konfirmasi pada perangkat mobile
  const deleteButtons = document.querySelectorAll(".delete-btn");
  if (window.innerWidth < 640) {
    // Hanya untuk perangkat mobile
    deleteButtons.forEach((button) => {
      button.addEventListener("click", (e) => {
        // Mencegah klik yang tidak disengaja pada perangkat mobile
        if (!confirm("Apakah Anda yakin ingin menghapus tugas ini?")) {
          e.preventDefault();
        }
      });
    });
  }

  // Otomatis menghilangkan pesan error setelah 5 detik
  if (errorAlert) {
    setTimeout(() => {
      errorAlert.style.opacity = "0";
      errorAlert.style.transition = "opacity 1s";

      setTimeout(() => {
        errorAlert.remove();
      }, 1000);
    }, 5000);
  }
});
