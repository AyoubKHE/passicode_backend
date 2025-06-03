<?php

namespace App\Http\Controllers\Payments;

use Exception;
use Throwable;
use App\Http\Controllers\Controller;
use Chargily\ChargilyPay\ChargilyPay;
use App\Models\Orders\ChargilyPayment;
use Chargily\ChargilyPay\Auth\Credentials;


class ChargilyPayRedirect extends Controller
{

    protected function chargilyPayInstance()
    {
        return new ChargilyPay(new Credentials([
            "mode" => "test",
            "public" => config('app.CHARGILY_PUBLIC_KEY'),
            "secret" => config('app.CHARGILY_SECRET_KEY'),
        ]));
    }

    public function __invoke($payment_id)
    {

        try {
            $payment = ChargilyPayment::where(
                "id",
                $payment_id
            )
                ->first();
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$payment) {
            throw new Exception(
                'Payment not found.',
                404
            );
        }

        $checkout = $this->chargilyPayInstance()->checkouts()->create([
            "metadata" => [
                "payment_id" => $payment->id,
            ],
            "locale" => "ar",
            "amount" => $payment->amount,
            "currency" => $payment->currency,
            "description" => "Payment ID={$payment->public_id}",
            "success_url" => "http://localhost:5173/payment/success",
            "failure_url" => "http://localhost:5173/payment/failure",
            "webhook_endpoint" => route("chargilypay.webhook_endpoint"),
        ]);

        if ($checkout) {
            return redirect((string) $checkout->getUrl());
        }
    }
}
