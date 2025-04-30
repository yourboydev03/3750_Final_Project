(function(){

    const STORAGE_KEY = 'calendarTheme';
    const SELECTOR    = 'theme-select';
    const VALID       = ['light','dark','ocean','forest','cherry','midnight'];

    // Apply one of the VALID themes to <body>
    function applyTheme(theme) {
        VALID.forEach(function(t) {
            document.body.classList.toggle('theme-' + t, t === theme);
        });
    }
    
    // Get the current user’s theme from the server
    function getServerTheme() {
        return fetch('get_theme.php')
            .then(function(res) { return res.json(); })
            .then(function(json) {
                return (json.success && VALID.indexOf(json.theme) !== -1)
                    ? json.theme
                    : null;
            })
            .catch(function() { return null; });
    }
    
    // Save a new theme choice for the user
    function saveServerTheme(theme) {
        fetch('save_theme.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({theme: theme})
        }).catch(function(){});
    }
    
    document.addEventListener('DOMContentLoaded', function () {
        // Load server theme first, then localStorage, then default
        getServerTheme().then(function(serverTheme) {
            var theme = serverTheme || localStorage.getItem(STORAGE_KEY) || 'light';
            applyTheme(theme);
            localStorage.setItem(STORAGE_KEY, theme);
            
            var sel = document.getElementById(SELECTOR);
            if (sel) {
                sel.value = theme;
                sel.addEventListener('change', function (e) {
                    var t = e.target.value;
                    if (VALID.indexOf(t) === -1) return;
                    applyTheme(t);
                    localStorage.setItem(STORAGE_KEY, t);
                    saveServerTheme(t);
                });
            }
        });
    });
    
})();










