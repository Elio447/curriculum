document.addEventListener('DOMContentLoaded', () => {
    const taskForm = document.getElementById('task-form');
    const taskInput = document.getElementById('task-input');
    const taskList = document.getElementById('task-list');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const messageBox = document.getElementById('message-box');
    let tasks = JSON.parse(localStorage.getItem('tasks')) || [];
    let currentFilter = 'all';

    /**
     * @param {string} message
     * @param {string} type 
     */
    function showMessage(message, type = 'success') {
        messageBox.textContent = message;
        messageBox.style.opacity = '1';
        
        if (type === 'success') {
            messageBox.className = 'fixed bottom-4 left-1/2 -translate-x-1/2 px-6 py-3 rounded-xl text-white shadow-lg z-50 opacity-0 transition-opacity duration-300 bg-green-500';
        } else if (type === 'error') {
            messageBox.className = 'fixed bottom-4 left-1/2 -translate-x-1/2 px-6 py-3 rounded-xl text-white shadow-lg z-50 opacity-0 transition-opacity duration-300 bg-red-500';
        }

        setTimeout(() => {
            messageBox.style.opacity = '0';
        }, 2000);
    }
    function saveTasks() {
        localStorage.setItem('tasks', JSON.stringify(tasks));
    }
    function renderTasks() {
        taskList.innerHTML = '';
        const filteredTasks = tasks.filter(task => {
            if (currentFilter === 'all') return true;
            if (currentFilter === 'pending') return !task.completed;
            if (currentFilter === 'completed') return task.completed;
        });

        if (filteredTasks.length === 0 && tasks.length > 0) {
            const noTasksMessage = document.createElement('p');
            noTasksMessage.textContent = `No hay tareas ${currentFilter === 'pending' ? 'pendientes' : 'completadas'} para mostrar.`;
            noTasksMessage.className = 'text-center text-gray-500 italic mt-8';
            taskList.appendChild(noTasksMessage);
            return;
        } else if (tasks.length === 0) {
             const noTasksMessage = document.createElement('p');
            noTasksMessage.textContent = `Añade tu primera tarea.`;
            noTasksMessage.className = 'text-center text-gray-500 italic mt-8';
            taskList.appendChild(noTasksMessage);
            return;
        }

        filteredTasks.forEach((task) => {
            const listItem = document.createElement('li');
            listItem.className = `task-item flex items-center justify-between p-4 rounded-xl bg-gray-50 shadow-sm ${task.completed ? 'completed' : ''}`;
            listItem.dataset.id = task.id;

            listItem.innerHTML = `
                <span class="task-text text-lg text-gray-800 flex-grow">${task.text}</span>
                <div class="flex items-center gap-2 ml-4">
                    <button class="toggle-btn text-green-500 hover:text-green-600 transition duration-200">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </button>
                    <button class="delete-btn text-red-500 hover:text-red-600 transition duration-200">
                        <i class="fas fa-trash-alt text-xl"></i>
                    </button>
                </div>
            `;
            taskList.appendChild(listItem);
        });
    }
    taskForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const taskText = taskInput.value.trim();
        if (taskText) {
            const newTask = {
                id: Date.now(),
                text: taskText,
                completed: false
            };
            tasks.push(newTask);
            saveTasks();
            renderTasks();
            taskInput.value = '';
            showMessage('Tarea añadida con éxito.');
        } else {
            showMessage('El campo de tarea no puede estar vacío.', 'error');
        }
    });
    taskList.addEventListener('click', (e) => {
        const item = e.target.closest('li');
        if (!item) return;

        const taskId = parseInt(item.dataset.id);
        if (e.target.closest('.toggle-btn')) {
            tasks = tasks.map(task => 
                task.id === taskId ? { ...task, completed: !task.completed } : task
            );
            saveTasks();
            renderTasks();
            showMessage('Estado de la tarea actualizado.');
        }
        if (e.target.closest('.delete-btn')) {
            tasks = tasks.filter(task => task.id !== taskId);
            saveTasks();
            renderTasks();
            showMessage('Tarea eliminada con éxito.');
        }
    });

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            currentFilter = button.dataset.filter;
            filterButtons.forEach(btn => {
                btn.className = 'filter-btn px-4 py-2 rounded-xl text-sm font-medium bg-gray-300 text-gray-700 transition duration-200 transform hover:scale-105';
            });
            button.className = 'filter-btn px-4 py-2 rounded-xl text-sm font-medium bg-blue-500 text-white transition duration-200 transform hover:scale-105';
            renderTasks();
        });
    });

    renderTasks();
});
