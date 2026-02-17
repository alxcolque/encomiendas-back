<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        return \App\Http\Resources\PaymentMethodResource::collection(PaymentMethod::all());
    }

    public function store(\App\Http\Requests\PaymentMethodRequest $request)
    {
        $paymentMethod = PaymentMethod::create($request->validated());
        return new \App\Http\Resources\PaymentMethodResource($paymentMethod);
    }

    public function show(PaymentMethod $paymentMethod)
    {
        return new \App\Http\Resources\PaymentMethodResource($paymentMethod);
    }

    public function update(\App\Http\Requests\PaymentMethodRequest $request, PaymentMethod $paymentMethod)
    {
        $paymentMethod->update($request->validated());
        return new \App\Http\Resources\PaymentMethodResource($paymentMethod);
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();

        return response()->noContent();
    }
}
