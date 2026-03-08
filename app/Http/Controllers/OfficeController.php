<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Http\Resources\Office\OfficeCollection;
use App\Http\Resources\Office\OfficeResource;
use App\Http\Requests\OfficeRequest;
use App\Http\Controllers\Files\FileStorage;
use Illuminate\Support\Facades\DB;

class OfficeController extends Controller
{
    private const FOLDER_PATH = "kolmox/offices";

    public function index()
    {
        return new OfficeCollection(Office::with('city', 'managers')->get());
    }

    public function store(OfficeRequest $request)
    {
        $data = $request->validated();

        if ($request->has('image') && $request->image != null) {
            $url = FileStorage::upload($request->image, self::FOLDER_PATH);
            if ($url == 'Error33' || strpos($url, 'Error') === 0) {
                return response()->json([
                    "message" => "No se subió la imagen de la oficina: " . $url
                ], 400);
            } else if (strpos($url, ",") !== false) {
                $imageArr = explode(",", $url);
                $data["image"] = $imageArr[1];
                $data["image_key"] = $imageArr[0];
            } else {
                $data["image"] = $url;
                $data["image_key"] = null;
            }
        }

        $office = Office::create($data);
        if ($request->has('users')) {
            $users = $request->input('users');
            if (is_array($users) && count($users) > 0) {
                DB::table('office_user')->whereIn('user_id', $users)->delete();
            }
            $office->managers()->sync($users);
        }
        $office->load('city', 'managers');
        return new OfficeResource($office);
    }

    public function show(Office $office)
    {
        $office->load('city', 'managers');
        return new OfficeResource($office);
    }

    public function update(OfficeRequest $request, Office $office)
    {
        $data = $request->validated();

        if ($request->has('image') && $request->image != null && $request->image != $office->image) {
            if ($office->image) {
                FileStorage::delete($office->image, $office->image_key);
            }

            $url = FileStorage::upload($request->image, self::FOLDER_PATH);

            if ($url == "Error33" || strpos($url, 'Error') === 0) {
                return response()->json([
                    "message" => "No se subió la imagen de la oficina: " . $url
                ], 400);
            } else if (strpos($url, ",") !== false) {
                $imageArr = explode(",", $url);
                $data["image"] = $imageArr[1];
                $data["image_key"] = $imageArr[0];
            } else {
                $data["image"] = $url;
                $data["image_key"] = null;
            }
        }

        $office->update($data);
        if ($request->has('users')) {
            $users = $request->input('users');
            if (is_array($users) && count($users) > 0) {
                DB::table('office_user')->whereIn('user_id', $users)->delete();
            }
            $office->managers()->sync($users);
        }
        $office->load('city', 'managers');
        return new OfficeResource($office);
    }

    public function destroy(Office $office)
    {
        if ($office->image) {
            FileStorage::delete($office->image, $office->image_key);
        }
        $office->delete();
        return response()->noContent();
    }
}
