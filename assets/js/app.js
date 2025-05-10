document.addEventListener("DOMContentLoaded", () => {
  // Mendapatkan referensi ke elemen-elemen DOM
  const taskForm = document.getElementById("task-form");
  const taskInput = document.getElementById("task-input");
  const errorMessageContainer = document.getElementById("error-message");
  const taskList = document.getElementById("task-list");

  // Modal elements
  const editModal = document.getElementById("edit-modal");
  const editTaskInput = document.getElementById("edit-task-input");
  const editTaskId = document.getElementById("edit-task-id");
  const saveEditBtn = document.getElementById("save-edit-btn");
  const cancelEditBtn = document.getElementById("cancel-edit-btn");
  const modalError = document.getElementById("modal-error");

  // Fungsi untuk menampilkan pesan error
  const showError = (message, container = errorMessageContainer) => {
    container.querySelector("span").textContent = message;
    container.classList.remove("hidden");

    // Otomatis menghilangkan pesan error setelah 5 detik
    setTimeout(() => {
      container.style.opacity = "0";
      container.style.transition = "opacity 1s";

      setTimeout(() => {
        container.classList.add("hidden");
        container.style.opacity = "1";
      }, 1000);
    }, 5000);
  };

  // Fungsi untuk menampilkan modal
  const showModal = (taskId, taskText) => {
    // Set nilai input dan ID
    editTaskInput.value = taskText;
    editTaskId.value = taskId;

    // Tampilkan modal
    editModal.classList.remove("hidden");

    // Focus pada input
    setTimeout(() => {
      editTaskInput.focus();

      // Posisikan kursor di akhir teks
      const inputLength = editTaskInput.value.length;
      editTaskInput.setSelectionRange(inputLength, inputLength);
    }, 100);

    // Tambahkan event listener untuk menutup modal dengan Escape
    document.addEventListener("keydown", handleEscapeKey);
  };

  // Fungsi untuk menyembunyikan modal
  const hideModal = () => {
    // Tambahkan class closing untuk animasi
    editModal.classList.add("closing");
    editModal.querySelector(".modal-container").classList.add("closing");

    // Tunggu animasi selesai sebelum menyembunyikan modal
    setTimeout(() => {
      editModal.classList.add("hidden");
      editModal.classList.remove("closing");
      editModal.querySelector(".modal-container").classList.remove("closing");

      // Reset nilai
      editTaskInput.value = "";
      editTaskId.value = "";
      modalError.classList.add("hidden");
    }, 300);

    // Hapus event listener
    document.removeEventListener("keydown", handleEscapeKey);
  };

  // Handler untuk tombol Escape
  const handleEscapeKey = (e) => {
    if (e.key === "Escape") {
      hideModal();
    }
  };

  // Event listener untuk tombol cancel pada modal
  cancelEditBtn.addEventListener("click", hideModal);

  // Event listener untuk klik di luar modal untuk menutupnya
  editModal.addEventListener("click", (e) => {
    if (e.target === editModal) {
      hideModal();
    }
  });

  // Event listener untuk tombol save pada modal
  saveEditBtn.addEventListener("click", () => {
    const taskId = editTaskId.value;
    const newText = editTaskInput.value.trim();

    if (newText === "") {
      showError("Tugas tidak boleh kosong!", modalError);
      return;
    }

    // Gunakan fungsi editTaskById
    window
      .editTaskById(taskId, newText)
      .then(() => {
        hideModal();
      })
      .catch((error) => {
        console.error("Error:", error);
        showError(error, modalError);
      });
  });

  // Handle Enter key in edit input
  editTaskInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      saveEditBtn.click();
    }
  });

  // Fungsi untuk mengedit tugas berdasarkan ID
  window.editTaskById = (taskId, newText) => {
    if (!taskId || !newText || newText.trim() === "") {
      showError("ID tugas dan teks baru diperlukan");
      return Promise.reject("ID tugas dan teks baru diperlukan");
    }

    return fetch("index.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: new URLSearchParams({
        action: "edit",
        id: taskId,
        task: newText.trim(),
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Update teks tugas di DOM jika elemen ada
          const taskItem = document.querySelector(
            `.task-item[data-id="${taskId}"]`,
          );
          if (taskItem) {
            const taskLabel = taskItem.querySelector(".task-label");
            taskLabel.textContent = newText.trim();

            // Update data-text attribute pada tombol edit
            const editBtn = taskItem.querySelector(".edit-btn");
            if (editBtn) {
              editBtn.setAttribute("data-text", newText.trim());
            }
          }
          return data;
        } else {
          showError(data.message);
          return Promise.reject(data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showError("Terjadi kesalahan saat mengedit tugas");
        return Promise.reject(error);
      });
  };

  // Fungsi untuk mendapatkan semua tugas
  window.getAllTasks = () => {
    return fetch("index.php?action=get_tasks", {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          return data.tasks;
        } else {
          showError(data.message);
          return Promise.reject(data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showError("Terjadi kesalahan saat mengambil tugas");
        return Promise.reject(error);
      });
  };

  // Fungsi untuk mendapatkan tugas berdasarkan ID
  window.getTaskById = (taskId) => {
    if (!taskId) {
      showError("ID tugas diperlukan");
      return Promise.reject("ID tugas diperlukan");
    }

    return fetch(`index.php?action=get_task&id=${taskId}`, {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          return data.task;
        } else {
          showError(data.message);
          return Promise.reject(data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showError("Terjadi kesalahan saat mengambil tugas");
        return Promise.reject(error);
      });
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

  // Menangani fitur edit tugas dengan modal
  document.querySelectorAll(".edit-btn").forEach((editBtn) => {
    editBtn.addEventListener("click", () => {
      const taskId = editBtn.dataset.id;
      const taskText = editBtn.dataset.text;

      // Tampilkan modal edit
      showModal(taskId, taskText);
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
