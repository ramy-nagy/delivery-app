<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\RestaurantCategoryResource;
use App\Models\RestaurantCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RestaurantCategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = RestaurantCategory::orderBy('sort_order')->orderBy('name')->get();
        return RestaurantCategoryResource::collection($categories);
    }
}
