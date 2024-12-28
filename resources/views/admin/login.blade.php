<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f6fa;
            background-image: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            padding: 0;
        }
        .login-header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }
        .login-header h3 {
            margin: 0;
            font-size: 24px;
        }
        .login-body {
            padding: 30px;
        }
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        .form-control {
            height: 50px;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            border-color: #3498db;
        }
        .login-btn {
            background: #2c3e50;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 16px;
            font-weight: 500;
            width: 100%;
            margin-top: 20px;
            color: white;
            height: 50px;
        }
        .login-btn:hover {
            background: #34495e;
        }
        .forgot-password {
            text-align: right;
            margin-top: 15px;
        }
        .forgot-password a {
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
        }
        .login-footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }
        .password-field {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }
        .form-label {
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
        .remember-me {
            margin-top: 15px;
        }
        .alert {
            margin-bottom: 20px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h3><i class="fas fa-lock me-2"></i>Admin Login</h3>
        </div>

        <div class="login-body">
            <div class="alert alert-danger d-none" id="errorAlert" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <span id="errorMessage">Invalid credentials. Please try again.</span>
            </div>

            <form id="loginForm" onsubmit="return handleLogin(event)">
                <div class="form-group">
                    <label class="form-label">Email address</label>
                    <input type="email"
                           class="form-control"
                           id="emailInput"
                           placeholder="Enter your email"
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="password-field">
                        <input type="password"
                               class="form-control"
                               id="passwordInput"
                               placeholder="Enter your password"
                               required>
                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="passwordToggleIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="remember-me">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">
                            Remember me
                        </label>
                    </div>
                </div>

                <button type="submit" class="login-btn">
                    <span class="btn-text"><i class="fas fa-sign-in-alt me-2"></i>Login</span>
                </button>

                <div class="forgot-password">
                    <a href="#" onclick="showForgotPassword()">Forgot Password?</a>
                </div>
            </form>
        </div>

        <div class="login-footer">
            <p class="mb-0"><i class="fas fa-shield-alt me-2"></i>Protected by SSL Security</p>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="resetPasswordForm">
                        <div class="form-group">
                            <label class="form-label">Email address</label>
                            <input type="email"
                                   class="form-control"
                                   id="resetEmail"
                                   placeholder="Enter your email"
                                   required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="handleResetPassword()">Send Reset Link</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Check if user is already logged in
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('api_token');
            if (token) {
                window.location.href = '/admin/dashboard';
            }
        });

        // Handle login form submission
        async function handleLogin(event) {
            event.preventDefault();

            const loginBtn = document.querySelector('.login-btn');
            const btnText = loginBtn.querySelector('.btn-text');
            const errorAlert = document.getElementById('errorAlert');
            const errorMessage = document.getElementById('errorMessage');
            const email = document.getElementById('emailInput').value;
            const password = document.getElementById('passwordInput').value;
            const rememberMe = document.getElementById('rememberMe').checked;

            // Show loading state
            btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Logging in...';
            loginBtn.disabled = true;
            errorAlert.classList.add('d-none');

            try {
                const response = await fetch('http://127.0.0.1:8000/api/user-management-service/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        email: email,
                        password: password,
                        remember: rememberMe
                    })
                });

                const data = await response.json();

                if (response.ok && data.token) {
                    // Store token and redirect
                    localStorage.setItem('api_token', data.token);
                    window.location.href = '/admin/dashboard';
                } else {
                    // Show error message
                    errorMessage.textContent = data.message || 'Invalid credentials. Please try again.';
                    errorAlert.classList.remove('d-none');

                    // Reset button state
                    btnText.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Login';
                    loginBtn.disabled = false;

                    // Hide error after 3 seconds
                    setTimeout(() => {
                        errorAlert.classList.add('d-none');
                    }, 3000);
                }
            } catch (error) {
                console.error('Login error:', error);
                errorMessage.textContent = 'Network error. Please try again later.';
                errorAlert.classList.remove('d-none');

                // Reset button state
                btnText.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Login';
                loginBtn.disabled = false;
            }
        }

        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const passwordToggleIcon = document.getElementById('passwordToggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggleIcon.classList.remove('fa-eye');
                passwordToggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordToggleIcon.classList.remove('fa-eye-slash');
                passwordToggleIcon.classList.add('fa-eye');
            }
        }

        // Show forgot password modal
        function showForgotPassword() {
            const modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
            modal.show();
        }

        // Handle reset password request
        function handleResetPassword() {
            const resetEmail = document.getElementById('resetEmail').value;
            const modalFooterBtns = document.querySelectorAll('.modal-footer button');

            modalFooterBtns.forEach(btn => btn.disabled = true);

            // Implement your password reset logic here
            setTimeout(() => {
                alert(`Password reset link sent to ${resetEmail}`);
                bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal')).hide();
                modalFooterBtns.forEach(btn => btn.disabled = false);
                document.getElementById('resetPasswordForm').reset();
            }, 1000);
        }
    </script>
</body>
</html>
