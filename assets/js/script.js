// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    // Toggle mobile menu
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Flash message auto-hide
    const flashMessage = document.querySelector('[data-flash-message]');
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.classList.add('opacity-0', 'transition-opacity', 'duration-500');
            setTimeout(() => flashMessage.remove(), 500);
        }, 5000);
    }
    
    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            const inputs = form.querySelectorAll('[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('border-red-500');
                    valid = false;
                } else {
                    input.classList.remove('border-red-500');
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    });
});

// AJAX helper function
function fetchData(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    return fetch(url, options)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        });
}