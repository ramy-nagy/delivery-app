<?php

namespace App\Http\Controllers\Api\V1\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,pdf'],
            'folder' => ['nullable', 'string', 'max:64', 'regex:/^[a-z0-9_\-]+$/'],
        ]);

        $folder = $request->input('folder', 'uploads');
        $file = $request->file('file');
        $name = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('public/'.$folder, $name);

        $url = Storage::url($path);

        return $this->created([
            'path' => $path,
            'url' => $url,
        ], 'File uploaded successfully');
    }
}
