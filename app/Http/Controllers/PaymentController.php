<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class PaymentController extends Controller
{
    private $merchant_id;
    private $merchant_secret;

    public function __construct()
    {
        $this->merchant_id = env('PAYHERE_MERCHANT_ID');
        $this->merchant_secret = env('PAYHERE_MERCHANT_SECRET');
    }

    /**
     * Generate the hash to send with payment request
     */
    private function generateHash($order_id, $amount, $currency)
    {
        return strtoupper(
            md5(
                $this->merchant_id .
                $order_id .
                number_format($amount, 2, '.', '') .
                $currency .
                strtoupper(md5($this->merchant_secret))
            )
        );
    }


    /**
     * Initiate Payment Request to PayHere
     */
    public function initiatePayment(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'order_id' => 'required|string',
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'country' => 'required|string',
        ]);

        // Extract request data
        $order_id = $request->order_id;
        $amount = $request->amount;
        $currency = $request->currency;

        // Generate hash for the payment
        $hash = $this->generateHash($order_id, $amount, $currency);

        // Prepare payment data
        $paymentData = [

            'return_url' => route('payment.return'),
            'cancel_url' => route('payment.cancel'),
            'notify_url' => route('payment.notify'),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'country' => $request->country,
            'amount' => $amount,
            'merchant_id' => $this->merchant_id,
            'currency' => $currency,
            'hash' => $hash,
            'order_id' => $order_id,
            'items' => "Order " . $order_id,
        ];

        // Return the PayHere payment form
        return view('payments.checkout', compact('paymentData'));
    }

    /**
     * Handle the return URL after payment (Payment success)
     */
    public function paymentReturn(Request $request)
    {
        Log::info('Payment Return: ', $request->all());
        return response()->json(['message' => 'Payment completed successfully!']);
    }

    /**
     * Handle payment notification from PayHere (Payment status callback)
     */
    public function paymentNotify(Request $request)
    {
        $merchant_id = $request->merchant_id;
        $order_id = $request->order_id;
        $payhere_amount = $request->payhere_amount;
        $payhere_currency = $request->payhere_currency;
        $status_code = $request->status_code;
        $md5sig = $request->md5sig;

        $local_md5sig = strtoupper(
            md5(
                $merchant_id .
                $order_id .
                $payhere_amount .
                $payhere_currency .
                $status_code .
                strtoupper(md5($this->merchant_secret))
            )
        );

        if ($local_md5sig === $md5sig && $status_code == 2) {
            Log::info('Payment successful', $request->all());
            return response()->json(['status' => 'success'], 200);
        } else {
            Log::error('Payment failed or invalid signature', $request->all());
            return response()->json(['status' => 'failure'], 400);
        }
    }

    public function checkout($encryptedDetails)
    {
        try {
            // Decrypt the URL parameter
            $decryptedDetails = Crypt::decryptString($encryptedDetails);
            $details = json_decode($decryptedDetails, true);

            // Retrieve user information using the user_ID
            $user = User::select('first_name', 'last_name', 'email', 'phone_number as phone', 'address')
                        ->where('user_ID', $details['user_id'])
                        ->first();

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            // Pass the details to the Blade view
            return view('payments.checkout', [
                'user_id' => $details['user_id'],
                'meeting_id' => $details['meeting_id'],
                'token' => $details['token'],
                'price' => $details['price'],
                'user' => $user, // Pass the user information
            ]);
        } catch (\Exception $e) {
            // Handle decryption failure
            return response()->json(['message' => 'Invalid or corrupted URL data.', 'error' => $e->getMessage()], 400);
        }
    }
}
