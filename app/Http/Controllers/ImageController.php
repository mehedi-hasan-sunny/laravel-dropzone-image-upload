<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function upload(ImageUploadRequest $request)
    {
        $extension = '.' . $request->file->extension();
        $imageName = str_replace($extension, '', $request->file->getClientOriginalName()) . '-' . Carbon::now() . $extension;
        $request->file->move(public_path('images'), $imageName);
        $data = [
            'title' => $request->title,
            'url' => url('/images/' . $imageName)
        ];

        $imageJson = $this->readJsonFile();
        array_push($imageJson, $data);
        $this->storeJsonFile($imageJson);

        return response()->json(['message' => 'Image uploaded successfully.', 'data' => $data], 200);
    }

    private function readJsonFile()
    {
        if (!file_exists(public_path('images.json'))) {
            $data = [];
            $this->storeJsonFile($data);
        }
        return json_decode(file_get_contents(public_path('images.json'), true));
    }

    private function storeJsonFile($data)
    {
        file_put_contents(public_path('images.json'), json_encode($data));
    }

    public function list()
    {
        $imageJson = $this->readJsonFile();
        return response()->json(['data' => $imageJson]);
    }

    public function remove(Request $request)
    {
        $array = $this->readJsonFile();
        $data = $this->removeFromArray($request->url, $array);

        if ($data !== false) {
            $this->storeJsonFile($data);
            $fileName = str_replace(url('/images/') . '/', '', $request->url);
            $this->deleteImage(public_path('/images/'.$fileName));
            return response()->json(['message' => 'Image deleted.']);
        } else {
            return response()->json(['message' => 'Image not found.'], 422);
        }
    }

    private function removeFromArray($value, $array)
    {
        $found = false;
        foreach ($array as $key => $object) {
            if (array_search($value, (array)$object)) {
                array_splice($array, $key, 1);
                $found = true;
                break;
            }
        }
        if ($found) {
            return $array;
        }
        return $found;
    }

    private function deleteImage($fileName)
    {
        if (file_exists($fileName)) {
            unlink($fileName);
        }
    }
}
