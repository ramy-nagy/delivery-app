<?php

namespace App\Livewire\Admin\Crud;

use App\Models\Shop;

class ShopCrudTable extends BaseCrudTable
{
    protected function modelClass(): string
    {
        return Shop::class;
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

