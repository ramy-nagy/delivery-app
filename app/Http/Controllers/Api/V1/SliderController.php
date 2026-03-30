<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Resources\V1\SliderResource;
use App\Http\Traits\ApiResponse;

class SliderController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $sliders = Slider::where('is_active', true)->orderBy('sort_order')->get();
        return $this->success(SliderResource::collection($sliders), 'Sliders fetched successfully.');
    }

    public function show(Slider $slider)
    {
        return $this->success(new SliderResource($slider), 'Slider fetched successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'required|image',
            'link' => 'nullable|string',
            'type' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);
        $slider = Slider::create(array_diff_key($validated, ['image' => '']));
        if ($request->hasFile('image')) {
            $slider->addMediaFromRequest('image')->toMediaCollection('image');
        }
        return $this->success(new SliderResource($slider), 'Slider created successfully.');
    }

    public function update(Request $request, Slider $slider)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'sometimes|image',
            'link' => 'nullable|string',
            'type' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);
        $slider->update(array_diff_key($validated, ['image' => '']));
        if ($request->hasFile('image')) {
            $slider->clearMediaCollection('image');
            $slider->addMediaFromRequest('image')->toMediaCollection('image');
        }
        return $this->success(new SliderResource($slider), 'Slider updated successfully.');
    }

    public function destroy(Slider $slider)
    {
        $slider->clearMediaCollection('image');
        $slider->delete();
        return $this->success(null, 'Slider deleted successfully.');
    }
}
