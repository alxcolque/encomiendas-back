<?php

namespace App\Http\Controllers;

use App\Models\SocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    public function index()
    {
        return \App\Http\Resources\SocialLinkResource::collection(SocialLink::all());
    }

    public function store(\App\Http\Requests\SocialLinkRequest $request)
    {
        $socialLink = SocialLink::create($request->validated());
        return new \App\Http\Resources\SocialLinkResource($socialLink);
    }

    public function show(SocialLink $socialLink)
    {
        return new \App\Http\Resources\SocialLinkResource($socialLink);
    }

    public function update(\App\Http\Requests\SocialLinkRequest $request, SocialLink $socialLink)
    {
        $socialLink->update($request->validated());
        return new \App\Http\Resources\SocialLinkResource($socialLink);
    }

    public function destroy(SocialLink $socialLink)
    {
        $socialLink->delete();

        return response()->noContent();
    }
}
