document.addEventListener('DOMContentLoaded', () => {
    const taskForm = document.getElementById('task-form');
    const taskInput = document.getElementById('task-input');
    const taskList = document.getElementById('task-list');

    let tasks = [];
    let isEditing = false;
    let editingId = null;

    taskForm.addEventListener('click', (e) => {
        const vti = taskInput.value.trim();
        if (vti !== '') {
            if (isEditing) {
                tasks = tasks.map(task =>
                    task.id === editingId ? {
                        ...task, text: vti
                    } : task);
                isEditing = false;
                editingId = null;
                taskForm.innerText = "Agregar";
            }
            else {
                const task = {
                    id: Date.now(),
                    text: vti,
                    complete: false
                };
                tasks.push(task);
                console.log(tasks);
            }
            renderTasks();
            taskInput.value = '';
        }
    });

    function renderTasks() {
        taskList.innerHTML = '';
        tasks.forEach(
            task => {
                console.log(task);

                const li = document.createElement('li');
                li.className='flex justify-between items-center bg-gray-100 px-12 py-15 rounded';
            if (task.complete) {
                li.classList.add('completada');
            }
                li.innerHTML =
                '<span>' + task.text + '</span>' +
                '<div>' +
               
                (task.complete ?
                    '<button class="complete-btn" disabled>Modificada</button>' :
                   
                    
                    '<button class="complete-btn" onclick="toggleComplete(' + task.id + ')">Marcar Completa</button>'+
                    '<button class="edit-btn" onclick="editTask(' + task.id + ')">Editar</button>' +
                    '<button class="delete-btn" onclick="deleteTask(' + task.id + ')">Eliminar</button>' 
                ) +
                '</div>';
        taskList.appendChild(li);
    
        const completeBtn = li.querySelector('.complete-btn');
        completeBtn.addEventListener('click', () => {
            completeTask(task.id);
        });
        });
    }

    window.completeTask = function (id) {
        tasks = tasks.map(task =>
            task.id === id ? { ...task, complete: !task.complete } : task
        );
        renderTasks();
    }

    window.deleteTask = function (id) {
        tasks = tasks.filter(task => task.id !== id);
        renderTasks();
    }

    window.editTask = function (id) {
        console.log(id);
        const et = tasks.find(t => t.id === id);
        if (et) {
            taskInput.value = et.text;
            taskForm.innerText = "Guardar";
            isEditing = true;
            editingId = et.id;
        }
    }

    
});