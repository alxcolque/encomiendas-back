<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use ImageKit\ImageKit;
use Intervention\Image\Laravel\Facades\Image;

class FileStorageService
{
    public function saveFromBase64($base64, $folder_path)
    {
        // Return only the key (path) or ID, logic to get URL will be separate or mixed?
        // Current usage in Controller:
        // $path = $service->saveFromBase64(...);
        // $url = $service->getUrl($path);

        // Let's implement this.
        return $this->upload($base64, $folder_path);
    }

    public function getUrl($path_or_result)
    {
        // If local, the upload returning the URL/Path.
        // If imagekit, upload returns "id,url".

        if (env("DIR_PATH_FILE") == "imagekit") {
            $parts = explode(',', $path_or_result);
            return count($parts) > 1 ? $parts[1] : $path_or_result;
        }

        return $path_or_result; // Local returns full URL already in previous code?
    }

    public function upload($fileb64, $folder_path)
    {
        $imageName = time() . ".webp";

        if (env("DIR_PATH_FILE") == "local") {

            $folderPath = public_path($folder_path);
            if (! file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }

            try {
                // Determine format
                if (strpos($fileb64, 'base64') !== false) {
                    $image_parts = explode(";base64,", $fileb64);
                    $image_base64 = base64_decode($image_parts[1]);
                } else {
                    $image_base64 = base64_decode($fileb64);
                }

                // Convert to WebP
                $image = Image::read($image_base64)
                    ->encodeByExtension('webp', 80);

                $imageFullPath = rtrim($folderPath, '/') . "/" . $imageName;
                $image->save($imageFullPath);

                // Return relative path or URL? 
                // Controller expects something it can save as 'avatar_key' AND get URL from.
                // For local, key can be relative path.

                return url('/') . '/' . trim($folder_path, '/') . '/' . $imageName;
            } catch (\Exception $e) {
                // Log error?
                throw $e;
            }
        } else if (env("DIR_PATH_FILE") == "imagekit") {

            $imageKit = new ImageKit(
                config("filesystems.imagekit.public_key"),
                config("filesystems.imagekit.private_key"),
                config("filesystems.imagekit.endpoint_url")
            );

            $upload_res = $imageKit->uploadFile([
                "file"     => $fileb64,
                "fileName" => $imageName,
                "folder"   => $folder_path,
            ]);

            // Return "id,url" to store both?
            // Controller saves 'avatar' (URL) and 'avatar_key' (ID).
            // Let's return the simplified result needed.
            return $upload_res->result->fileId . ',' . $upload_res->result->url;
        }

        return null;
    }

    public function delete($path_key)
    {
        if (env("DIR_PATH_FILE") == "imagekit") {
            // key is "id,url" or just "id"?
            // If we stored "id,url" in avatar_key, we need to split.
            $parts = explode(',', $path_key);
            $fileId = $parts[0];

            $imageKit = new ImageKit(
                config("filesystems.imagekit.public_key"),
                config("filesystems.imagekit.private_key"),
                config("filesystems.imagekit.endpoint_url")
            );

            try {
                $imageKit->deleteFile($fileId);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            // Local: path_key might be the full URL or relative path
            // We need relative path to public_path

            $host = url("/");
            $pathFile = str_replace($host . '/', '', $path_key);

            if (File::exists(public_path($pathFile))) {
                File::delete(public_path($pathFile));
                return true;
            }
            return false;
        }
    }
}
