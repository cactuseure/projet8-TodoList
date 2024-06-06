document.addEventListener('turbo:load', (event) => {
    initializeAlertCloseButtons();
});

function initializeAlertCloseButtons() {

    if (!document.querySelector('aside.notifications .alert')) {
        return;
    }

    document.querySelectorAll('aside.notifications .alert').forEach(alert => {
        alert.querySelector('.close-btn svg').addEventListener('click', function () {
            this.closest('.alert').remove();
        });
    });
}
