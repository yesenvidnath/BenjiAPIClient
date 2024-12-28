document.addEventListener('DOMContentLoaded', function() {
    // Ensure PayHere script is loaded
    if (typeof payhere === 'undefined') {
        console.error('PayHere script not loaded');
        return;
    }

    // Payment Button Click Handler
    function initiatePayment(meetingDetails) {
        // Send payment preparation request to backend
        fetch('/prepare-payment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(meetingDetails)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const paymentData = result.paymentData;

                // Configure PayHere payment object
                const payment = {
                    "sandbox": true, // Set to false in production
                    "merchant_id": paymentData.merchant_id,
                    "return_url": window.location.origin + "/payment/return",
                    "cancel_url": window.location.origin + "/payment/cancel",
                    "notify_url": window.location.origin + "/payment/callback",
                    "order_id": paymentData.order_id,
                    "items": "Meeting Payment",
                    "amount": paymentData.amount,
                    "currency": paymentData.currency,
                    "hash": paymentData.hash,
                    "first_name": paymentData.first_name,
                    "last_name": paymentData.last_name,
                    "email": paymentData.email,
                    "phone": paymentData.phone,
                    "address": paymentData.address,
                    "city": "Colombo",
                    "country": "Sri Lanka",
                    "custom_1": paymentData.meetings_id, // Pass meetings_id
                    "custom_2": paymentData.user_id // Pass user_id
                };

                // Configure PayHere callbacks
                payhere.onCompleted = function(orderId) {
                    console.log("Payment completed. OrderID:", orderId);
                    // Optional: Trigger success notification
                };

                payhere.onDismissed = function() {
                    console.log("Payment dismissed");
                    // Optional: Handle cancelled payment
                };

                payhere.onError = function(error) {
                    console.log("Payment error:", error);
                    // Optional: Handle payment errors
                };

                // Initiate PayHere payment
                payhere.startPayment(payment);
            } else {
                // Handle preparation failure
                console.error('Payment preparation failed');
            }
        })
        .catch(error => {
            console.error('Error preparing payment:', error);
        });
    }

    // Expose payment initiation to global scope if needed
    window.initiatePayment = initiatePayment;
});
