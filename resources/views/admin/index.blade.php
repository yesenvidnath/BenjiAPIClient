<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #2c3e50;
            color: white;
        }
        .nav-link {
            color: #ecf0f1;
            padding: 15px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .nav-link:hover {
            background: #34495e;
            color: white;
        }
        .nav-link.active {
            background: #3498db;
            color: white;
        }
        .main-content {
            background: #f5f6fa;
            min-height: 100vh;
        }
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .toggle-button {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .toggle-button input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #2196F3;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
    </style>
</head>
<body>
    <!-- Check Authentication -->
    <script>
        // Check if token exists
        const token = localStorage.getItem('api_token');
        if (!token) {
            window.location.href = '/login';
        }
    </script>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3">
                    <h4 class="text-center mb-4">Admin Panel</h4>
                    <div id="userInfo" class="text-center mb-4">
                        <!-- User info will be populated here -->
                    </div>
                    <div class="nav flex-column">
                        <a href="#home" class="nav-link active" data-bs-toggle="pill">
                            <i class="fas fa-home me-2"></i> Home
                        </a>
                        <a href="#professionals" class="nav-link" data-bs-toggle="pill">
                            <i class="fas fa-user-tie me-2"></i> Professionals
                        </a>
                        <a href="#customers" class="nav-link" data-bs-toggle="pill">
                            <i class="fas fa-users me-2"></i> Customers
                        </a>
                        <a href="#notifications" class="nav-link" data-bs-toggle="pill">
                            <i class="fas fa-bell me-2"></i> Notifications
                        </a>
                        <a href="#payments" class="nav-link" data-bs-toggle="pill">
                            <i class="fas fa-credit-card me-2"></i> Payments
                        </a>
                        <a href="#bot" class="nav-link" data-bs-toggle="pill">
                            <i class="fas fa-robot me-2"></i> Manage Bot
                        </a>
                        <a href="#meetings" class="nav-link" data-bs-toggle="pill">
                            <i class="fas fa-calendar me-2"></i> Meetings
                        </a>
                        <a href="#requests" class="nav-link" data-bs-toggle="pill">
                            <i class="fas fa-tasks me-2"></i> Requests
                        </a>
                        <a href="#profile" class="nav-link" data-bs-toggle="pill">
                            <i class="fas fa-user me-2"></i> Profile
                        </a>
                        <a href="#" onclick="handleLogout()" class="nav-link">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content p-4">
                <div class="tab-content">
                    <!-- Home Tab -->
                    <div class="tab-pane fade show active" id="home">
                        <h2 class="mb-4">Dashboard Overview</h2>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <h5>Total Users</h5>
                                    <h2 id="totalUsers">Loading...</h2>
                                    <p class="text-success" id="usersGrowth">Loading...</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <h5>Revenue</h5>
                                    <h2 id="totalRevenue">Loading...</h2>
                                    <p class="text-success" id="revenueGrowth">Loading...</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <h5>Active Sessions</h5>
                                    <h2 id="activeSessions">Loading...</h2>
                                    <p id="sessionsGrowth">Loading...</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <h5>Pending Requests</h5>
                                    <h2 id="pendingRequests">Loading...</h2>
                                    <p id="requestsGrowth">Loading...</p>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <canvas id="usersChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Tab -->
                    <div class="tab-pane fade" id="profile">
                        <h2>Admin Profile</h2>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <img id="profileImage" src="https://via.placeholder.com/150" class="rounded-circle mb-3" alt="Profile">
                                        <h4 id="profileName">Loading...</h4>
                                        <p class="text-muted" id="profileRole">Loading...</p>
                                    </div>
                                    <div class="col-md-8">
                                        <h5>Profile Details</h5>
                                        <hr>
                                        <div class="row mb-3">
                                            <div class="col-md-4">Email:</div>
                                            <div class="col-md-8" id="profileEmail">Loading...</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">Phone:</div>
                                            <div class="col-md-8" id="profilePhone">Loading...</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">Last Login:</div>
                                            <div class="col-md-8" id="lastLogin">Loading...</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Fetch user profile data
        async function fetchUserProfile() {
            try {
                const token = localStorage.getItem('api_token');
                const response = await fetch('http://127.0.0.1:8000/api/user-management-service/auth/profile/me', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch profile');
                }

                const data = await response.json();
                updateProfileUI(data);
            } catch (error) {
                console.error('Error fetching profile:', error);
                handleAuthError();
            }
        }

        // Update UI with profile data
        function updateProfileUI(data) {
            // Update sidebar user info
            document.getElementById('userInfo').innerHTML = `
                <img src="${data.avatar || 'https://via.placeholder.com/50'}" class="rounded-circle mb-2" width="50" height="50">
                <div class="text-white">${data.name}</div>
                <small class="text-light">${data.role}</small>
            `;

            // Update profile tab
            document.getElementById('profileName').textContent = data.name;
            document.getElementById('profileRole').textContent = data.role;
            document.getElementById('profileEmail').textContent = data.email;
            document.getElementById('profilePhone').textContent = data.phone || 'Not provided';
            document.getElementById('lastLogin').textContent = new Date(data.last_login).toLocaleString();
            if (data.avatar) {
                document.getElementById('profileImage').src = data.avatar;
            }
        }

        // Handle authentication errors
        function handleAuthError() {
            localStorage.removeItem('api_token');
            window.location.href = '/login';
        }

        // Handle logout
        function handleLogout() {
            localStorage.removeItem('api_token');
            window.location.href = '/login';
        }

        // Initialize charts
        function initializeCharts() {
            // Revenue Chart
            const revenueChart = new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Revenue',
                        data: [30000, 35000, 32000, 40000, 38000, 45000],
                        borderColor: '#3498db',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Monthly Revenue'
                        }
                    }
                }
            });

            // Users Chart
            const usersChart = new Chart(document.getElementById('usersChart'), {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'New Users',
                        data: [120, 150, 180, 90, 200, 220],
                        backgroundColor: '#2ecc71'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'New Users per Month'
                        }
                    }
                }
            });
        }

        // Initialize tooltips
        function initializeTooltips() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        }

        // On page load
        document.addEventListener('DOMContentLoaded', function() {
            fetchUserProfile();
            initializeCharts();
            initializeTooltips();
        });
    </script>
</body>
</html>
