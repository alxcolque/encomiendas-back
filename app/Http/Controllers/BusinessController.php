<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessStoreRequest;
use App\Http\Requests\BusinessUpdateRequest;
use App\Http\Resources\Business\BusinessCollection;
use App\Http\Resources\Business\BusinessResource;
use App\Models\Business;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $businesses = Business::orderBy('company_name')->get();
        return new BusinessCollection($businesses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BusinessStoreRequest $request)
    {
        $business = Business::create($request->validated());
        return new BusinessResource($business);
    }

    /**
     * Display the specified resource.
     */
    public function show(Business $business)
    {
        return new BusinessResource($business);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BusinessUpdateRequest $request, Business $business)
    {
        $business->update($request->validated());
        return new BusinessResource($business);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Business $business)
    {
        $business->delete();
        return response()->json([
            'message' => 'Empresa eliminada correctamente.',
        ]);
    }
}
