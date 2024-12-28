<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4b6cb7;
            --secondary-color: #182848;
            --accent-color: #5a67d8;
            --success-color: #48bb78;
            --warning-color: #f6ad55;
            --danger-color: #fc8181;
            --light-bg: #f7fafc;
        }

        body {
            background: var(--light-bg);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .sidebar {
            background: linear-gradient(180deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            min-height: 100vh;
            color: white;
            padding-top: 20px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 12px 20px;
            margin: 4px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: var(--accent-color);
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .main-content {
            padding: 30px;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .status-online {
            background-color: var(--success-color);
            box-shadow: 0 0 0 3px rgba(72, 187, 120, 0.2);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .chart-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            min-height: 400px;
        }

        .meeting-card {
            border-left: 4px solid var(--accent-color);
        }

        .notification-badge {
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            padding: 0.25rem 0.6rem;
            font-size: 0.8rem;
            position: absolute;
            top: -5px;
            right: -5px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .top-bar {
            background: white;
            padding: 15px 30px;
            border-bottom: 1px solid #edf2f7;
            margin-bottom: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        }

        .search-input {
            border-radius: 20px;
            padding-left: 40px;
            background: var(--light-bg);
            border: none;
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
        }

        .meeting-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
        }

        .status-upcoming {
            background: rgba(90, 103, 216, 0.1);
            color: var(--accent-color);
        }

        .status-completed {
            background: rgba(72, 187, 120, 0.1);
            color: var(--success-color);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="text-center mb-4">
                    <h4>Professional</h4>
                    <div class="mt-2">
                        <span class="status-indicator status-online"></span>
                        <span>Online</span>
                    </div>
                </div>
                <div class="nav flex-column">
                    <a href="#dashboard" class="nav-link active" data-bs-toggle="pill">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                    <a href="#meetings" class="nav-link" data-bs-toggle="pill">
                        <i class="fas fa-calendar-alt me-2"></i> My Meetings
                    </a>
                    <a href="#notifications" class="nav-link position-relative" data-bs-toggle="pill">
                        <i class="fas fa-bell me-2"></i> Notifications
                        <span class="notification-badge">3</span>
                    </a>
                    <a href="#profile" class="nav-link" data-bs-toggle="pill">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="top-bar d-flex justify-content-between align-items-center">
                    <div class="position-relative">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="form-control search-input" placeholder="Search...">
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <i class="fas fa-bell fs-5"></i>
                        </div>
                        <img src="https://via.placeholder.com/40" alt="User" class="user-avatar">
                    </div>
                </div>

                <div class="main-content">
                    <div class="tab-content">
                        <!-- Dashboard Tab -->
                        <div class="tab-pane fade show active" id="dashboard">
                            <h4 class="mb-4">Dashboard Overview</h4>

                            <!-- Stats Row -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="stat-card">
                                        <h6 class="text-muted">Total Income</h6>
                                        <h3>$24,500</h3>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-arrow-up text-success me-1"></i>
                                            <span class="text-success">8.5%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stat-card">
                                        <h6 class="text-muted">Total Expenses</h6>
                                        <h3>$12,300</h3>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-arrow-down text-danger me-1"></i>
                                            <span class="text-danger">2.3%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stat-card">
                                        <h6 class="text-muted">Total Meetings</h6>
                                        <h3>145</h3>
                                        <div class="progress mt-2">
                                            <div class="progress-bar bg-success" style="width: 75%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stat-card">
                                        <h6 class="text-muted">Success Rate</h6>
                                        <h3>92%</h3>
                                        <div class="progress mt-2">
                                            <div class="progress-bar bg-primary" style="width: 92%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Charts Row -->
                            <div class="row mt-4">
                                <div class="col-md-8">
                                    <div class="chart-card">
                                        <h5 class="mb-4">Income vs Expenses</h5>
                                        <canvas id="financialChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="chart-card">
                                        <h5 class="mb-4">Expense Categories</h5>
                                        <canvas id="expensesPieChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Meetings Tab -->
                        <div class="tab-pane fade" id="meetings">
                            <h4 class="mb-4">My Meetings</h4>
                            <div class="card meeting-card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5>Client Meeting - Project Review</h5>
                                            <p class="text-muted mb-0">
                                                <i class="far fa-clock me-2"></i>10:00 AM - 11:30 AM
                                            </p>
                                        </div>
                                        <span class="meeting-status status-upcoming">Upcoming</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Add more meeting cards here -->
                        </div>

                        <!-- Notifications Tab -->
                        <div class="tab-pane fade" id="notifications">
                            <h4 class="mb-4">Notifications</h4>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="fas fa-info-circle text-primary fs-4"></i>
                                        </div>
                                        <div>
                                            <h6>New Meeting Request</h6>
                                            <p class="text-muted mb-0">You have a new meeting request from John Doe</p>
                                            <small class="text-muted">2 hours ago</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Add more notification cards here -->
                        </div>

                        <!-- Profile Tab -->
                        <div class="tab-pane fade" id="profile">
                            <h4 class="mb-4">My Profile</h4>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 text-center">
                                            <img src="https://via.placeholder.com/150" class="rounded-circle mb-3" alt="Profile">
                                            <h5>John Doe</h5>
                                            <p class="text-muted">Professional</p>
                                        </div>
                                        <div class="col-md-8">
                                            <form>
                                                <div class="mb-3">
                                                    <label class="form-label">Full Name</label>
                                                    <input type="text" class="form-control" value="John Doe">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" class="form-control" value="john@example.com">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Phone</label>
                                                    <input type="tel" class="form-control" value="+1 234 567 890">
                                                </div>
                                                <button class="btn btn-primary">Update Profile</button>
                                            </form>
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
        // Financial Chart
        const financialChart = new Chart(document.getElementById('financialChart'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Income',
                    data: [4500, 5200, 4800, 5800, 4900, 6000],
                    borderColor: '#4b6cb7',
                    tension: 0.4,
                    fill: false
                }, {
                    label: 'Expenses',
                    data: [2800, 3100, 2900, 3300, 2800, 3200],
                    borderColor: '#fc8181',
                    tension: 0.4,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });

        // Expenses Pie Chart
        const expensesPieChart = new Chart(document.getElementById('expensesPieChart'), {
            type: 'doughnut',
            data: {
                labels: ['Services', 'Equipment', 'Marketing', 'Others'],
                datasets: [{
                    data: [45, 25, 20, 10],
                    backgroundColor: ['#4b6cb7', '#5a67d8', '#48bb78', '#f6ad55']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
