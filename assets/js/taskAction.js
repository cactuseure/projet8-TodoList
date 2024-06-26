document.addEventListener('turbo:load', (event) => {
    initializeTaskList();
    initializeDeleteModal();
});

function initializeTaskList() {
    if (!document.querySelector('.task-list')) {
        return;
    }

    const csrfToken = document.querySelector('.task-list').getAttribute('data-csrf-token');

    document.querySelectorAll('.task-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const taskId = this.closest('li').getAttribute('data-task-id');
            toggleTask(taskId, csrfToken);
        });
    });
}

function toggleTask(taskId, csrfToken) {
    const url = `/tasks/${taskId}/toggle`;
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken
        }
    })
        .then(response => {
            if (response.ok) {
                location.reload(); // Recharge la page pour afficher les changements
            } else {
                console.error('Erreur lors de la mise à jour de la tâche.');
            }
        })
        .catch(error => {
            console.error('Erreur réseau :', error);
        });
}

function initializeDeleteModal() {
    const deleteModal = document.getElementById('deleteModal');
    if (!deleteModal) {
        return;
    }

    deleteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget; // Bouton qui a déclenché la modal
        const taskId = button.getAttribute('data-task-id'); // Récupère l'ID de la tâche

        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        confirmDeleteBtn.href = `/tasks/${taskId}/delete`;
    });
}

// Gestion de la position de défilement
window.addEventListener('beforeunload', function() {
    localStorage.setItem('scrollPosition', window.scrollY);
});

window.addEventListener('load', function() {
    if (localStorage.getItem('scrollPosition') !== null) {
        window.scrollTo(0, localStorage.getItem('scrollPosition'));
        localStorage.removeItem('scrollPosition');
    }
});