// Theme Configuration
const themeConfig = {
    sidebarStorageKey: 'admin_sidebar_state',
    dateTimeFormat: {
        date: {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        },
        time: {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        }
    }
};

// DOM Elements
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const currentTime = document.getElementById('currentTime');

// Sidebar Toggle Functionality
function initializeSidebar() {
    // Load saved state
    const savedState = localStorage.getItem(themeConfig.sidebarStorageKey);
    if (savedState === 'collapsed') {
        document.body.classList.add('sidebar-collapsed');
    }

    // Toggle sidebar
    sidebarToggle?.addEventListener('click', () => {
        document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem(
            themeConfig.sidebarStorageKey,
            document.body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded'
        );
    });

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            if (!sidebar?.contains(e.target) && !sidebarToggle?.contains(e.target)) {
                document.body.classList.add('sidebar-collapsed');
            }
        }
    });
}

// Time Display Functionality
function initializeTimeDisplay() {
    if (!currentTime) return;

    function updateTime() {
        const now = new Date();
        const options = {
            ...themeConfig.dateTimeFormat.time,
            timeZoneName: 'short'
        };
        currentTime.textContent = now.toLocaleTimeString(undefined, options);
    }

    updateTime();
    setInterval(updateTime, 1000);
}

// Form Validation
function initializeFormValidation() {
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', (e) => {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    
                    // Create or update error message
                    let errorMessage = field.nextElementSibling;
                    if (!errorMessage || !errorMessage.classList.contains('error-message')) {
                        errorMessage = document.createElement('div');
                        errorMessage.classList.add('error-message');
                        field.parentNode.insertBefore(errorMessage, field.nextSibling);
                    }
                    errorMessage.textContent = 'Este campo é obrigatório.';
                } else {
                    field.classList.remove('is-invalid');
                    const errorMessage = field.nextElementSibling;
                    if (errorMessage?.classList.contains('error-message')) {
                        errorMessage.remove();
                    }
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    });
}

// Table Sorting
function initializeTableSorting() {
    document.querySelectorAll('table[data-sortable]').forEach(table => {
        const headers = table.querySelectorAll('th[data-sort]');
        
        headers.forEach(header => {
            header.addEventListener('click', () => {
                const column = header.dataset.sort;
                const direction = header.dataset.direction === 'asc' ? 'desc' : 'asc';
                
                // Remove sort direction from all headers
                headers.forEach(h => delete h.dataset.direction);
                
                // Set sort direction on clicked header
                header.dataset.direction = direction;

                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));

                // Sort rows
                rows.sort((a, b) => {
                    const aValue = a.querySelector(`td[data-column="${column}"]`).textContent;
                    const bValue = b.querySelector(`td[data-column="${column}"]`).textContent;
                    
                    return direction === 'asc' 
                        ? aValue.localeCompare(bValue)
                        : bValue.localeCompare(aValue);
                });

                // Update table
                tbody.append(...rows);
            });
        });
    });
}

// Notification System
class NotificationSystem {
    constructor() {
        this.container = document.createElement('div');
        this.container.className = 'notification-container';
        document.body.appendChild(this.container);
    }

    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;

        this.container.appendChild(notification);

        // Trigger animation
        setTimeout(() => notification.classList.add('show'), 10);

        // Remove notification after duration
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }
}

// Initialize features when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initializeSidebar();
    initializeTimeDisplay();
    initializeFormValidation();
    initializeTableSorting();

    // Initialize notification system
    window.notifications = new NotificationSystem();
});

// Handle responsive behavior
window.addEventListener('resize', () => {
    if (window.innerWidth <= 768) {
        document.body.classList.add('sidebar-collapsed');
    }
});

// Add custom event listeners for dynamic content
document.addEventListener('click', (e) => {
    // Handle dynamic buttons
    if (e.target.matches('[data-action]')) {
        const action = e.target.dataset.action;
        switch (action) {
            case 'refresh':
                window.location.reload();
                break;
            case 'back':
                window.history.back();
                break;
            // Add more actions as needed
        }
    }
});

// Export utilities for use in other scripts
window.adminUtils = {
    formatDate(date) {
        return new Date(date).toLocaleDateString(undefined, themeConfig.dateTimeFormat.date);
    },
    
    formatTime(date) {
        return new Date(date).toLocaleTimeString(undefined, themeConfig.dateTimeFormat.time);
    },
    
    formatDateTime(date) {
        return new Date(date).toLocaleString(undefined, {
            ...themeConfig.dateTimeFormat.date,
            ...themeConfig.dateTimeFormat.time
        });
    },

    showNotification(message, type = 'info', duration = 5000) {
        window.notifications?.show(message, type, duration);
    }
};