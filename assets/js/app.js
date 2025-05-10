document.addEventListener("DOMContentLoaded", () => {
  // Mendapatkan referensi ke elemen-elemen DOM
  const taskForm = document.getElementById("task-form");
  const taskInput = document.getElementById("task-input");
  const errorMessageContainer = document.getElementById("error-message");
  const taskList = document.getElementById("task-list");
  const taskItems = document.querySelectorAll(".task-item");

  // Fungsi untuk menampilkan pesan error
  const showError = (message) => {
    errorMessageContainer.textContent = message;
    errorMessageContainer.classList.remove("hidden");

    // Otomatis menghilangkan pesan error setelah 5 detik
    setTimeout(() => {
      errorMessageContainer.style.opacity = "0";
      errorMessageContainer.style.transition = "opacity 1s";

      setTimeout(() => {
        errorMessageContainer.classList.add("hidden");
        errorMessageContainer.style.opacity = "1";
      }, 1000);
    }, 5000);
  };

  // Menambahkan event listener untuk pengiriman formulir
  taskForm.addEventListener("submit", (e) => {
    e.preventDefault();

    if (taskInput.value.trim() === "") {
      showError("Silakan masukkan tugas!");
      return;
    }

    // Kirim data menggunakan fetch API
    fetch("index.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: new URLSearchParams({
        action: "add",
        task: taskInput.value,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Refresh halaman untuk menampilkan tugas baru
          // Dalam implementasi yang lebih baik, kita bisa menambahkan tugas baru ke DOM tanpa refresh
          window.location.reload();
        } else {
          showError(data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showError("Terjadi kesalahan saat menambahkan tugas");
      });
  });

  // Fokus pada kolom input saat halaman dimuat
  taskInput.focus();

  // Menangani fitur edit tugas
  document.querySelectorAll(".edit-btn").forEach((editBtn) => {
    editBtn.addEventListener("click", () => {
      const taskId = editBtn.dataset.id;
      const taskItem = document.querySelector(
        `.task-item[data-id="${taskId}"]`,
      );
      const taskView = taskItem.querySelector(".task-view");
      const taskEdit = taskItem.querySelector(".task-edit");
      const editInput = taskItem.querySelector(".edit-input");

      // Sembunyikan semua form edit lainnya
      document.querySelectorAll(".task-edit").forEach((edit) => {
        if (edit !== taskEdit) {
          edit.classList.add("hidden");
        }
      });
      document.querySelectorAll(".task-view").forEach((view) => {
        if (view !== taskView) {
          view.classList.remove("hidden");
        }
      });

      // Tampilkan form edit untuk tugas ini
      taskView.classList.add("hidden");
      taskEdit.classList.remove("hidden");
      editInput.focus();

      // Posisikan kursor di akhir teks
      const inputLength = editInput.value.length;
      editInput.setSelectionRange(inputLength, inputLength);
    });
  });

  // Menangani tombol cancel pada form edit
  document.querySelectorAll(".cancel-btn").forEach((cancelBtn) => {
    cancelBtn.addEventListener("click", () => {
      const taskItem = cancelBtn.closest(".task-item");
      const taskView = taskItem.querySelector(".task-view");
      const taskEdit = taskItem.querySelector(".task-edit");

      taskView.classList.remove("hidden");
      taskEdit.classList.add("hidden");
    });
  });

  // Menangani tombol save pada form edit
  document.querySelectorAll(".save-btn").forEach((saveBtn) => {
    saveBtn.addEventListener("click", () => {
      const taskId = saveBtn.dataset.id;
      const taskItem = document.querySelector(
        `.task-item[data-id="${taskId}"]`,
      );
      const editInput = taskItem.querySelector(".edit-input");
      const taskLabel = taskItem.querySelector(".task-label");
      const taskView = taskItem.querySelector(".task-view");
      const taskEdit = taskItem.querySelector(".task-edit");

      const newText = editInput.value.trim();

      if (newText === "") {
        showError("Tugas tidak boleh kosong!");
        return;
      }

      // Kirim data menggunakan fetch API
      fetch("index.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: new URLSearchParams({
          action: "edit",
          id: taskId,
          task: newText,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Update teks tugas di DOM
            taskLabel.textContent = newText;

            // Sembunyikan form edit
            taskView.classList.remove("hidden");
            taskEdit.classList.add("hidden");
          } else {
            showError(data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showError("Terjadi kesalahan saat mengedit tugas");
        });
    });
  });

  // Menangani checkbox untuk toggle status tugas
  document.querySelectorAll(".task-checkbox").forEach((checkbox) => {
    checkbox.addEventListener("change", () => {
      const taskId = checkbox.dataset.id;
      const taskItem = document.querySelector(
        `.task-item[data-id="${taskId}"]`,
      );
      const taskLabel = taskItem.querySelector(".task-label");

      // Kirim data menggunakan fetch API
      fetch(`index.php?action=toggle&id=${taskId}`, {
        method: "GET",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Toggle class untuk styling
            taskLabel.classList.toggle("line-through");
            taskLabel.classList.toggle("text-gray-500");
          } else {
            showError(data.message);
            // Kembalikan status checkbox jika gagal
            checkbox.checked = !checkbox.checked;
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showError("Terjadi kesalahan saat mengubah status tugas");
          // Kembalikan status checkbox jika gagal
          checkbox.checked = !checkbox.checked;
        });
    });
  });

  // Menangani tombol delete
  document.querySelectorAll(".delete-btn").forEach((deleteBtn) => {
    deleteBtn.addEventListener("click", () => {
      const taskId = deleteBtn.dataset.id;

      // Konfirmasi penghapusan pada perangkat mobile atau jika diperlukan
      if (
        window.innerWidth < 640 &&
        !confirm("Apakah Anda yakin ingin menghapus tugas ini?")
      ) {
        return;
      }

      // Kirim data menggunakan fetch API
      fetch(`index.php?action=delete&id=${taskId}`, {
        method: "GET",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Hapus elemen tugas dari DOM
            const taskItem = document.querySelector(
              `.task-item[data-id="${taskId}"]`,
            );
            taskItem.remove();

            // Jika tidak ada tugas lagi, tampilkan pesan
            if (data.tasks.length === 0) {
              const emptyMessage = document.createElement("li");
              emptyMessage.className =
                "text-center py-4 sm:py-6 px-3 sm:px-4 bg-gray-50 text-gray-500 italic rounded-md";
              emptyMessage.textContent =
                "Belum ada tugas yang terbuat, tambahkan sekarang!";
              taskList.appendChild(emptyMessage);
            }
          } else {
            showError(data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showError("Terjadi kesalahan saat menghapus tugas");
        });
    });
  });

  // Otomatis menghilangkan pesan error yang ada saat halaman dimuat
  if (!errorMessageContainer.classList.contains("hidden")) {
    setTimeout(() => {
      errorMessageContainer.style.opacity = "0";
      errorMessageContainer.style.transition = "opacity 1s";

      setTimeout(() => {
        errorMessageContainer.classList.add("hidden");
        errorMessageContainer.style.opacity = "1";
      }, 1000);
    }, 5000);
  }
});
