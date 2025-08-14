<?php
/**
 * README.md
 * * Este es el archivo README para el proyecto de la aplicación de tareas.
 *
 * ## Descripción del Proyecto
 * Esta es una aplicación de gestión de tareas (To-Do App) simple, construida con PHP,
 * MySQL, HTML y Tailwind CSS. El objetivo es demostrar un conocimiento básico a intermedio
 * del desarrollo web con PHP. La aplicación permite a los usuarios:
 * - Crear, leer, actualizar y eliminar tareas (CRUD).
 * - Registrarse e iniciar sesión.
 * - Administrar tareas de forma individual, con cada usuario viendo solo sus propias tareas.
 *
 * ## Estructura de Archivos
 *
 * /
 * ├── config/
 * │   └── database.php      # Configuración de la conexión a la base de datos
 * ├── core/
 * │   └── TaskController.php # Lógica de negocio para el manejo de tareas
 * ├── api/
 * │   └── delete_task.php   # Endpoint de la API para eliminar tareas
 * │   └── update_status.php # Endpoint de la API para actualizar el estado de las tareas
 * ├── index.php             # Página principal de la aplicación
 * ├── login.php             # Página de inicio de sesión
 * ├── register.php          # Página de registro de usuarios
 * ├── logout.php            # Lógica para cerrar la sesión del usuario
 * └── schema.sql            # Script SQL para la creación de la base de datos
 *
 * ## Requisitos
 * - Servidor web (Apache, Nginx, etc.)
 * - PHP 7.4+
 * - Base de datos MySQL
 *
 * ## Configuración
 * 1.  Crea una base de datos MySQL.
 * 2.  Ejecuta el script `schema.sql` para crear las tablas `users` y `tasks`.
 * 3.  Modifica el archivo `config/database.php` con tus credenciales de la base de datos.
 * 4.  Coloca todos los archivos en tu servidor web y navega a `index.php` para empezar.
 *
 */
?>
<?php
/**
 * schema.sql
 *
 * Este archivo contiene las sentencias SQL para crear la base de datos
 * y las tablas necesarias para la aplicación.
 *
 */

// Script SQL para crear las tablas
/*
CREATE DATABASE IF NOT EXISTS todo_app;

USE todo_app;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    is_completed BOOLEAN NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
*/
?>
<?php
// -----------------------------------------------------------------------------
// Archivo: config/database.php
// Descripción: Configuración de la conexión a la base de datos.
// -----------------------------------------------------------------------------

// Credenciales de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'todo_app');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // Crear una instancia de PDO
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    // Establecer el modo de error de PDO a excepción para manejar errores
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Establecer el modo de fetch predeterminado a FETCH_ASSOC
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // En caso de error, muestra un mensaje amigable y el error real para debugging
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>
<?php
// -----------------------------------------------------------------------------
// Archivo: core/TaskController.php
// Descripción: Clase para manejar la lógica de las tareas y la base de datos.
// -----------------------------------------------------------------------------

class TaskController {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getTasksByUser($userId) {
        $sql = "SELECT * FROM tasks WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function createTask($userId, $title, $description) {
        $sql = "INSERT INTO tasks (user_id, title, description) VALUES (:user_id, :title, :description)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'title' => $title, 'description' => $description]);
    }

    public function updateTask($id, $title, $description, $isCompleted) {
        $sql = "UPDATE tasks SET title = :title, description = :description, is_completed = :is_completed WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['title' => $title, 'description' => $description, 'is_completed' => $isCompleted, 'id' => $id]);
    }
    
    public function updateTaskStatus($id, $isCompleted) {
        $sql = "UPDATE tasks SET is_completed = :is_completed WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['is_completed' => $isCompleted, 'id' => $id]);
    }

    public function deleteTask($id) {
        $sql = "DELETE FROM tasks WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    public function registerUser($username, $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['username' => $username, 'password' => $hashed_password]);
    }

    public function getUserByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }
}
?>
<?php
// -----------------------------------------------------------------------------
// Archivo: index.php
// Descripción: Punto de entrada principal de la aplicación.
// -----------------------------------------------------------------------------

require 'config/database.php';
require 'core/TaskController.php';

session_start();

// Validar la sesión del usuario
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$taskController = new TaskController($pdo);

// Manejar la acción de crear una tarea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $taskController->createTask($user_id, $title, $description);
    header("Location: index.php");
    exit();
}

// Obtener todas las tareas del usuario
$tasks = $taskController->getTasksByUser($user_id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplicación de Tareas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-2xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Tus Tareas</h1>
            <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-red-600 transition duration-300">Cerrar Sesión</a>
        </div>

        <!-- Formulario para agregar una nueva tarea -->
        <form action="index.php" method="POST" class="mb-8 p-6 bg-gray-50 rounded-xl shadow-inner">
            <input type="hidden" name="action" value="create">
            <div class="mb-4">
                <label for="title" class="block text-gray-700 font-semibold mb-2">Título de la Tarea</label>
                <input type="text" name="title" id="title" required class="w-full p-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-semibold mb-2">Descripción</label>
                <textarea name="description" id="description" rows="3" class="w-full p-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white p-3 rounded-xl font-bold hover:bg-blue-600 transition duration-300">
                <i class="fas fa-plus mr-2"></i> Añadir Tarea
            </button>
        </form>

        <!-- Lista de Tareas -->
        <div class="space-y-4">
            <?php if (count($tasks) > 0): ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="flex items-center justify-between p-4 bg-white rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                        <div class="flex items-start flex-1 min-w-0">
                            <input type="checkbox"
                                   id="task-<?= htmlspecialchars($task['id']) ?>"
                                   data-task-id="<?= htmlspecialchars($task['id']) ?>"
                                   <?= $task['is_completed'] ? 'checked' : '' ?>
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded mt-1.5 focus:ring-blue-500 cursor-pointer">
                            <div class="ml-4 flex-1">
                                <h3 class="font-semibold text-lg <?= $task['is_completed'] ? 'line-through text-gray-500' : 'text-gray-800' ?>">
                                    <?= htmlspecialchars($task['title']) ?>
                                </h3>
                                <p class="text-sm text-gray-600 mt-1 <?= $task['is_completed'] ? 'line-through text-gray-400' : '' ?>">
                                    <?= htmlspecialchars($task['description']) ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-2 ml-4">
                            <!-- Botón de Editar (abre modal o formulario de edición) -->
                            <button onclick="editTask('<?= htmlspecialchars(json_encode($task)) ?>')"
                                    class="text-blue-500 hover:text-blue-700 transition-colors duration-300">
                                <i class="fas fa-edit"></i>
                            </button>
                            <!-- Formulario para eliminar la tarea (la lógica se manejará en api/delete_task.php) -->
                            <form action="api/delete_task.php" method="POST">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($task['id']) ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 transition-colors duration-300">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-gray-500 py-8">¡Aún no tienes tareas!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de Edición -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center p-4">
        <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-md">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Editar Tarea</h2>
            <form id="editForm" action="api/update_task.php" method="POST">
                <input type="hidden" name="id" id="edit-id">
                <div class="mb-4">
                    <label for="edit-title" class="block text-gray-700 font-semibold mb-2">Título</label>
                    <input type="text" name="title" id="edit-title" required class="w-full p-3 border border-gray-300 rounded-xl">
                </div>
                <div class="mb-4">
                    <label for="edit-description" class="block text-gray-700 font-semibold mb-2">Descripción</label>
                    <textarea name="description" id="edit-description" rows="3" class="w-full p-3 border border-gray-300 rounded-xl"></textarea>
                </div>
                <div class="flex items-center mb-6">
                    <input type="checkbox" name="is_completed" id="edit-is_completed" class="form-checkbox h-5 w-5 text-blue-600 rounded">
                    <label for="edit-is_completed" class="ml-2 text-gray-700">Completada</label>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal()" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-xl font-semibold hover:bg-gray-400 transition-colors duration-300">Cancelar</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-xl font-bold hover:bg-blue-600 transition-colors duration-300">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Funciones para manejar el modal de edición
        const editModal = document.getElementById('editModal');
        const editForm = document.getElementById('editForm');
        const editId = document.getElementById('edit-id');
        const editTitle = document.getElementById('edit-title');
        const editDescription = document.getElementById('edit-description');
        const editIsCompleted = document.getElementById('edit-is_completed');

        function editTask(taskJson) {
            const task = JSON.parse(taskJson);
            editId.value = task.id;
            editTitle.value = task.title;
            editDescription.value = task.description;
            editIsCompleted.checked = task.is_completed == 1;
            editModal.classList.remove('hidden');
            editModal.classList.add('flex');
        }

        function closeModal() {
            editModal.classList.add('hidden');
            editModal.classList.remove('flex');
        }

        // Manejar el cambio del estado de la tarea (completado)
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', async (e) => {
                const taskId = e.target.dataset.taskId;
                const isCompleted = e.target.checked ? 1 : 0;
                
                const response = await fetch('api/update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: taskId, is_completed: isCompleted })
                });

                if (response.ok) {
                    location.reload(); // Recargar la página para ver el cambio
                } else {
                    console.error('Error al actualizar la tarea');
                }
            });
        });
    </script>
</body>
</html>
