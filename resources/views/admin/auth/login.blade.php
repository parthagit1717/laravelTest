<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Sash</title>
    <!-- Include Inter font from Google Fonts --><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Include Font Awesome for the input icons --><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- Color Variables --- */
        :root {
            --primary-color: #4a90e2; /* Blue */
            --primary-hover: #3a7bd5;
            --background-start: #1b212c; /* Deep Slate Blue */
            --background-end: #0a0e14; /* Near Black */
            --card-glass: rgba(255, 255, 255, 0.08); /* Transparent white for glass effect */
            --card-border: rgba(255, 255, 255, 0.1);
            --text-light: #f0f0f0;
            --text-placeholder: #a0a0a0;
            --input-bg: rgba(0, 0, 0, 0.2);
            --error-color: #ff7070;
            --shadow-premium: 0 10px 40px rgba(0, 0, 0, 0.5); /* Stronger shadow for floating effect */
        }

        body {
            font-family: 'Inter', sans-serif;
            /* Deep, dark gradient background */
            background: linear-gradient(135deg, var(--background-start) 0%, var(--background-end) 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: var(--text-light);
            overflow: hidden; /* Prevent scrollbar from background effects */
        }

        /* --- Background Effect: Subtle glow/orb --- */
        .orb {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.4;
        }
        .orb-1 { background-color: var(--primary-color); top: 15%; left: 10%; }
        .orb-2 { background-color: #ff5722; bottom: 10%; right: 5%; opacity: 0.3; }


        /* --- Login Card (Glassmorphism) --- */
        .login-card {
            background: var(--card-glass);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 16px; /* Slightly larger radius */
            box-shadow: var(--shadow-premium);
            border: 1px solid var(--card-border); /* Subtle light border */
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 10; /* Keep it above the orbs */
            transition: all 0.3s ease;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h1 {
            margin: 0;
            font-size: 34px;
            font-weight: 800;
            color: var(--text-light);
        }
        
        .logo-text {
            color: var(--primary-color);
            font-weight: 700;
            display: block;
            margin-top: 5px;
            font-size: 15px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* --- Error Messages --- */
        .error-container {
            margin-bottom: 25px;
            background-color: rgba(231, 76, 60, 0.1); /* Transparent red */
            border: 1px solid var(--error-color);
            border-radius: 8px;
            padding: 12px 18px;
        }

        .error-container ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .error-container li {
            color: var(--error-color);
            font-size: 14px;
        }

        /* --- Form Group Styles --- */
        .form-group {
            margin-bottom: 25px;
        }

        .input-wrapper {
            position: relative;
        }

        /* General Input Icons (Left side) */
        .input-wrapper i.left-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-placeholder);
            font-size: 16px;
            transition: color 0.3s;
        }

        /* New: Toggle Icon (Right side) */
        .input-wrapper i.toggle-icon {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-placeholder);
            font-size: 16px;
            cursor: pointer; /* Important for usability */
            transition: color 0.3s;
        }
        
        .input-wrapper i.toggle-icon:hover {
            color: var(--primary-color);
        }

        label {
            display: none; 
        }

        /* --- Input fields (general) --- */
        input[type="email"],
        input[type="password"] {
            width: 100%;
            /* Left padding for the left icon is 55px. */
            /* INCREASED right padding to 65px for extra clearance around the toggle icon */
            padding: 15px 65px 15px 55px; 
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            font-size: 16px;
            box-sizing: border-box;
            background-color: var(--input-bg);
            color: var(--text-light);
            transition: border-color 0.3s, background-color 0.3s;
        }
        
        input::placeholder {
            color: var(--text-placeholder);
            opacity: 0.7;
        }

        input:focus {
            border-color: var(--primary-color);
            background-color: rgba(0, 0, 0, 0.4);
            outline: none;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.4);
        }
        
        /* Change icon color on focus */
        .input-wrapper:focus-within i.left-icon {
            color: var(--primary-color);
        }

        /* --- Checkbox and Remember Me --- */
        .remember-me {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .remember-me label {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: var(--text-light);
            opacity: 0.8;
            cursor: pointer;
        }

        input[type="checkbox"] {
            margin-right: 8px;
            accent-color: var(--primary-color);
            transform: scale(1.1);
        }

        /* --- Buttons and Links --- */
        .btn-login {
            width: 100%;
            padding: 14px;
            /* Gradient for the button */
            background: linear-gradient(90deg, var(--primary-color), var(--primary-hover));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.4);
            transition: all 0.2s ease-in-out;
        }

        .btn-login:hover {
            background: linear-gradient(90deg, var(--primary-hover), #437ab8);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 144, 226, 0.6);
        }
        
        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.4);
        }

        .forgot-password-link a {
            font-size: 14px;
            color: var(--text-light);
            opacity: 0.6;
            text-decoration: none;
            transition: color 0.3s, opacity 0.3s;
        }

        .forgot-password-link a:hover {
            color: var(--primary-color);
            opacity: 1;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    
    <!-- Background Orbs for premium visual effect --><div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    
    <div class="login-card">
        <div class="login-header">
            <h1>Admin Login</h1>
            <!-- <span class="logo-text">sash panel</span> -->
        </div>

        <!-- Error Handling -->@if ($errors->any())
            <div class="error-container">
                <ul>
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <!-- Email Input --><div class="form-group">
                <label>Email</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope left-icon"></i>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter Email Address" required autofocus>
                </div>
            </div>

            <!-- Password Input --><div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <!-- Left lock icon --><i class="fa-solid fa-lock left-icon"></i> 
                    
                    <!-- Password Input Field (Added ID) --><input type="password" name="password" id="password-field" placeholder="Enter Password" required>
                    
                    <!-- Right toggle icon (Added ID and toggle class) --><!-- <i class="fa-solid fa-eye-slash toggle-icon" id="password-toggle"></i> -->
                </div>
            </div>

            <!-- Remember Me & Forgot Password Link --><div class="remember-me">
                <!-- <label>
                    <input type="checkbox" name="remember"> Keep me logged in
                </label> -->
                <div class="forgot-password-link">
                    <a href="{{ route('admin.password.request') }}">Forgot Password?</a>
                </div>
            </div>

            <!-- Login Button --><div class="form-group">
                <button type="submit" class="btn-login">
                    Sign In Securely
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password-field');
            const passwordToggle = document.getElementById('password-toggle');

            if (passwordToggle && passwordField) {
                // Attach click listener to the eye icon
                passwordToggle.addEventListener('click', function() {
                    // Determine the new input type (password -> text, or text -> password)
                    const newType = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    
                    // Set the new input type
                    passwordField.setAttribute('type', newType);
                    
                    // Toggle the icon class (fa-eye-slash for hidden, fa-eye for shown)
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>
</body>
</html>