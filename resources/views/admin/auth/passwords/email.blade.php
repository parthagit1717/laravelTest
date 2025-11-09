<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Sash Admin</title>
    <!-- Include Inter font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Include Font Awesome for the input icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            --success-color: #4CAF50;
            --shadow-premium: 0 10px 40px rgba(0, 0, 0, 0.5); /* Stronger shadow for floating effect */
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--background-start) 0%, var(--background-end) 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: var(--text-light);
            overflow: hidden; 
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

        /* --- Auth Card (Glassmorphism) --- */
        .auth-card {
            background: var(--card-glass);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 16px;
            box-shadow: var(--shadow-premium);
            border: 1px solid var(--card-border);
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 10;
            transition: all 0.3s ease;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .auth-header h1 {
            margin: 0;
            font-size: 34px;
            font-weight: 800;
            color: var(--text-light);
        }
        
        .form-subtitle {
            font-size: 15px;
            color: var(--text-light);
            opacity: 0.7;
            margin-top: -15px;
            margin-bottom: 30px;
            line-height: 1.4;
        }

        /* --- Error & Status Messages --- */
        .message-container {
            margin-bottom: 25px;
            border-radius: 8px;
            padding: 12px 18px;
            font-size: 14px;
            line-height: 1.4;
        }

        .error-container {
            background-color: rgba(255, 112, 112, 0.1); /* Transparent red */
            border: 1px solid var(--error-color);
        }
        
        .error-container ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .error-container li {
            color: var(--error-color);
        }
        
        .status-success {
            background-color: rgba(76, 175, 80, 0.1);
            border: 1px solid var(--success-color);
            color: var(--success-color);
        }

        /* --- Form Group Styles --- */
        .form-group {
            margin-bottom: 25px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.left-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-placeholder);
            font-size: 16px;
            transition: color 0.3s;
        }

        /* --- Input fields (general) --- */
        input[type="email"] {
            width: 100%;
            padding: 15px 20px 15px 55px;
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
        
        .input-wrapper:focus-within i.left-icon {
            color: var(--primary-color);
        }

        /* --- Buttons and Links --- */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-hover));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.4);
            transition: all 0.2s ease-in-out;
            margin-bottom: 15px;
        }

        .btn-submit:hover {
            background: linear-gradient(90deg, var(--primary-hover), #437ab8);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 144, 226, 0.6);
        }
        
        .btn-submit:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.4);
        }
        
        .back-to-login a {
            font-size: 14px;
            color: var(--text-light);
            opacity: 0.6;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: color 0.3s, opacity 0.3s;
        }

        .back-to-login a:hover {
            color: var(--primary-color);
            opacity: 1;
        }
    </style>
</head>
<body>
    
    <!-- Background Orbs for premium visual effect -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    
    <div class="auth-card">
        <div class="auth-header">
            <h1>Forgot Password</h1>
        </div>

        <p class="form-subtitle">
            Enter your email and we will send you a link to reset your password.
        </p>

        <!-- Session Status (Success message after email is sent) -->
        @if (session('status'))
            <div class="message-container status-success">
                {{ session('status') }}
            </div>
        @endif
        
        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="message-container error-container">
                <ul>
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.password.email') }}">
            @csrf

            <!-- Email Input -->
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope left-icon"></i>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="Enter Email Address" required autofocus>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" class="btn-submit">
                    Send Reset Link
                </button>
            </div>
        </form>

        <div class="back-to-login">
            <a href="{{ route('admin.login') }}">Back to Admin Login</a>
        </div>
    </div>

</body>
</html>