<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MenuCategoryResource;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MenuCategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = MenuCategory::orderBy('sort_order')->orderBy('name')->get();
        return MenuCategoryResource::collection($categories);
    }

    public function show(MenuCategory $menuCategory): MenuCategoryResource
    {
        return new MenuCategoryResource($menuCategory->load('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $validated['image'] = $path;
        }

        $validated['slug'] = Str::slug($validated['name']);
        $category = MenuCategory::create($validated);
        return new MenuCategoryResource($category);
    }

    public function update(Request $request, MenuCategory $menuCategory)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($menuCategory->image) {
                Storage::disk('public')->delete($menuCategory->image);
            }
            $path = $request->file('image')->store('categories', 'public');
            $validated['image'] = $path;
        }

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $menuCategory->update($validated);
        return new MenuCategoryResource($menuCategory);
    }

    public function destroy(MenuCategory $menuCategory)
    {
        if ($menuCategory->image) {
            Storage::disk('public')->delete($menuCategory->image);
        }
        $menuCategory->delete();
        return response()->noContent();
    }
}
