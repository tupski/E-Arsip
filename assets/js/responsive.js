/**
 * Responsive JavaScript Enhancements for E-Arsip
 * Handles responsive behavior and mobile interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize responsive components
    initResponsiveComponents();
    initMobileNavigation();
    initResponsiveTables();
    initTouchGestures();
    initLoadingStates();
    initSearchEnhancements();
    
    // Handle window resize
    window.addEventListener('resize', debounce(handleResize, 250));
    
    // Handle orientation change
    window.addEventListener('orientationchange', function() {
        setTimeout(handleResize, 100);
    });
});

/**
 * Initialize responsive components
 */
function initResponsiveComponents() {
    // Initialize Materialize components
    var elems = document.querySelectorAll('.sidenav');
    M.Sidenav.init(elems, {
        edge: 'left',
        draggable: true
    });
    
    var dropdowns = document.querySelectorAll('.dropdown-trigger');
    M.Dropdown.init(dropdowns, {
        coverTrigger: false,
        constrainWidth: false
    });
    
    var modals = document.querySelectorAll('.modal');
    M.Modal.init(modals, {
        dismissible: true,
        opacity: 0.5
    });
    
    var tooltips = document.querySelectorAll('.tooltipped');
    M.Tooltip.init(tooltips);
    
    var selects = document.querySelectorAll('select');
    M.FormSelect.init(selects);
    
    var datepickers = document.querySelectorAll('.datepicker');
    M.Datepicker.init(datepickers, {
        format: 'dd/mm/yyyy',
        i18n: {
            months: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 
                         'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            weekdays: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
            weekdaysShort: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            weekdaysAbbrev: ['M', 'S', 'S', 'R', 'K', 'J', 'S']
        }
    });
}

/**
 * Initialize mobile navigation
 */
function initMobileNavigation() {
    var sidenavTrigger = document.querySelector('.sidenav-trigger');
    var sidenav = document.querySelector('.sidenav');
    
    if (sidenavTrigger && sidenav) {
        // Add swipe gestures for mobile
        var hammer = new Hammer(document.body);
        
        hammer.on('swiperight', function(e) {
            if (window.innerWidth <= 992 && e.deltaX > 50) {
                var instance = M.Sidenav.getInstance(sidenav);
                instance.open();
            }
        });
        
        hammer.on('swipeleft', function(e) {
            if (window.innerWidth <= 992 && e.deltaX < -50) {
                var instance = M.Sidenav.getInstance(sidenav);
                instance.close();
            }
        });
    }
    
    // Auto-close sidenav on mobile when clicking links
    var sidenavLinks = document.querySelectorAll('.sidenav a');
    sidenavLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 992) {
                var instance = M.Sidenav.getInstance(sidenav);
                instance.close();
            }
        });
    });
}

/**
 * Initialize responsive tables
 */
function initResponsiveTables() {
    var tables = document.querySelectorAll('table');
    
    tables.forEach(function(table) {
        // Add responsive wrapper if not exists
        if (!table.parentElement.classList.contains('responsive-table')) {
            var wrapper = document.createElement('div');
            wrapper.className = 'responsive-table';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
        
        // Add mobile-friendly table headers
        if (window.innerWidth <= 600) {
            addMobileTableHeaders(table);
        }
    });
}

/**
 * Add mobile table headers
 */
function addMobileTableHeaders(table) {
    var headers = table.querySelectorAll('th');
    var rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(function(row) {
        var cells = row.querySelectorAll('td');
        cells.forEach(function(cell, index) {
            if (headers[index]) {
                cell.setAttribute('data-label', headers[index].textContent);
            }
        });
    });
}

/**
 * Initialize touch gestures
 */
function initTouchGestures() {
    // Add touch feedback to buttons
    var buttons = document.querySelectorAll('.btn, .btn-floating');
    
    buttons.forEach(function(button) {
        button.addEventListener('touchstart', function() {
            this.classList.add('touching');
        });
        
        button.addEventListener('touchend', function() {
            var self = this;
            setTimeout(function() {
                self.classList.remove('touching');
            }, 150);
        });
    });
    
    // Add pull-to-refresh functionality
    var pullToRefresh = document.querySelector('.pull-to-refresh');
    if (pullToRefresh) {
        initPullToRefresh(pullToRefresh);
    }
}

/**
 * Initialize pull-to-refresh
 */
function initPullToRefresh(element) {
    var startY = 0;
    var currentY = 0;
    var pulling = false;
    var threshold = 100;
    
    element.addEventListener('touchstart', function(e) {
        startY = e.touches[0].pageY;
        pulling = true;
    });
    
    element.addEventListener('touchmove', function(e) {
        if (!pulling) return;
        
        currentY = e.touches[0].pageY;
        var deltaY = currentY - startY;
        
        if (deltaY > 0 && window.scrollY === 0) {
            e.preventDefault();
            var progress = Math.min(deltaY / threshold, 1);
            element.style.transform = 'translateY(' + (deltaY * 0.5) + 'px)';
            element.style.opacity = 1 - (progress * 0.3);
        }
    });
    
    element.addEventListener('touchend', function(e) {
        if (!pulling) return;
        
        var deltaY = currentY - startY;
        
        if (deltaY > threshold) {
            // Trigger refresh
            location.reload();
        } else {
            // Reset
            element.style.transform = '';
            element.style.opacity = '';
        }
        
        pulling = false;
    });
}

/**
 * Initialize loading states
 */
function initLoadingStates() {
    // Add loading state to forms
    var forms = document.querySelectorAll('form');
    
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            var submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="material-icons left">hourglass_empty</i>Memproses...';
                submitBtn.classList.add('loading');
            }
        });
    });
    
    // Add loading state to AJAX links
    var ajaxLinks = document.querySelectorAll('[data-ajax]');
    
    ajaxLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            showLoadingOverlay();
            
            // Simulate AJAX request
            setTimeout(function() {
                hideLoadingOverlay();
                window.location.href = link.href;
            }, 1000);
        });
    });
}

/**
 * Show loading overlay
 */
function showLoadingOverlay() {
    var overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = `
        <div class="loading-spinner">
            <div class="preloader-wrapper big active">
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
            <p>Memuat...</p>
        </div>
    `;
    
    document.body.appendChild(overlay);
}

/**
 * Hide loading overlay
 */
function hideLoadingOverlay() {
    var overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.remove();
    }
}

/**
 * Initialize search enhancements
 */
function initSearchEnhancements() {
    var searchInputs = document.querySelectorAll('input[type="search"], .search-input');
    
    searchInputs.forEach(function(input) {
        // Add search icon
        if (!input.parentElement.querySelector('.search-icon')) {
            var icon = document.createElement('i');
            icon.className = 'material-icons search-icon';
            icon.textContent = 'search';
            input.parentElement.appendChild(icon);
        }
        
        // Add real-time search
        var timeout;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                performSearch(input.value);
            }, 300);
        });
        
        // Add search suggestions
        input.addEventListener('focus', function() {
            showSearchSuggestions(input);
        });
    });
}

/**
 * Perform search
 */
function performSearch(query) {
    if (query.length < 2) return;
    
    // Show loading state
    var searchResults = document.querySelector('.search-results');
    if (searchResults) {
        searchResults.innerHTML = '<div class="center-align"><div class="preloader-wrapper small active"><div class="spinner-layer spinner-blue-only"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div></div>';
    }
    
    // Simulate search request
    setTimeout(function() {
        if (searchResults) {
            searchResults.innerHTML = '<p>Hasil pencarian untuk: "' + query + '"</p>';
        }
    }, 500);
}

/**
 * Show search suggestions
 */
function showSearchSuggestions(input) {
    var suggestions = ['Berita Acara', 'Kendaraan', 'User Management', 'Laporan'];
    var suggestionList = document.createElement('ul');
    suggestionList.className = 'search-suggestions';
    
    suggestions.forEach(function(suggestion) {
        var li = document.createElement('li');
        li.textContent = suggestion;
        li.addEventListener('click', function() {
            input.value = suggestion;
            suggestionList.remove();
            performSearch(suggestion);
        });
        suggestionList.appendChild(li);
    });
    
    // Remove existing suggestions
    var existing = document.querySelector('.search-suggestions');
    if (existing) existing.remove();
    
    input.parentElement.appendChild(suggestionList);
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target)) {
            suggestionList.remove();
        }
    });
}

/**
 * Handle window resize
 */
function handleResize() {
    // Reinitialize responsive tables
    initResponsiveTables();
    
    // Update navigation
    var sidenav = document.querySelector('.sidenav');
    if (sidenav) {
        var instance = M.Sidenav.getInstance(sidenav);
        if (window.innerWidth > 992) {
            instance.close();
        }
    }
    
    // Update card layouts
    updateCardLayouts();
}

/**
 * Update card layouts
 */
function updateCardLayouts() {
    var cards = document.querySelectorAll('.card');
    
    cards.forEach(function(card) {
        if (window.innerWidth <= 600) {
            card.classList.add('mobile-card');
        } else {
            card.classList.remove('mobile-card');
        }
    });
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    var timeout;
    return function executedFunction() {
        var later = function() {
            clearTimeout(timeout);
            func.apply(this, arguments);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Utility function to check if device is mobile
 */
function isMobile() {
    return window.innerWidth <= 768;
}

/**
 * Utility function to check if device is tablet
 */
function isTablet() {
    return window.innerWidth > 768 && window.innerWidth <= 1024;
}

/**
 * Utility function to check if device is desktop
 */
function isDesktop() {
    return window.innerWidth > 1024;
}

// Add CSS for loading overlay
var style = document.createElement('style');
style.textContent = `
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-spinner {
    text-align: center;
    color: white;
}

.loading-spinner p {
    margin-top: 20px;
    font-size: 1.2rem;
}

.touching {
    transform: scale(0.95);
    opacity: 0.8;
}

.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    z-index: 1000;
    max-height: 200px;
    overflow-y: auto;
}

.search-suggestions li {
    padding: 10px 15px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.search-suggestions li:hover {
    background: #f5f5f5;
}

.search-suggestions li:last-child {
    border-bottom: none;
}

.search-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
}

@media only screen and (max-width: 600px) {
    .mobile-card {
        margin: 5px 0;
        border-radius: 0;
    }
    
    .responsive-table table td:before {
        content: attr(data-label) ": ";
        font-weight: bold;
        display: inline-block;
        width: 120px;
    }
}
`;
document.head.appendChild(style);
