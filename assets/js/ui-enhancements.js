/**
 * UI/UX Enhancement JavaScript for E-Arsip
 * Advanced user interface interactions and experience improvements
 */

document.addEventListener('DOMContentLoaded', function() {
    initUIEnhancements();
    initAdvancedSearch();
    initSmartNotifications();
    initFormEnhancements();
    initTableEnhancements();
    initProgressIndicators();
    initKeyboardShortcuts();
    initAccessibilityFeatures();
});

/**
 * Initialize UI enhancements
 */
function initUIEnhancements() {
    // Add smooth scrolling
    document.documentElement.style.scrollBehavior = 'smooth';
    
    // Initialize tooltips with enhanced styling
    var tooltips = document.querySelectorAll('.tooltipped');
    M.Tooltip.init(tooltips, {
        position: 'top',
        delay: 50,
        html: true
    });
    
    // Initialize floating action button
    var fabs = document.querySelectorAll('.fixed-action-btn');
    M.FloatingActionButton.init(fabs, {
        direction: 'top',
        hoverEnabled: false
    });
    
    // Add page transition effects
    addPageTransitions();
    
    // Initialize lazy loading for images
    initLazyLoading();
    
    // Add scroll-to-top button
    addScrollToTopButton();
}

/**
 * Initialize advanced search functionality
 */
function initAdvancedSearch() {
    var searchInputs = document.querySelectorAll('.search-input, input[type="search"]');
    
    searchInputs.forEach(function(input) {
        // Add search suggestions
        addSearchSuggestions(input);
        
        // Add search history
        addSearchHistory(input);
        
        // Add real-time search
        addRealTimeSearch(input);
        
        // Add search filters
        addSearchFilters(input);
    });
}

/**
 * Add search suggestions
 */
function addSearchSuggestions(input) {
    var suggestions = [
        'Berita Acara Serah Terima',
        'Kendaraan Dinas',
        'User Management',
        'Laporan Bulanan',
        'Data Pegawai',
        'Inventaris Barang'
    ];
    
    input.addEventListener('input', function() {
        var value = this.value.toLowerCase();
        if (value.length < 2) {
            hideSuggestions();
            return;
        }
        
        var filtered = suggestions.filter(function(suggestion) {
            return suggestion.toLowerCase().includes(value);
        });
        
        showSuggestions(input, filtered);
    });
    
    input.addEventListener('blur', function() {
        setTimeout(hideSuggestions, 200);
    });
}

/**
 * Show search suggestions
 */
function showSuggestions(input, suggestions) {
    hideSuggestions();
    
    if (suggestions.length === 0) return;
    
    var container = document.createElement('div');
    container.className = 'search-suggestions fade-in';
    container.id = 'search-suggestions';
    
    suggestions.forEach(function(suggestion) {
        var item = document.createElement('div');
        item.className = 'suggestion-item';
        item.textContent = suggestion;
        item.addEventListener('click', function() {
            input.value = suggestion;
            hideSuggestions();
            performSearch(suggestion);
        });
        container.appendChild(item);
    });
    
    input.parentElement.appendChild(container);
}

/**
 * Hide search suggestions
 */
function hideSuggestions() {
    var existing = document.getElementById('search-suggestions');
    if (existing) {
        existing.remove();
    }
}

/**
 * Add search history
 */
function addSearchHistory(input) {
    var historyKey = 'search_history_' + (input.name || 'default');
    
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && this.value.trim()) {
            saveSearchHistory(historyKey, this.value.trim());
        }
    });
}

/**
 * Save search to history
 */
function saveSearchHistory(key, query) {
    var history = JSON.parse(localStorage.getItem(key) || '[]');
    
    // Remove if already exists
    history = history.filter(function(item) {
        return item !== query;
    });
    
    // Add to beginning
    history.unshift(query);
    
    // Keep only last 10 searches
    history = history.slice(0, 10);
    
    localStorage.setItem(key, JSON.stringify(history));
}

/**
 * Add real-time search
 */
function addRealTimeSearch(input) {
    var timeout;
    
    input.addEventListener('input', function() {
        clearTimeout(timeout);
        var query = this.value.trim();
        
        if (query.length < 2) return;
        
        timeout = setTimeout(function() {
            performSearch(query);
        }, 300);
    });
}

/**
 * Perform search
 */
function performSearch(query) {
    console.log('Searching for:', query);
    
    // Show loading state
    showSearchLoading();
    
    // Simulate API call
    setTimeout(function() {
        hideSearchLoading();
        updateSearchResults(query);
    }, 500);
}

/**
 * Show search loading
 */
function showSearchLoading() {
    var resultsContainer = document.querySelector('.search-results');
    if (resultsContainer) {
        resultsContainer.innerHTML = `
            <div class="center-align" style="padding: 40px;">
                <div class="preloader-wrapper small active">
                    <div class="spinner-layer spinner-blue-only">
                        <div class="circle-clipper left">
                            <div class="circle"></div>
                        </div>
                        <div class="gap-patch">
                            <div class="circle"></div>
                        </div>
                        <div class="circle-clipper right">
                            <div class="circle"></div>
                        </div>
                    </div>
                </div>
                <p style="margin-top: 20px; color: #666;">Mencari...</p>
            </div>
        `;
    }
}

/**
 * Hide search loading
 */
function hideSearchLoading() {
    // Implementation depends on your search results structure
}

/**
 * Update search results
 */
function updateSearchResults(query) {
    var resultsContainer = document.querySelector('.search-results');
    if (resultsContainer) {
        resultsContainer.innerHTML = `
            <div class="slide-in-up">
                <h5>Hasil pencarian untuk: "${query}"</h5>
                <p>Menampilkan hasil pencarian...</p>
            </div>
        `;
    }
}

/**
 * Initialize smart notifications
 */
function initSmartNotifications() {
    // Auto-hide notifications after 5 seconds
    var alerts = document.querySelectorAll('.alert, .card-panel');
    
    alerts.forEach(function(alert) {
        if (alert.classList.contains('auto-hide')) {
            setTimeout(function() {
                hideNotification(alert);
            }, 5000);
        }
        
        // Add close button if not exists
        if (!alert.querySelector('.alert-close')) {
            addCloseButton(alert);
        }
    });
}

/**
 * Add close button to notification
 */
function addCloseButton(alert) {
    var closeBtn = document.createElement('button');
    closeBtn.className = 'alert-close';
    closeBtn.innerHTML = '<i class="material-icons">close</i>';
    closeBtn.addEventListener('click', function() {
        hideNotification(alert);
    });
    
    alert.appendChild(closeBtn);
}

/**
 * Hide notification with animation
 */
function hideNotification(alert) {
    alert.style.animation = 'slideOutUp 0.3s ease';
    setTimeout(function() {
        alert.remove();
    }, 300);
}

/**
 * Show notification
 */
function showNotification(type, title, message, autoHide = true) {
    var notification = document.createElement('div');
    notification.className = `alert alert-${type} slide-in-down`;
    if (autoHide) notification.classList.add('auto-hide');
    
    notification.innerHTML = `
        <i class="material-icons alert-icon">${getNotificationIcon(type)}</i>
        <div class="alert-content">
            <div class="alert-title">${title}</div>
            <div class="alert-message">${message}</div>
        </div>
        <button class="alert-close">
            <i class="material-icons">close</i>
        </button>
    `;
    
    // Add to page
    var container = document.querySelector('.notification-container') || document.body;
    container.appendChild(notification);
    
    // Add close functionality
    notification.querySelector('.alert-close').addEventListener('click', function() {
        hideNotification(notification);
    });
    
    // Auto-hide
    if (autoHide) {
        setTimeout(function() {
            hideNotification(notification);
        }, 5000);
    }
}

/**
 * Get notification icon
 */
function getNotificationIcon(type) {
    switch (type) {
        case 'success': return 'check_circle';
        case 'warning': return 'warning';
        case 'error': return 'error';
        case 'info': return 'info';
        default: return 'info';
    }
}

/**
 * Initialize form enhancements
 */
function initFormEnhancements() {
    // Add form validation
    var forms = document.querySelectorAll('form');
    
    forms.forEach(function(form) {
        addFormValidation(form);
        addFormProgress(form);
        addAutoSave(form);
    });
    
    // Add input enhancements
    var inputs = document.querySelectorAll('input, textarea, select');
    
    inputs.forEach(function(input) {
        addInputEnhancements(input);
    });
}

/**
 * Add form validation
 */
function addFormValidation(form) {
    form.addEventListener('submit', function(e) {
        if (!validateForm(form)) {
            e.preventDefault();
            showNotification('error', 'Validasi Gagal', 'Mohon periksa kembali data yang diisi.');
        }
    });
}

/**
 * Validate form
 */
function validateForm(form) {
    var isValid = true;
    var inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(function(input) {
        if (!input.value.trim()) {
            markFieldInvalid(input, 'Field ini wajib diisi');
            isValid = false;
        } else {
            markFieldValid(input);
        }
    });
    
    return isValid;
}

/**
 * Mark field as invalid
 */
function markFieldInvalid(field, message) {
    field.classList.add('invalid');
    field.classList.remove('valid');
    
    // Add error message
    var errorMsg = field.parentElement.querySelector('.error-message');
    if (!errorMsg) {
        errorMsg = document.createElement('div');
        errorMsg.className = 'error-message';
        field.parentElement.appendChild(errorMsg);
    }
    errorMsg.textContent = message;
}

/**
 * Mark field as valid
 */
function markFieldValid(field) {
    field.classList.add('valid');
    field.classList.remove('invalid');
    
    // Remove error message
    var errorMsg = field.parentElement.querySelector('.error-message');
    if (errorMsg) {
        errorMsg.remove();
    }
}

/**
 * Add input enhancements
 */
function addInputEnhancements(input) {
    // Add character counter for text inputs
    if (input.hasAttribute('maxlength') && (input.type === 'text' || input.tagName === 'TEXTAREA')) {
        addCharacterCounter(input);
    }
    
    // Add format validation
    if (input.type === 'email') {
        addEmailValidation(input);
    }
    
    if (input.type === 'tel' || input.name === 'nip') {
        addPhoneValidation(input);
    }
}

/**
 * Add character counter
 */
function addCharacterCounter(input) {
    var maxLength = input.getAttribute('maxlength');
    var counter = document.createElement('div');
    counter.className = 'character-counter';
    
    function updateCounter() {
        var current = input.value.length;
        counter.textContent = current + '/' + maxLength;
        
        if (current > maxLength * 0.8) {
            counter.style.color = '#ff9800';
        } else {
            counter.style.color = '#666';
        }
    }
    
    input.addEventListener('input', updateCounter);
    input.parentElement.appendChild(counter);
    updateCounter();
}

/**
 * Initialize table enhancements
 */
function initTableEnhancements() {
    var tables = document.querySelectorAll('table');
    
    tables.forEach(function(table) {
        addTableSorting(table);
        addTableFiltering(table);
        addRowSelection(table);
    });
}

/**
 * Add table sorting
 */
function addTableSorting(table) {
    var headers = table.querySelectorAll('th');
    
    headers.forEach(function(header, index) {
        if (!header.classList.contains('no-sort')) {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(table, index);
            });
            
            // Add sort icon
            var icon = document.createElement('i');
            icon.className = 'material-icons sort-icon';
            icon.textContent = 'unfold_more';
            header.appendChild(icon);
        }
    });
}

/**
 * Sort table
 */
function sortTable(table, columnIndex) {
    var tbody = table.querySelector('tbody');
    var rows = Array.from(tbody.querySelectorAll('tr'));
    var isAscending = table.getAttribute('data-sort-direction') !== 'asc';
    
    rows.sort(function(a, b) {
        var aText = a.cells[columnIndex].textContent.trim();
        var bText = b.cells[columnIndex].textContent.trim();
        
        // Try to parse as numbers
        var aNum = parseFloat(aText);
        var bNum = parseFloat(bText);
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? aNum - bNum : bNum - aNum;
        }
        
        // Sort as text
        return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
    });
    
    // Update table
    rows.forEach(function(row) {
        tbody.appendChild(row);
    });
    
    // Update sort direction
    table.setAttribute('data-sort-direction', isAscending ? 'asc' : 'desc');
    
    // Update sort icons
    var headers = table.querySelectorAll('th .sort-icon');
    headers.forEach(function(icon, index) {
        if (index === columnIndex) {
            icon.textContent = isAscending ? 'keyboard_arrow_up' : 'keyboard_arrow_down';
        } else {
            icon.textContent = 'unfold_more';
        }
    });
}

/**
 * Initialize keyboard shortcuts
 */
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            var searchInput = document.querySelector('.search-input, input[type="search"]');
            if (searchInput) {
                searchInput.focus();
            }
        }
        
        // Escape to close modals/dropdowns
        if (e.key === 'Escape') {
            var openModal = document.querySelector('.modal.open');
            if (openModal) {
                M.Modal.getInstance(openModal).close();
            }
        }
    });
}

/**
 * Add page transitions
 */
function addPageTransitions() {
    // Add fade-in effect to main content
    var main = document.querySelector('main');
    if (main) {
        main.classList.add('fade-in');
    }
    
    // Add slide-in effect to cards
    var cards = document.querySelectorAll('.card');
    cards.forEach(function(card, index) {
        setTimeout(function() {
            card.classList.add('slide-in-up');
        }, index * 100);
    });
}

/**
 * Initialize lazy loading
 */
function initLazyLoading() {
    var images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        var imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(function(img) {
            imageObserver.observe(img);
        });
    }
}

/**
 * Add scroll to top button
 */
function addScrollToTopButton() {
    var button = document.createElement('button');
    button.className = 'btn-floating btn-large scroll-to-top';
    button.innerHTML = '<i class="material-icons">keyboard_arrow_up</i>';
    button.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    `;
    
    button.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    
    document.body.appendChild(button);
    
    // Show/hide based on scroll position
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            button.style.opacity = '1';
            button.style.visibility = 'visible';
        } else {
            button.style.opacity = '0';
            button.style.visibility = 'hidden';
        }
    });
}

// Export functions for global use
window.UIEnhancements = {
    showNotification: showNotification,
    hideNotification: hideNotification,
    performSearch: performSearch,
    validateForm: validateForm
};
