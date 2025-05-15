// Confirm Delete
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// Flash Message Auto-hide
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    });
});

// Form Validation
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    return isValid;
}

// Date Range Picker
function initDateRangePicker(startInput, endInput) {
    if (!startInput || !endInput) return;

    startInput.addEventListener('change', function() {
        endInput.min = this.value;
    });

    endInput.addEventListener('change', function() {
        startInput.max = this.value;
    });
}
