document.addEventListener('DOMContentLoaded', () => {
    // Elementos del formulario de tareas
    const taskForm = document.getElementById('task-form');
    const taskList = document.getElementById('task-list');
    const taskIdInput = document.getElementById('task-id');
    const taskTitle = document.getElementById('title');
    const taskDescription = document.getElementById('description');
    const taskDueDate = document.getElementById('due_date');
    const taskCompleted = document.getElementById('completed');
    const taskCategorySelect = document.getElementById('category_id');
    const taskUserIdInput = document.getElementById('user_id');
    const taskSubmitBtn = document.getElementById('task-submit-btn');
    const taskCancelBtn = document.getElementById('task-cancel-btn');

    // Elementos del formulario de categorías
    const categoryForm = document.getElementById('category-form');
    const categoryList = document.getElementById('category-list');
    const categoryIdInput = document.getElementById('category-id');
    const categoryName = document.getElementById('category-name');
    const categorySubmitBtn = document.getElementById('category-submit-btn');
    const categoryCancelBtn = document.getElementById('category-cancel-btn');

    // Variables de control
    let currentUserId = null;
    let isEditingTask = false;
    let isEditingCategory = false;

    // Verificar sesión de usuario
    function checkSession() {
        return fetch('server/user/session_info.php')
            .then(res => res.json())
            .then(data => {
                if (data.user_id) {
                    currentUserId = data.user_id;
                    taskUserIdInput.value = currentUserId;
                    return true;
                } else {
                    console.warn(data.error || 'No hay sesión activa');
                    window.location.href = 'login.php';
                    return false;
                }
            })
            .catch(error => {
                console.error('Error al verificar sesión:', error);
                return false;
            });
    }

    // =============== FUNCIONES PARA CATEGORÍAS ===============

    // Cargar categorías
    function loadCategories() {
        if (!currentUserId) return;

        fetch('server/category/index.php?user_id=' + currentUserId)
            .then(response => response.json())
            .then(categories => {
                renderCategories(categories);
                populateCategorySelect(categories);
            })
            .catch(error => {
                console.error('Error al cargar categorías:', error);
            });
    }

    // Renderizar lista de categorías
    function renderCategories(categories) {
        categoryList.innerHTML = '';
        categories.forEach(category => {
            const li = document.createElement('li');
            li.className = 'category-item';
            li.innerHTML = `
                <span>${category.name}</span>
                <div class="category-actions">
                    <button class="edit-btn" onclick="editCategory(${category.id}, '${category.name}')">
                        Editar
                    </button>
                    <button class="delete-btn" onclick="deleteCategory(${category.id})">
                        Eliminar
                    </button>
                </div>
            `;
            categoryList.appendChild(li);
        });
    }

    // Llenar el select de categorías
    function populateCategorySelect(categories) {
        // Mantener la opción por defecto
        taskCategorySelect.innerHTML = '<option value="">Seleccione una categoría</option>';
        
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            taskCategorySelect.appendChild(option);
        });
    }

    // Crear o actualizar categoría
    function saveCategory(event) {
        event.preventDefault();
        
        if (!categoryName.value.trim()) {
            alert('El nombre de la categoría es requerido');
            return;
        }

        const formData = new FormData();
        formData.append('name', categoryName.value);
        formData.append('user_id', currentUserId);

        let url = 'server/category/create.php';
        let method = 'POST';

        if (isEditingCategory) {
            url = 'server/category/update.php';
            formData.append('category_id', categoryIdInput.value);
        }

        fetch(url, {
            method: method,
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resetCategoryForm();
                loadCategories();
            } else {
                alert(data.message || 'Error al guardar la categoría');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    }

    // Resetear formulario de categoría
    function resetCategoryForm() {
        categoryForm.reset();
        categoryIdInput.value = '';
        categorySubmitBtn.textContent = 'Agregar categoría';
        categoryCancelBtn.style.display = 'none';
        isEditingCategory = false;
    }

    // =============== FUNCIONES PARA TAREAS ===============

    // Cargar tareas
    function loadTasks() {
        if (!currentUserId) return;

        fetch('server/task/index.php?user_id=' + currentUserId)
            .then(response => response.json())
            .then(tasks => {
                renderTasks(tasks);
            })
            .catch(error => {
                console.error('Error al cargar tareas:', error);
            });
    }

    // Renderizar lista de tareas
    function renderTasks(tasks) {
        taskList.innerHTML = '';
        tasks.forEach(task => {
            const li = document.createElement('li');
            li.className = task.complete == 1 ? 'task-ready' : 'task-pending';
            
            const dueDate = task.due_date ? new Date(task.due_date).toLocaleDateString() : 'Sin fecha límite';
            
            li.innerHTML = `
                <div class="task-content">
                    <h4>${task.title}</h4>
                    <p>${task.description || 'Sin descripción'}</p>
                    <p class="task-meta">
                        <span>Categoría: ${task.category_name || 'Sin categoría'}</span>
                        <span>Fecha límite: ${dueDate}</span>
                    </p>
                </div>
                <div class="task-actions">
                    ${task.complete == 0 ? 
                        `<button class="complete-btn" onclick="completeTask(${task.id})">
                            Completar
                        </button>` : ''}
                    <button class="edit-btn" onclick="editTask(${task.id})">
                        Editar
                    </button>
                    <button class="delete-btn" onclick="deleteTask(${task.id})">
                        Eliminar
                    </button>
                </div>
            `;
            taskList.appendChild(li);
        });
    }

    // Enviar formulario de tarea
    function handleTaskFormSubmit(event) {
        event.preventDefault();
        
        const formData = new FormData(taskForm);
        
        // Verificar si el checkbox está marcado y ajustar el valor
        formData.set('completed', taskCompleted.checked ? '1' : '0');
        
        let url = 'server/task/create.php';
        
        if (isEditingTask) {
            url = 'server/task/update.php';
        }

        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resetTaskForm();
                loadTasks();
            } else {
                alert(data.message || 'Error al guardar la tarea');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    }

    // Resetear formulario de tarea
    function resetTaskForm() {
        taskForm.reset();
        taskIdInput.value = '';
        taskSubmitBtn.textContent = 'Agregar tarea';
        taskCancelBtn.style.display = 'none';
        isEditingTask = false;
    }

    // =============== FUNCIONES GLOBALES (WINDOW) ===============

    // Editar categoría
    window.editCategory = function(id, name) {
        categoryIdInput.value = id;
        categoryName.value = name;
        categorySubmitBtn.textContent = 'Actualizar categoría';
        categoryCancelBtn.style.display = 'inline-block';
        isEditingCategory = true;
        
        // Hacer scroll hacia el formulario
        categoryForm.scrollIntoView({ behavior: 'smooth' });
    };

    // Eliminar categoría
    window.deleteCategory = function(id) {
        if (!confirm('¿Está seguro de eliminar esta categoría? Se eliminarán todas las tareas asociadas.')) {
            return;
        }

        fetch('server/category/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `category_id=${id}&user_id=${currentUserId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCategories();
                loadTasks();
            } else {
                alert(data.message || 'Error al eliminar la categoría');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    };

    // Editar tarea
    window.editTask = function(id) {
        fetch(`server/task/get.php?task_id=${id}&user_id=${currentUserId}`)
            .then(response => response.json())
            .then(task => {
                if (task) {
                    taskIdInput.value = task.id;
                    taskTitle.value = task.title;
                    taskDescription.value = task.description || '';
                    taskDueDate.value = task.due_date || '';
                    taskCompleted.checked = task.complete == 1;
                    taskCategorySelect.value = task.category_id || '';
                    
                    taskSubmitBtn.textContent = 'Actualizar tarea';
                    taskCancelBtn.style.display = 'inline-block';
                    isEditingTask = true;
                    
                    // Hacer scroll hacia el formulario
                    taskForm.scrollIntoView({ behavior: 'smooth' });
                }
            })
            .catch(error => {
                console.error('Error al obtener tarea:', error);
            });
    };

    // Eliminar tarea
    window.deleteTask = function(id) {
        if (!confirm('¿Está seguro de eliminar esta tarea?')) {
            return;
        }

        fetch('server/task/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `task_id=${id}&user_id=${currentUserId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadTasks();
            } else {
                alert(data.message || 'Error al eliminar la tarea');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    };

    // Marcar tarea como completada
    window.completeTask = function(id) {
        fetch('server/task/complete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `task_id=${id}&user_id=${currentUserId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadTasks();
            } else {
                alert(data.message || 'Error al completar la tarea');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    };

    // =============== EVENT LISTENERS ===============

    // Formulario de categoría
    categoryForm.addEventListener('submit', saveCategory);
    
    // Botón cancelar categoría
    categoryCancelBtn.addEventListener('click', resetCategoryForm);
    
    // Formulario de tarea
    taskForm.addEventListener('submit', handleTaskFormSubmit);
    
    // Botón cancelar tarea
    taskCancelBtn.addEventListener('click', resetTaskForm);

    // =============== INICIALIZACIÓN ===============

    // Verificar sesión y cargar datos
    checkSession().then(sessionActive => {
        if (sessionActive) {
            loadCategories();
            loadTasks();
        }
    });
});
