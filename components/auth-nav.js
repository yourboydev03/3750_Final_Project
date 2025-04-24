(function() {
    // Helper to read cookie by name
    function getCookie(name) {
        const m = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return m ? decodeURIComponent(m[2]) : null;
    }
    
    // Helper to erase cookie
    function eraseCookie(name) {
        document.cookie = name + '=; Max-Age=0; path=/Assignments/Final_Project/';
    }
    
    const nav = document.querySelector('ul.auth-nav');
    nav.innerHTML = ''; // clear existing items
    
    const isLoggedIn = getCookie('calendar_loggedIn') === 'true';
    
    if (isLoggedIn) {
        // Fetch profile pic
        fetch('get_profile.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                  // Add avatar
                  const liAvatar = document.createElement('li');
                  const a = document.createElement('a');
                  a.href = 'profile.php';
                  const img = document.createElement('img');
                  img.src = data.profile_pic;
                  img.className = 'avatar';
                  a.appendChild(img);
                  liAvatar.appendChild(a);
                  nav.appendChild(liAvatar);
                }
                 // Add logout link
                const liLogout = document.createElement('li');
                liLogout.innerHTML = '<a href="#" id="logout-btn">Logout</a>';
                nav.appendChild(liLogout);
                
                document.getElementById('logout-btn')
                    .addEventListener('click', function(e) {
                        e.preventDefault();
                        eraseCookie('calendar_loggedIn');
                        // Clear PHP session
                        fetch('logout.php').then(() => {
                            window.location.href = 'login.html';
                        });
                    });
            });
    } else {
        // Not logged in: show Login and Register
        const liLogin = document.createElement('li');
        liLogin.innerHTML = '<a href="login.html">Login</a>';
        nav.appendChild(liLogin);
        
        const liReg = document.createElement('li');
        liReg.innerHTML = '<a href="register.html">Register</a>';
        nav.appendChild(liReg);
    }
})();








