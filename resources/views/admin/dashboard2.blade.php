<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Sash</title>
    <!-- Include Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- We're using simple inline CSS for this single-file demonstration -->
    <style>
        /* --- Root Variables (Default: Light Mode) --- */
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
            --primary-color: #4a90e2; /* Blue */
            --background-light: #f4f7f6;
            --text-dark: #333;
            --text-light: #fff;
            --sidebar-bg: #fff;
            --card-bg: #fff;
            --border-color: #eee;
            --nav-active-bg: #e0f7fa;
            --nav-hover-bg: #f7f7f7;
        }

        /* --- Dark Mode Overrides --- */
        .dark-mode {
            --background-light: #1e1e2d; /* Dark Background */
            --text-dark: #f0f0f0; /* Light Text */
            --sidebar-bg: #27293d; /* Darker Sidebar/Header */
            --card-bg: #27293d; /* Dark Card Background */
            --border-color: #3b3b54;
            --nav-active-bg: #353855;
            --nav-hover-bg: #3b3b54;
        }

        /* --- Base Styles --- */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--background-light);
            display: flex;
            min-height: 100vh;
            color: var(--text-dark);
            transition: background-color 0.3s, color 0.3s;
        }

        /* --- Sidebar Styles --- */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            position: fixed;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 20px 0;
            transition: width 0.3s, background-color 0.3s;
            border-right: 1px solid var(--border-color);
        }

        .logo {
            padding: 0 20px 20px 20px;
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 10px;
            transition: opacity 0.3s;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--text-dark);
            text-decoration: none;
            transition: background-color 0.2s, color 0.2s, padding 0.3s;
            font-size: 15px;
            border-left: 3px solid transparent;
        }

        .nav-link i {
            margin-right: 10px;
            font-size: 18px;
        }

        .nav-link.active {
            background-color: var(--nav-active-bg);
            color: var(--primary-color);
            font-weight: bold;
            border-left-color: var(--primary-color);
        }

        .nav-link:hover:not(.active) {
            background-color: var(--nav-hover-bg);
        }
        
        /* --- Main Content Area --- */
        .main-content {
            flex-grow: 1;
            margin-left: var(--sidebar-width); /* Space for the fixed sidebar */
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s;
        }
        
        /* --- Header Styles --- */
        .header {
            height: var(--header-height);
            background-color: var(--card-bg);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
            transition: background-color 0.3s;
        }

        .header-left i {
            font-size: 24px;
            cursor: pointer;
            color: var(--text-dark);
            transition: color 0.3s;
        }
        
        /* FIX: Ensure all elements in the header-right are vertically centered */
        .header-right {
            display: flex;
            align-items: center; 
        }
        /* --- Profile Dropdown Container --- */
        .profile-dropdown {
            position: relative; 
            margin-left: 20px;
        }

        .profile-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: var(--text-light);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 2px solid var(--primary-color);
            cursor: pointer; 
        }
        
        /* --- Dropdown Menu Styles --- */
        .dropdown-menu {
            position: absolute;
            top: calc(100% + 10px); 
            right: 0;
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            width: 180px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s, background-color 0.3s;
            border: 1px solid var(--border-color);
        }
        
        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-menu a, .dropdown-menu button {
            display: block;
            width: 100%;
            padding: 10px 15px;
            text-align: left;
            text-decoration: none;
            color: var(--text-dark);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 15px;
            transition: background-color 0.2s, color 0.3s;
            border-radius: 0;
        }
        
        .dropdown-menu a i, .dropdown-menu button i {
            margin-right: 8px;
        }

        .dropdown-menu a:hover, .dropdown-menu button:hover {
            background-color: var(--nav-hover-bg);
        }

        .dropdown-menu button {
            border-top: 1px solid var(--border-color);
            color: #e74c3c; /* Logout color */
        }

        .dark-mode .dropdown-menu button {
            color: #ff8b8b;
        }


        /* --- Page Content Styles --- */
        .page-content {
            padding: 30px;
            flex-grow: 1;
        }

        .page-content h2 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 28px;
            font-weight: normal;
        }

        /* --- Welcome Card Styles --- */
        .welcome-card {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .welcome-card h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 500;
        }

        .edit-button {
            background-color: #2ecc71; /* Green color */
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
        }

        .edit-button:hover {
            background-color: #27ae60;
        }

        .edit-button i {
            margin-right: 5px;
            font-size: 16px;
        }
        
        /* Moon/Sun Icon Styling */
        .theme-toggle i {
            font-size: 20px; 
            margin-right: 20px; 
            cursor: pointer;
            color: var(--text-dark);
            transition: color 0.3s;
        }

        /* --- Modal Styles --- */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s, visibility 0.3s;
        }

        .modal-overlay.show {
            visibility: visible;
            opacity: 1;
        }

        .modal-content {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 400px;
            transform: scale(0.95);
            transition: transform 0.3s;
            color: var(--text-dark);
        }

        .modal-overlay.show .modal-content {
            transform: scale(1);
        }

        .modal-content h3 {
            margin-top: 0;
            color: #e74c3c; /* Red color for warning */
            display: flex;
            align-items: center;
            font-size: 20px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .modal-content h3 i {
            margin-right: 10px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .modal-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.2s;
        }

        .btn-cancel {
            background-color: #bdc3c7;
            color: var(--text-dark);
        }

        .btn-cancel:hover {
            background-color: #95a5a6;
        }

        .btn-logout-confirm {
            background-color: #e74c3c;
            color: var(--text-light);
        }

        .btn-logout-confirm:hover {
            background-color: #c0392b;
        }

        .dark-mode .btn-cancel {
            background-color: #3b3b54;
            color: #f0f0f0;
        }
        .dark-mode .btn-cancel:hover {
            background-color: #4c4f6f;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">Welcome Admin</div>
        <nav>
            <a href="{{ route('admin.dashboard') }}" class="nav-link active">
                <i class="fa-solid fa-gauge-high"></i> 
                Dashboard
            </a>
            <a href="#" class="nav-link">
                <i class="fa-solid fa-box"></i>
                Manage Product
            </a>
            <a href="#" class="nav-link">
                <i class="fa-solid fa-users"></i>
                Users
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <!-- Hamburger Menu Icon -->
                <i class="fa-solid fa-bars"></i>
            </div>
            <div class="header-right">
                <!-- Theme/Mode toggle icon (Moon/Sun) -->
                <div class="theme-toggle" id="theme-toggle">
                    <!-- Icon will be set dynamically by JavaScript -->
                    <i class="fa-regular fa-moon"></i>
                </div>
                
                <!-- Profile Dropdown Container -->
                <div class="profile-dropdown" id="profile-dropdown-container">
                    <!-- Profile Avatar (Clickable) -->
                    <div class="profile-icon" id="profile-icon">
                        <!-- Get the first letter of the authenticated user's name -->
                        {{ strtoupper(substr(Auth::guard('admin')->user()->name, 0, 1)) }}
                    </div>
                    
                    <!-- Dropdown Menu -->
                    <div class="dropdown-menu" id="dropdown-menu">
                        <!-- Edit Profile Link -->
                        <a href="#">
                            <i class="fa-solid fa-user-pen"></i> Edit Profile
                        </a>
                        
                        <!-- Logout Button: Calls showLogoutModal() instead of direct submission -->
                        <button onclick="showLogoutModal();">
                            <i class="fa-solid fa-right-from-bracket"></i> Logout
                        </button>
                    </div>

                    <!-- Logout Form (Hidden) -->
                    <form method="POST" action="{{ route('admin.logout') }}" style="display: none;" id="logout-form">
                        @csrf
                    </form>

                </div>

            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Dashboard</h2>
                <span style="font-size: 14px; color: #999;">Dashboard</span>
            </div>

            <!-- Welcome Card -->
            <div class="welcome-card">
                <h3>Welcome, {{ Auth::guard('admin')->user()->name }}</h3>
                <a href="#" class="edit-button">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit Profile
                </a>
            </div>
            
            <!-- Placeholder for additional dashboard content -->
            <div style="margin-top: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div class="welcome-card" style="padding: 20px;">Statistics Card 1</div>
                <div class="welcome-card" style="padding: 20px;">Statistics Card 2</div>
                <div class="welcome-card" style="padding: 20px;">Report Summary</div>
            </div>

        </div>
    </div>

    <!-- Logout Confirmation Modal (Custom Alert) -->
    <div class="modal-overlay" id="logout-modal">
        <div class="modal-content">
            <h3><i class="fa-solid fa-triangle-exclamation"></i> Confirm Logout</h3>
            <p>Are you sure you want to log out of the Admin Dashboard?</p>
            <div class="modal-actions">
                <button class="modal-btn btn-cancel" id="cancel-logout">Cancel</button>
                <button class="modal-btn btn-logout-confirm" id="confirm-logout">Yes, Log Out</button>
            </div>
        </div>
    </div>


    <script>
        // --- Dark Mode Logic ---
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;
        const themeIcon = themeToggle.querySelector('i');

        function enableDarkMode() {
            body.classList.add('dark-mode');
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
            localStorage.setItem('theme', 'dark');
        }

        function disableDarkMode() {
            body.classList.remove('dark-mode');
            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
            localStorage.setItem('theme', 'light');
        }

        // 1. Check local storage on load
        const storedTheme = localStorage.getItem('theme');
        if (storedTheme === 'dark') {
            enableDarkMode();
        } else {
            disableDarkMode();
        }

        // 2. Add toggle listener
        themeToggle.addEventListener('click', () => {
            if (body.classList.contains('dark-mode')) {
                disableDarkMode();
            } else {
                enableDarkMode();
            }
        });


        // --- Sidebar Toggle Script ---
        document.querySelector('.header-left i').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            // Define the collapsed width
            const collapsedWidth = '60px';
            const expandedWidth = 'var(--sidebar-width)';
            
            // Check if sidebar is collapsed (use getComputedStyle for accurate check)
            if (getComputedStyle(sidebar).width === collapsedWidth) { 
                // Expand sidebar
                sidebar.style.width = expandedWidth;
                mainContent.style.marginLeft = expandedWidth;
                // Re-enable text if collapsed
                document.querySelectorAll('.sidebar .nav-link').forEach(link => link.style.paddingLeft = '20px');
                document.querySelector('.logo').style.opacity = '1';

            } else {
                // Collapse sidebar
                sidebar.style.width = collapsedWidth;
                mainContent.style.marginLeft = collapsedWidth;
                // Hide text/adjust padding
                document.querySelectorAll('.sidebar .nav-link').forEach(link => link.style.paddingLeft = '18px');
                document.querySelector('.logo').style.opacity = '0';
            }
        });

        // --- Dropdown Toggle Script ---
        const profileIcon = document.getElementById('profile-icon');
        const dropdownMenu = document.getElementById('dropdown-menu');

        // Toggle dropdown visibility when the profile icon is clicked
        profileIcon.addEventListener('click', (e) => {
            e.stopPropagation(); 
            dropdownMenu.classList.toggle('show');
            // Ensure modal is closed when dropdown is opened/closed
            hideLogoutModal(); 
        });

        // Close the dropdown if the user clicks anywhere outside of it
        document.addEventListener('click', (e) => {
            const dropdownContainer = document.getElementById('profile-dropdown-container');
            if (dropdownMenu.classList.contains('show') && !dropdownContainer.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });

        // --- Logout Modal Logic (Replacing alert/confirm) ---
        const logoutModal = document.getElementById('logout-modal');
        const confirmLogoutBtn = document.getElementById('confirm-logout');
        const cancelLogoutBtn = document.getElementById('cancel-logout');
        const logoutForm = document.getElementById('logout-form');

        // Function to display the modal
        function showLogoutModal() {
            logoutModal.classList.add('show');
            // Close the profile dropdown immediately when modal shows
            dropdownMenu.classList.remove('show'); 
        }

        // Function to hide the modal
        function hideLogoutModal() {
            logoutModal.classList.remove('show');
        }

        // Handler for the "Yes, Log Out" button in the modal
        confirmLogoutBtn.addEventListener('click', () => {
            // Submit the hidden form to trigger logout
            logoutForm.submit();
            hideLogoutModal(); 
        });

        // Handler for the "Cancel" button in the modal
        cancelLogoutBtn.addEventListener('click', hideLogoutModal);

        // Close modal if user clicks outside the content (on the overlay)
        logoutModal.addEventListener('click', (e) => {
            if (e.target.id === 'logout-modal') {
                hideLogoutModal();
            }
        });

    </script>
</body>
</html>