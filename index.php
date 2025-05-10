<?php
// Memulai sesi untuk menyimpan tugas-tugas
session_start();

// Inisialisasi array tugas jika belum ada
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

// Inisialisasi variabel pesan error
$errorMessage = '';

// Cek apakah request adalah AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Menangani pengiriman formulir untuk menambahkan tugas
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!empty($_POST['task'])) {
        $newTaskText = trim($_POST['task']);
        
        // Cek apakah tugas dengan teks yang sama sudah ada
        $isDuplicate = false;
        foreach ($_SESSION['tasks'] as $task) {
            if (strtolower($task['text']) === strtolower($newTaskText)) {
                $isDuplicate = true;
                break;
            }
        }
        
        if ($isDuplicate) {
            // Set pesan error jika tugas duplikat
            $_SESSION['error_message'] = "Tugas dengan teks yang sama sudah ada!";
        } else {
            // Tambahkan tugas baru jika bukan duplikat
            $newTask = [
                'id' => uniqid(),
                'text' => $newTaskText,
                'completed' => false
            ];
            $_SESSION['tasks'][] = $newTask;
        }
    }
    
    if ($isAjax) {
        // Jika AJAX request, kirim response JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => !$isDuplicate,
            'message' => $isDuplicate ? "Tugas dengan teks yang sama sudah ada!" : "Tugas berhasil ditambahkan",
            'tasks' => $_SESSION['tasks']
        ]);
        exit;
    } else {
        // Pengalihan untuk mencegah pengiriman ulang formulir
        header('Location: index.php');
        exit;
    }
}

// Menangani penghapusan tugas
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $taskId = $_GET['id'];
    $success = false;
    
    foreach ($_SESSION['tasks'] as $key => $task) {
        if ($task['id'] === $taskId) {
            unset($_SESSION['tasks'][$key]);
            $success = true;
            break;
        }
    }
    
    // Mengindeks ulang array
    $_SESSION['tasks'] = array_values($_SESSION['tasks']);
    
    if ($isAjax) {
        // Jika AJAX request, kirim response JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? "Tugas berhasil dihapus" : "Tugas tidak ditemukan",
            'tasks' => $_SESSION['tasks']
        ]);
        exit;
    } else {
        // Pengalihan untuk mencegah pengiriman ulang formulir
        header('Location: index.php');
        exit;
    }
}

// Menangani pengalihan status penyelesaian tugas
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $taskId = $_GET['id'];
    $success = false;
    
    foreach ($_SESSION['tasks'] as $key => $task) {
        if ($task['id'] === $taskId) {
            // Toggle status completed
            $_SESSION['tasks'][$key]['completed'] = !$_SESSION['tasks'][$key]['completed'];
            $success = true;
            break;
        }
    }
    
    if ($isAjax) {
        // Jika AJAX request, kirim response JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? "Status tugas berhasil diubah" : "Tugas tidak ditemukan",
            'tasks' => $_SESSION['tasks']
        ]);
        exit;
    } else {
        // Pengalihan untuk mencegah pengiriman ulang formulir
        header('Location: index.php');
        exit;
    }
}

// Menangani pengeditan tugas via AJAX
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $response = ['success' => false, 'message' => 'Invalid request'];
    
    if (!empty($_POST['task']) && isset($_POST['id'])) {
        $editedTaskText = trim($_POST['task']);
        $taskId = $_POST['id'];
        
        // Cek apakah tugas dengan teks yang sama sudah ada (kecuali tugas yang sedang diedit)
        $isDuplicate = false;
        foreach ($_SESSION['tasks'] as $task) {
            if ($task['id'] !== $taskId && strtolower($task['text']) === strtolower($editedTaskText)) {
                $isDuplicate = true;
                break;
            }
        }
        
        if ($isDuplicate) {
            $response = [
                'success' => false,
                'message' => "Tugas dengan teks yang sama sudah ada!"
            ];
        } else {
            // Update tugas jika bukan duplikat
            $updated = false;
            foreach ($_SESSION['tasks'] as $key => $task) {
                if ($task['id'] === $taskId) {
                    $_SESSION['tasks'][$key]['text'] = $editedTaskText;
                    $updated = true;
                    break;
                }
            }
            
            if ($updated) {
                $response = [
                    'success' => true,
                    'message' => "Tugas berhasil diperbarui",
                    'taskId' => $taskId,
                    'newText' => $editedTaskText
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => "Tugas tidak ditemukan"
                ];
            }
        }
    }
    
    // Kirim response JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Menangani permintaan untuk mendapatkan semua tugas
if (isset($_GET['action']) && $_GET['action'] === 'get_tasks') {
    $response = [
        'success' => true,
        'message' => 'Tugas berhasil diambil',
        'tasks' => $_SESSION['tasks']
    ];
    
    // Kirim response JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Menangani permintaan untuk mendapatkan tugas berdasarkan ID
if (isset($_GET['action']) && $_GET['action'] === 'get_task' && isset($_GET['id'])) {
    $taskId = $_GET['id'];
    $taskFound = false;
    $foundTask = null;
    
    foreach ($_SESSION['tasks'] as $task) {
        if ($task['id'] === $taskId) {
            $taskFound = true;
            $foundTask = $task;
            break;
        }
    }
    
    if ($taskFound) {
        $response = [
            'success' => true,
            'message' => 'Tugas berhasil ditemukan',
            'task' => $foundTask
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Tugas tidak ditemukan'
        ];
    }
    
    // Kirim response JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Ambil pesan error jika ada
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Hapus pesan error setelah diambil
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    screens: {
                        'xs': '480px', // Adding extra small breakpoint
                    }
                }
            }
        }
    </script>
    <style>
        /* Modal animation */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .modal-overlay {
            animation: fadeIn 0.3s ease-out;
        }
        
        .modal-container {
            animation: slideIn 0.3s ease-out;
        }
        
        .modal-overlay.closing {
            animation: fadeIn 0.3s ease-out reverse;
        }
        
        .modal-container.closing {
            animation: slideIn 0.3s ease-out reverse;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans leading-normal min-h-screen flex flex-col">
    <div class="w-full max-w-xs sm:max-w-sm md:max-w-md mx-auto my-4 sm:my-6 md:my-10 px-3 sm:px-4 py-4 sm:py-6 bg-white rounded-lg shadow-md">
        <h1 class="text-xl sm:text-2xl font-bold text-center text-gray-700 mb-4 sm:mb-6">Todo List</h1>
        
        <div id="error-message" class="<?php echo !empty($errorMessage) ? '' : 'hidden'; ?> bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($errorMessage); ?></span>
        </div>
        
        <form method="POST" action="index.php" id="task-form" class="mb-4 sm:mb-6">
            <input type="hidden" name="action" value="add">
            <div class="flex flex-col xs:flex-row gap-2 xs:gap-0">
                <input 
                    type="text" 
                    name="task" 
                    id="task-input" 
                    placeholder="Add a new task..." 
                    required 
                    autofocus
                    class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-md xs:rounded-r-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                <button 
                    type="submit"
                    class="w-full xs:w-auto px-3 sm:px-4 py-2 bg-blue-500 text-white font-medium rounded-md xs:rounded-l-none hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-colors"
                >
                    Add Task
                </button>
            </div>
        </form>
        
        <ul id="task-list" class="space-y-2">
            <?php if (empty($_SESSION['tasks'])): ?>
                <li class="text-center py-4 sm:py-6 px-3 sm:px-4 bg-gray-50 text-gray-500 italic rounded-md">
                    Belum ada tugas yang terbuat, tambahkan sekarang!
                </li>
            <?php else: ?>
                <?php foreach ($_SESSION['tasks'] as $task): ?>
                    <li class="task-item px-3 sm:px-4 py-2 sm:py-3 bg-gray-50 rounded-md hover:bg-gray-100 transition-colors" data-id="<?php echo $task['id']; ?>">
                        <div class="task-view flex items-center justify-between">
                            <div class="flex items-center flex-1 min-w-0">
                                <input 
                                    type="checkbox" 
                                    class="task-checkbox w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3 cursor-pointer text-blue-500 focus:ring-blue-500 rounded" 
                                    id="task-<?php echo $task['id']; ?>"
                                    <?php echo $task['completed'] ? 'checked' : ''; ?>
                                    data-id="<?php echo $task['id']; ?>"
                                >
                                <label 
                                    for="task-<?php echo $task['id']; ?>" 
                                    class="task-label cursor-pointer flex-1 truncate mr-2 text-sm sm:text-base <?php echo $task['completed'] ? 'line-through text-gray-500' : ''; ?>"
                                >
                                    <?php echo htmlspecialchars($task['text']); ?>
                                </label>
                            </div>
                            <div class="flex items-center">
                                <button 
                                    class="edit-btn ml-1 sm:ml-2 text-blue-500 hover:text-blue-700 transition-colors text-sm sm:text-base flex-shrink-0 px-2 py-1"
                                    aria-label="Edit task"
                                    data-id="<?php echo $task['id']; ?>"
                                    data-text="<?php echo htmlspecialchars($task['text']); ?>"
                                >
                                    ✎
                                </button>
                                <button 
                                    class="delete-btn ml-1 sm:ml-2 text-red-500 font-bold hover:text-red-700 transition-colors text-lg sm:text-xl flex-shrink-0 px-2 py-1"
                                    aria-label="Delete task"
                                    data-id="<?php echo $task['id']; ?>"
                                >
                                    x
                                </button>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
    
    <!-- Edit Task Modal (Hidden by default) -->
    <div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden modal-overlay" aria-modal="true" role="dialog">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 modal-container">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Edit Task</h3>
            </div>
            <div class="p-6">
                <input type="hidden" id="edit-task-id">
                <div class="mb-4">
                    <label for="edit-task-input" class="block text-sm font-medium text-gray-700 mb-2">Task</label>
                    <input 
                        type="text" 
                        id="edit-task-input" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                    >
                </div>
                <div id="modal-error" class="hidden mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"></span>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-2">
                <button 
                    id="cancel-edit-btn"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 transition-colors"
                >
                    Cancel
                </button>
                <button 
                    id="save-edit-btn"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-colors"
                >
                    Save
                </button>
            </div>
        </div>
    </div>
    
    <footer class="text-center mt-auto py-4 text-gray-500 text-xs sm:text-sm">
        <p>&copy; <?php echo date('Y'); ?> Ary Syaddam. All rights reserved.</p>
    </footer>
    
    <script src="assets/js/app.js"></script>
</body>
</html>

