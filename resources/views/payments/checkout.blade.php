<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Checkout</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script type="text/javascript" src="https://www.payhere.lk/lib/payhere.js"></script>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4>Payment Checkout</h4>
            </div>
            <div class="card-body">
                <!-- User Information Section -->
                <h5>User Information</h5>
                <p><strong>Full Name:</strong> {{ $user->first_name }} {{ $user->last_name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Phone:</strong> {{ $user->phone }}</p>
                <p><strong>Address:</strong> {{ $user->address }}</p>

                <hr>

                <!-- Meeting Information Section -->
                <h5>Meeting Information</h5>
                <p><strong>Meeting ID:</strong> {{ $meeting_id }}</p>
                <p><strong>Price:</strong> LKR {{ number_format($price, 2) }}</p>
                <p><strong>Authorization Token:</strong> {{ $token }}</p>

                <hr>

                <!-- Payment Button -->
                <button class="btn btn-success" id="payhere-payment">Pay with PayHere</button>
            </div>
        </div>
    </div>

    <script>
        // PayHere Event Handlers
        payhere.onCompleted = function onCompleted(orderId) {
            console.log("Payment completed successfully. OrderID: " + orderId);
            alert("Payment completed! Order ID: " + orderId);
        };

        payhere.onDismissed = function onDismissed() {
            console.log("Payment dismissed by the user.");
            alert("Payment was dismissed. Please try again.");
        };

        payhere.onError = function onError(error) {
            console.error("Payment error: " + error);
            alert("An error occurred during payment. Please try again.");
        };

        // PayHere Payment Configuration
        var payment = {
            "first_name": "{{ $user->first_name }}",
            "last_name": "{{ $user->last_name }}",
            "email": "{{ $user->email }}",
            "phone": "{{ $user->phone }}",
            "address": "{{ $user->address }}",
            "city": "Colombo",
            "country": "Sri Lanka",
            "amount": "{{ $price }}",
            "merchant_id": "{{ env('PAYHERE_MERCHANT_ID') }}",
            "order_id": "Meeting_{{ $meeting_id }}",
            "currency": "LKR",
            "hash": "{{ strtoupper(md5(env('PAYHERE_MERCHANT_ID') . 'Meeting_' . $meeting_id . $price . 'LKR' . env('PAYHERE_MERCHANT_SECRET'))) }}",
            "return_url": "{{ route('payment.return') }}",
            "cancel_url": "{{ route('payment.cancel') }}",
            "notify_url": "{{ route('payment.notify') }}",
            "items": "Meeting with Professional",
            "sandbox": true,

        };

        // Show PayHere Payment Popup
        document.getElementById('payhere-payment').onclick = function (e) {
            payhere.startPayment(payment);
        };
    </script>
</body>
</html>
