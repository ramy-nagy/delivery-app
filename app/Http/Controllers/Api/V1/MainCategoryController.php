<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MenuCategorySingleResource;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\MenuCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MainCategoryController extends Controller
{
    use ApiResponse;
    public function index()
    {
        $categories = MenuCategory::orderBy('sort_order')->orderBy('name')->get();
        return $this->success(MenuCategorySingleResource::collection($categories), 'Main categories fetched successfully.');
    }

    public function show(MenuCategory $menuCategory)
    {
        return $this->success(new MenuCategorySingleResource($menuCategory->load('items')), 'Main category fetched successfully.');
    }
}
