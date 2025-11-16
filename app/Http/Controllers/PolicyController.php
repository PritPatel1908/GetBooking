<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PolicyController extends Controller
{
    /**
     * Display Terms and Conditions page
     */
    public function termsAndConditions()
    {
        return view('policy.terms-and-conditions');
    }

    /**
     * Display Contact Us page
     */
    public function contactUs()
    {
        return view('policy.contact-us');
    }

    /**
     * Display Cancellation and Refund Policy page
     */
    public function cancellationAndRefund()
    {
        return view('policy.cancellation-and-refund');
    }

    /**
     * Display Privacy Policy page
     */
    public function privacyPolicy()
    {
        return view('policy.privacy-policy');
    }

    /**
     * Display Shipping and Delivery Policy page
     */
    public function shippingAndDelivery()
    {
        return view('policy.shipping-and-delivery');
    }
}
