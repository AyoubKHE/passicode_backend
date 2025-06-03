<?php

namespace App\Http\Controllers\Payments;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Chargily\ChargilyPay\ChargilyPay;
use Chargily\ChargilyPay\Auth\Credentials;


class ChargilyPayBack extends Controller
{

    protected function chargilyPayInstance()
    {
        return new ChargilyPay(new Credentials([
            "mode" => "test",
            "public" => config('app.CHARGILY_PUBLIC_KEY'),
            "secret" => config('app.CHARGILY_SECRET_KEY'),
        ]));
    }

    public function __invoke(Request $request)
    {

        return to_route("shop.showcase");

        // $user = auth()->user();
        // $checkout_id = $request->input("checkout_id");
        // $checkout = $this->chargilyPayInstance()->checkouts()->get($checkout_id);
        // $payment = null;

        // if ($checkout) {
        //     $metadata = $checkout->getMetadata();
        //     $payment = \App\Models\ChargilyPayment::find($metadata['payment_id']);
        //     ////
        //     //// Is not recomended to process payment in back page / success or fail page
        //     //// Doing payment processing in webhook for best practices
        //     ////
        // }
        // dd($checkout, $payment);
    }
}
