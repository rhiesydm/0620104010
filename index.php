<?php
// Memulai sesi untuk menyimpan tugas-tugas
session_start();

// Inisialisasi array tugas jika belum ada
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

// Inisialisasi variabel pesan error
$errorMessage = '';

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
    // Pengalihan untuk mencegah pengiriman ulang formulir
    header('Location: index.php');
    exit;
}

// Menangani penghapusan tugas
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    foreach ($_SESSION['tasks'] as $key => $task) {
        if ($task['id'] === $_GET['id']) {
            unset($_SESSION['tasks'][$key]);
            break;
        }
    }
    // Mengindeks ulang array
    $_SESSION['tasks'] = array_values($_SESSION['tasks']);
    header('Location: index.php');
    exit;
}

// Menangani pengalihan status penyelesaian tugas
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    foreach ($_SESSION['tasks'] as $key => $task) {
        if ($task['id'] === $_GET['id']) {
            // Toggle status completed
            $_SESSION['tasks'][$key]['completed'] = !$_SESSION['tasks'][$key]['completed'];
            break;
        }
    }
    header('Location: index.php');
    exit;
}

// Menangani pengeditan tugas
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    echo $_POST['edit'];
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
            // Set pesan error jika tugas duplikat
            $_SESSION['error_message'] = "Tugas dengan teks yang sama sudah ada!";
        } else {
            // Update tugas jika bukan duplikat
            foreach ($_SESSION['tasks'] as $key => $task) {
                if ($task['id'] === $taskId) {
                    $_SESSION['tasks'][$key]['text'] = $editedTaskText;
                    break;
                }
            }
        }
    }
    // Pengalihan untuk mencegah pengiriman ulang formulir
    header('Location: index.php');
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
    <title>Todo List Application</title>
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
</head>
<body class="bg-gray-100 text-gray-800 font-sans leading-normal min-h-screen flex flex-col">
    <div class="w-full max-w-xs sm:max-w-sm md:max-w-md mx-auto my-4 sm:my-6 md:my-10 px-3 sm:px-4 py-4 sm:py-6 bg-white rounded-lg shadow-md">
        <h1 class="text-xl sm:text-2xl font-bold text-center text-gray-700 mb-4 sm:mb-6">Todo List</h1>
        
        <?php if (!empty($errorMessage)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($errorMessage); ?></span>
        </div>
        <?php endif; ?>
        
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
                        <!-- Normal View -->
                        <div class="task-view flex items-center justify-between">
                            <div class="flex items-center flex-1 min-w-0">
                                <input 
                                    type="checkbox" 
                                    class="w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3 cursor-pointer text-blue-500 focus:ring-blue-500 rounded" 
                                    id="task-<?php echo $task['id']; ?>"
                                    <?php echo $task['completed'] ? 'checked' : ''; ?>
                                    onchange="window.location.href='index.php?action=toggle&id=<?php echo $task['id']; ?>'"
                                >
                                <label 
                                    for="task-<?php echo $task['id']; ?>" 
                                    class="cursor-pointer flex-1 truncate mr-2 text-sm sm:text-base <?php echo $task['completed'] ? 'line-through text-gray-500' : ''; ?>"
                                >
                                    <?php echo htmlspecialchars($task['text']); ?>
                                </label>
                            </div>
                            <div class="flex items-center">
                                <button 
                                    class="edit-btn ml-1 sm:ml-2 text-blue-500 hover:text-blue-700 transition-colors text-sm sm:text-base flex-shrink-0 px-2 py-1"
                                    aria-label="Edit task"
                                >
                                    ✎
                                </button>
                                <a 
                                    href="index.php?action=delete&id=<?php echo $task['id']; ?>" 
                                    class="delete-btn ml-1 sm:ml-2 text-red-500 font-bold hover:text-red-700 transition-colors text-lg sm:text-xl flex-shrink-0 px-2 py-1"
                                    aria-label="Delete task"
                                >
                                    x
                                </a>
                            </div>
                        </div>
                        
                        <!-- Edit View (Hidden by default) -->
                        <div class="task-edit hidden">
                            <form method="POST" action="index.php" class="edit-form flex items-center">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                                <input 
                                    type="text" 
                                    name="task" 
                                    class="edit-input flex-1 px-3 py-1 border border-gray-300 rounded-md mr-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    value="<?php echo htmlspecialchars($task['text']); ?>"
                                    required
                                >
                                <button 
                                    type="submit"
                                    class="save-btn bg-green-500 text-white px-2 py-1 rounded-md hover:bg-green-600 transition-colors mr-1"
                                >
                                    ✓
                                </button>
                                <button 
                                    type="button"
                                    class="cancel-btn bg-gray-500 text-white px-2 py-1 rounded-md hover:bg-gray-600 transition-colors"
                                >
                                    ✕
                                </button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
    
    <footer class="text-center mt-auto py-4 text-gray-500 text-xs sm:text-sm">
        <p>&copy; <?php echo date('Y'); ?> Ary Syaddam. All rights reserved.</p>
    </footer> 
    <script src="assets/js/script.js"></script>
</body>
</html>

