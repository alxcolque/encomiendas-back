<?php

namespace App\Http\Controllers\Files;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use ImageKit\ImageKit;

class FileStorage extends Controller
{
    //private const folder_path = 'myfolder';

    public static function path($path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    public static function upload($fileb64, $folder_path, $format = 'webp')
    {
        $imageName = time() . "." . $format;

        if (env("DIR_PATH_FILE") == "local") {

            $folderPath = public_path($folder_path);
            if (! file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }

            try {
                // separar base64
                $image_parts = explode(";base64,", $fileb64);

                // validar estructura
                if (count($image_parts) < 2) {
                    throw new \Exception("Formato base64 inválido");
                }

                $image_base64 = base64_decode($image_parts[1]);
                $imageFullPath = rtrim($folderPath, '/') . "/" . $imageName;

                if (in_array($format, ['ico', 'svg'])) {
                    // Save directly without processing
                    file_put_contents($imageFullPath, $image_base64);
                } else {
                    // convertir (Intervention Image v3)
                    $image = \Intervention\Image\Laravel\Facades\Image::read($image_base64);

                    // Only encode if it's one of the supported formats for encoding, broadly. 
                    // But meant mainly for webp/jpg/png conversions if needed.
                    // If format is png, we save as png.
                    $image->encodeByExtension($format, 80)->save($imageFullPath);
                }

                $result = rtrim(url('/'), '/') . '/' . trim($folder_path, '/') . '/' . $imageName;
                return $result;
            } catch (\Exception $e) {
                return "Error al procesar imagen: " . $e->getMessage();
            }
        } else if (env("DIR_PATH_FILE") == "imagekit") {

            $imageKit = new \ImageKit\ImageKit(
                config("filesystems.imagekit.public_key"),
                config("filesystems.imagekit.private_key"),
                config("filesystems.imagekit.endpoint_url")
            );

            $upload_res = $imageKit->uploadFile([
                "file"     => $fileb64, // base64 string
                "fileName" => $imageName,
                "folder"   => $folder_path,
            ]);

            return $upload_res->result->fileId . ',' . $upload_res->result->url;
        } else {
            return "Error, no se definió la ruta (DIR_PATH_FILE)";
        }
    }

    public static function replace($path, $file_id, $image, $folder_path)
    {
        self::delete($path, $file_id);
        return self::upload($image, $folder_path);
    }

    public static function delete($path_url, $file_id)
    {
        if (strpos($path_url, "https") !== false) {
            if (env("DIR_PATH_FILE") == "imagekit") {
                $imageKit = new ImageKit(
                    config("filesystems.imagekit.public_key"),
                    config("filesystems.imagekit.private_key"),
                    config("filesystems.imagekit.endpoint_url")
                );

                try {
                    $imageKit->deleteFile($file_id);
                    return "ok";
                } catch (\Exception $e) {
                    return "falla";
                }
            } else {
                return "ok";
            }
        } else {
            $host     = url("/");
            $pathFile = substr($path_url, strlen($host) + 1, strlen($path_url));
            if (File::exists(public_path($pathFile))) {
                File::delete(public_path($pathFile));
                return "ok";
            }
            return "falla";
        }
    }
}
