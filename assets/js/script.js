// Initialize Materialize components
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar initialization
    var elems = document.querySelectorAll('.sidenav');
    var instances = M.Sidenav.init(elems);
    
    // Dropdown initialization
    var dropdowns = document.querySelectorAll('.dropdown-trigger');
    M.Dropdown.init(dropdowns, {
        coverTrigger: false
    });
    
    // Select initialization
    var selects = document.querySelectorAll('select');
    M.FormSelect.init(selects);
    
    // Datepicker initialization
    var datepickers = document.querySelectorAll('.datepicker');
    M.Datepicker.init(datepickers, {
        format: 'yyyy-mm-dd',
        autoClose: true
    });
    
    // Modal initialization
    var modals = document.querySelectorAll('.modal');
    M.Modal.init(modals);
    
    // Collapsible initialization
    var collapsibles = document.querySelectorAll('.collapsible');
    M.Collapsible.init(collapsibles);
    
    // Tooltip initialization
    var tooltips = document.querySelectorAll('.tooltipped');
    M.Tooltip.init(tooltips);
    
    // Character counter
    var textNeedCount = document.querySelectorAll('#keterangan');
    M.CharacterCounter.init(textNeedCount);
    
    // Auto hide alert message
    setTimeout(function() {
        var alert = document.getElementById('alert-message');
        if (alert) {
            alert.style.display = 'none';
        }
    }, 5000);
});

// Button collapse sidebar for mobile
$(document).ready(function(){
    $('.button-collapse').sideNav();
});

// Search functionality
function searchTable() {
    var input, filter, table, tr, td, i, j, txtValue, found;
    input = document.getElementById("searchInput");
    filter = input.value.toUpperCase();
    table = document.getElementById("tbl");
    tr = table.getElementsByTagName("tr");
    
    for (i = 1; i < tr.length; i++) { // Start from 1 to skip header
        found = false;
        td = tr[i].getElementsByTagName("td");
        
        for (j = 0; j < td.length; j++) {
            if (td[j]) {
                txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        if (found) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}

// Add event listener to search input
document.getElementById('searchInput').addEventListener('keyup', searchTable);

// Confirm before delete
function confirmDelete() {
    return confirm('Apakah Anda yakin ingin menghapus data ini?');
}

// Format NIP
function formatNIP(nip) {
    if (!nip) return '';
    return nip.replace(/(\d{8})(\d{6})(\d{1})(\d{3})/, '$1 $2 $3 $4');
}

// Initialize all formatted NIPs on page load
document.addEventListener('DOMContentLoaded', function() {
    var nips = document.querySelectorAll('.format-nip');
    nips.forEach(function(el) {
        el.textContent = formatNIP(el.textContent);
    });
});