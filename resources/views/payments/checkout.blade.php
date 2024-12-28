<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Checkout</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .payment-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 15px;
        }
        .card-header {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }
        .info-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .payment-summary {
            background-color: #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .payment-btn {
            background: linear-gradient(135deg, #198754, #20c997);
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            transition: transform 0.2s;
        }
        .payment-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .info-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }
        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: #212529;
        }
        .secure-badge {
            font-size: 0.9rem;
            color: #198754;
            margin-top: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5 payment-container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0 text-white d-flex align-items-center">
                    <i class="fas fa-lock me-2"></i>
                    Secure Payment Checkout
                </h4>
            </div>
            <div class="card-body p-4">
                <div class="info-section">
                    <h5 class="mb-3"><i class="fas fa-user me-2"></i>Customer Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Full Name</div>
                            <div class="info-value">{{ $user->first_name }} {{ $user->last_name }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Email Address</div>
                            <div class="info-value">{{ $user->email }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value">{{ $user->phone }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Billing Address</div>
                            <div class="info-value">{{ $user->address }}</div>
                        </div>
                    </div>
                </div>

                <div class="payment-summary">
                    <h5 class="mb-3"><i class="fas fa-calendar-check me-2"></i>Meeting Details</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Meeting ID</div>
                            <div class="info-value">#{{ $meeting_id }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Amount to Pay</div>
                            <div class="info-value" style="font-size: 1.2rem; color: #198754">
                                LKR {{ number_format($price, 2) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button class="btn btn-success payment-btn" id="payment">
                        <i class="fas fa-credit-card me-2"></i>Pay with PayHere
                    </button>
                    <div class="secure-badge mt-3">
                        <i class="fas fa-shield-alt me-1"></i>
                        Secured by PayHere Payment Gateway
                    </div>
                </div>

                <div id="responseMessage" class="mt-4"></div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('payment').addEventListener('click', function () {
            const meetingId = {{ $meeting_id }};
            const paymentAmount = {{ $price }};
            const token = "{{ $token }}";
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const button = this;

            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

            const data = {
                meeting_id: meetingId,
                payment_amount: paymentAmount
            };

            fetch("{{ route('finalizeMeetingPayment') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                const responseMessage = document.getElementById('responseMessage');
                if (result.message) {
                    responseMessage.innerHTML = `
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle me-2"></i>Payment Successful</h5>
                            <p>${result.message}</p>
                            <div class="mt-3 p-3 bg-light rounded">
                                <p class="mb-2"><strong>Meeting URL:</strong></p>
                                <p class="text-break">${result.meet_url}</p>
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Please copy this URL or check your mobile app.</small>
                            </div>
                        </div>
                    `;
                } else {
                    responseMessage.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>Payment failed. Please try again.
                        </div>
                    `;
                }
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-credit-card me-2"></i>Pay with PayHere';
            })
            .catch(error => {
                console.error('Error:', error);
                const responseMessage = document.getElementById('responseMessage');
                responseMessage.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>An error occurred while processing your payment. Please try again.
                    </div>
                `;
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-credit-card me-2"></i>Pay with PayHere';
            });
        });
    </script>
</body>
</html>
