<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Http\Resources\FaqResource;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        return FaqResource::collection(Faq::orderBy('order_index')->get());
    }

    public function store(\App\Http\Requests\FaqRequest $request)
    {
        $faq = Faq::create($request->validated());
        return new FaqResource($faq);
    }

    public function show(Faq $faq)
    {
        return new FaqResource($faq);
    }

    public function update(\App\Http\Requests\FaqRequest $request, Faq $faq)
    {
        $faq->update($request->validated());
        return new FaqResource($faq);
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return response()->noContent();
    }
}
