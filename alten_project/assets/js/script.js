/**
 * ALTEN Maroc - JavaScript Functionality
 * Développé par Fayssal Rajouani
 */

// Global variables
let currentPage = 1;
let itemsPerPage = 10;

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// Initialize Application
function initializeApp() {
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize file upload
    initializeFileUpload();
    
    // Initialize search functionality
    initializeSearch();
    
    // Initialize notifications
    initializeNotifications();
    
    // Initialize charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        initializeCharts();
    }
    
    // Add fade-in animation to cards
    animateCards();
}

// Initialize Bootstrap tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Form Validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                showNotification('Veuillez corriger les erreurs dans le formulaire', 'error');
            }
            form.classList.add('was-validated');
        }, false);
    });
}

// File Upload Functionality
function initializeFileUpload() {
    const uploadArea = document.querySelector('.file-upload-area');
    const fileInput = document.querySelector('#fileInput');
    
    if (uploadArea && fileInput) {
        // Click to upload
        uploadArea.addEventListener('click', function() {
            fileInput.click();
        });
        
        // Drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileUpload(files[0]);
            }
        });
        
        // File input change
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                handleFileUpload(e.target.files[0]);
            }
        });
    }
}

// Handle file upload
function handleFileUpload(file) {
    // Validate file type
    const allowedTypes = ['application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    
    if (!allowedTypes.includes(file.type)) {
        showNotification('Type de fichier non autorisé. Seuls les fichiers PDF et Excel sont acceptés.', 'error');
        return;
    }
    
    // Validate file size (max 10MB)
    if (file.size > 10 * 1024 * 1024) {
        showNotification('Le fichier est trop volumineux. Taille maximale : 10MB', 'error');
        return;
    }
    
    // Update UI
    const uploadArea = document.querySelector('.file-upload-area');
    const fileName = document.querySelector('#fileName');
    const fileSize = document.querySelector('#fileSize');
    
    if (fileName) fileName.textContent = file.name;
    if (fileSize) fileSize.textContent = formatFileSize(file.size);
    
    showNotification('Fichier sélectionné avec succès', 'success');
}

// Search functionality
function initializeSearch() {
    const searchInput = document.querySelector('#searchInput');
    const searchButton = document.querySelector('#searchButton');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            } else {
                // Real-time search with debounce
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    performSearch();
                }, 500);
            }
        });
    }
    
    if (searchButton) {
        searchButton.addEventListener('click', performSearch);
    }
}

// Perform search
function performSearch() {
    const searchInput = document.querySelector('#searchInput');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const tableRows = document.querySelectorAll('tbody tr');
    
    let visibleRows = 0;
    
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
            visibleRows++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update results count
    const resultsCount = document.querySelector('#resultsCount');
    if (resultsCount) {
        resultsCount.textContent = `${visibleRows} résultat(s) trouvé(s)`;
    }
}

// Notifications
function initializeNotifications() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }
        }, 5000);
    });
}

// Show notification
function showNotification(message, type = 'info') {
    const toast = document.querySelector('#liveToast');
    const toastBody = toast.querySelector('.toast-body');
    const toastHeader = toast.querySelector('.toast-header i');
    
    // Set message
    toastBody.textContent = message;
    
    // Set icon based on type
    toastHeader.className = `fas me-2 ${getIconClass(type)} text-${type === 'error' ? 'danger' : type}`;
    
    // Show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}

// Get icon class for notification type
function getIconClass(type) {
    switch(type) {
        case 'success': return 'fa-check-circle';
        case 'error': return 'fa-exclamation-circle';
        case 'warning': return 'fa-exclamation-triangle';
        default: return 'fa-info-circle';
    }
}

// Initialize Charts
function initializeCharts() {
    // Chart.js default configuration
    Chart.defaults.global.defaultFontFamily = 'Segoe UI';
    Chart.defaults.global.defaultFontColor = '#858796';
}

// Animate cards on page load
function animateCards() {
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('fade-in');
        }, index * 100);
    });
}

// Utility Functions
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-MA', {
        style: 'currency',
        currency: 'MAD'
    }).format(amount);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

// Confirmation dialogs
function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
    return confirm(message);
}

// Loading spinner
function showLoading() {
    const spinner = document.createElement('div');
    spinner.className = 'spinner-overlay';
    spinner.innerHTML = `
        <div class="spinner-border spinner-border-custom text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
    `;
    document.body.appendChild(spinner);
}

function hideLoading() {
    const spinner = document.querySelector('.spinner-overlay');
    if (spinner) {
        spinner.remove();
    }
}

// AJAX Helper
function makeAjaxRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url);
        xhr.setRequestHeader('Content-Type', 'application/json');
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                } catch (e) {
                    resolve(xhr.responseText);
                }
            } else {
                reject(new Error(`HTTP ${xhr.status}: ${xhr.statusText}`));
            }
        };
        
        xhr.onerror = function() {
            reject(new Error('Network error'));
        };
        
        xhr.send(data ? JSON.stringify(data) : null);
    });
}

// Export functions
function exportToExcel(tableId, filename = 'export.xlsx') {
    // This would require a library like SheetJS
    showNotification('Fonctionnalité d\'export Excel à implémenter', 'info');
}

function exportToPDF(elementId, filename = 'export.pdf') {
    // This would require a library like jsPDF
    showNotification('Fonctionnalité d\'export PDF à implémenter', 'info');
}

// Print functionality
function printPage() {
    window.print();
}

// Responsive table helper
function makeTableResponsive() {
    const tables = document.querySelectorAll('.table');
    tables.forEach(table => {
        if (!table.parentNode.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
}

// Initialize responsive tables on window resize
window.addEventListener('resize', function() {
    makeTableResponsive();
});

// Form auto-save (for drafts)
function initializeAutoSave() {
    const forms = document.querySelectorAll('form[data-autosave]');
    
    forms.forEach(form => {
        const formId = form.getAttribute('data-autosave');
        
        // Load saved data
        const savedData = localStorage.getItem(`autosave_${formId}`);
        if (savedData) {
            const data = JSON.parse(savedData);
            Object.keys(data).forEach(key => {
                const field = form.querySelector(`[name="${key}"]`);
                if (field) {
                    field.value = data[key];
                }
            });
        }
        
        // Save on input
        form.addEventListener('input', function() {
            const formData = new FormData(form);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            localStorage.setItem(`autosave_${formId}`, JSON.stringify(data));
        });
        
        // Clear on submit
        form.addEventListener('submit', function() {
            localStorage.removeItem(`autosave_${formId}`);
        });
    });
}

// Initialize auto-save
document.addEventListener('DOMContentLoaded', function() {
    initializeAutoSave();
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+S to save (prevent default browser save)
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        const submitButton = document.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.click();
        }
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        });
    }
});

// Performance monitoring
function logPerformance() {
    if (performance.timing) {
        const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
        console.log(`Page loaded in ${loadTime}ms`);
    }
}

window.addEventListener('load', logPerformance);