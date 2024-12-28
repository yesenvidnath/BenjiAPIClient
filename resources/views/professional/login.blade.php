<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4b6cb7;
            --secondary-color: #182848;
            --accent-color: #5a67d8;
            --light-bg: #f7fafc;
        }

        body {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h4 {
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            margin-bottom: 1rem;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(90, 103, 216, 0.1);
        }

        .input-group-text {
            background: transparent;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
            margin-bottom: 0;
        }

        .btn-login {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--accent-color) 100%);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 0.75rem;
            width: 100%;
            font-weight: 500;
            margin-top: 1rem;
            transition: transform 0.2s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(90, 103, 216, 0.2);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .divider span {
            padding: 0 1rem;
            color: #718096;
            font-size: 0.875rem;
        }

        .social-login {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .social-btn {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: white;
            color: #4a5568;
            transition: all 0.2s ease;
        }

        .social-btn:hover {
            background: var(--light-bg);
            transform: translateY(-2px);
        }

        .forgot-password {
            text-align: center;
            margin-top: 1rem;
        }

        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.875rem;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h4>Professional</h4>
            <p class="text-muted">Welcome back! Please login to your account.</p>
        </div>

        <div class="social-login">
            <button class="social-btn">
                <i class="fab fa-google"></i>
            </button>
            <button class="social-btn">
                <i class="fab fa-facebook-f"></i>
            </button>
            <button class="social-btn">
                <i class="fab fa-twitter"></i>
            </button>
        </div>

        <div class="divider">
            <span>or login with email</span>
        </div>

        <form>
            <div class="input-group mb-3">
                <span class="input-group-text">
                    <i class="far fa-envelope"></i>
                </span>
                <input type="email" class="form-control" placeholder="Email address" required>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text">
                    <i class="fas fa-lock"></i>
                </span>
                <input type="password" class="form-control" placeholder="Password" required>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>

            <button type="submit" class="btn btn-login">Login</button>
        </form>

        <div class="forgot-password">
            <a href="#">Forgot Password?</a>
        </div>
    </div>
</body>
</html>
