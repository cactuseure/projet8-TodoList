document.addEventListener('DOMContentLoaded', () => {
    const taskItems = document.querySelectorAll('.task-list li');
    taskItems.forEach(taskItem => {
        const editButton = taskItem.querySelector('.edit-button');
        const deleteButton = taskItem.querySelector('.delete-button');
        const checkbox = taskItem.querySelector('.task-checkbox');

        editButton.addEventListener('click', async () => {
            const taskId = taskItem.dataset.taskId;
            try {
                const response = await fetch(`/tasks/${taskId}/edit`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (response.ok) {
                    window.location.href = `/tasks/${taskId}/edit`;
                } else {
                    console.error('Erreur lors de la requête.');
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        });

        deleteButton.addEventListener('click', async () => {
            const taskId = taskItem.dataset.taskId;
            const isConfirmed = confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?');
            if (!isConfirmed) return;

            try {
                const response = await fetch(`/tasks/${taskId}/delete`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (response.ok) {
                    window.location.reload();
                } else {
                    console.error('Erreur lors de la requête.');
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        });

        checkbox.addEventListener('change', async function() {
            const taskId = taskItem.dataset.taskId;

            try {
                const response = await fetch(`/tasks/${taskId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (response.ok) {
                    window.location.reload();
                } else {
                    console.error('Erreur lors de la requête.');
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        });
    });
});

window.addEventListener('beforeunload', function() {
    localStorage.setItem('scrollPosition', window.scrollY);
});

window.addEventListener('load', function() {
    if (localStorage.getItem('scrollPosition') !== null) {
        window.scrollTo(0, localStorage.getItem('scrollPosition'));
        localStorage.removeItem('scrollPosition'); // Supprimez après l'utilisation pour éviter de restaurer une ancienne position
    }
});