<?php

namespace App\Livewire\Admin\Crud;

use App\Models\Restaurant;
use Livewire\Component;

class RestaurantCrudTable extends BaseCrudTable
{
    protected function modelClass(): string
    {
        return Restaurant::class;
    }

    protected function tableColumns(): array
    {
        return [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'slug', 'label' => 'Slug'],
            ['key' => 'is_open', 'label' => 'Open'],
            ['key' => 'minimum_order_cents', 'label' => 'Min (cents)'],
            ['key' => 'created_at', 'label' => 'Created'],
        ];
    }

    protected function formFields(): array
    {
        return [
            'owner_id' => [
                'label' => 'Owner (User ID)',
                'rules' => ['nullable', 'integer'],
                'type' => 'number',
            ],
            'restaurant_category_id' => [
                'label' => 'Category (ID)',
                'rules' => ['nullable', 'integer', 'exists:restaurant_categories,id'],
                'type' => 'number',
            ],
            'name' => [
                'label' => 'Name',
                'rules' => ['required', 'string', 'max:255'],
                'type' => 'text',
            ],
            'slug' => [
                'label' => 'Slug',
                'rules' => ['required', 'string', 'max:255'],
                'type' => 'text',
            ],
            'description' => [
                'label' => 'Description',
                'rules' => ['nullable', 'string', 'max:5000'],
                'type' => 'textarea',
            ],
            'phone' => [
                'label' => 'Phone',
                'rules' => ['nullable', 'string', 'max:64'],
                'type' => 'text',
            ],
            'is_open' => [
                'label' => 'Is Open',
                'rules' => ['boolean'],
                'type' => 'bool',
            ],
            'minimum_order_cents' => [
                'label' => 'Minimum order (cents)',
                'rules' => ['required', 'integer', 'min:0'],
                'type' => 'number',
            ],
            'latitude' => [
                'label' => 'Latitude',
                'rules' => ['nullable', 'numeric'],
                'type' => 'number',
            ],
            'longitude' => [
                'label' => 'Longitude',
                'rules' => ['nullable', 'numeric'],
                'type' => 'number',
            ],
        ];
    }
}

