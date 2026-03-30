<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MenuCategorySingleResource;
use App\Models\MenuCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MainCategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = MenuCategory::orderBy('sort_order')->orderBy('name')->get();
        return MenuCategorySingleResource::collection($categories);
    }

    public function show(MenuCategory $menuCategory): MenuCategorySingleResource
    {
        return new MenuCategorySingleResource($menuCategory->load('items'));
    }
}
