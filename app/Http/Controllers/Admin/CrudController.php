<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CrudController extends Controller
{
    public function resource(Request $request)
    {
        $resource = (string) $request->route('resource', 'restaurants');

        $map = [
            'restaurants' => \App\Livewire\Admin\Crud\RestaurantCrudTable::class,
            'shops' => \App\Livewire\Admin\Crud\ShopCrudTable::class,
        ];

        $componentClass = $map[$resource] ?? null;

        if ($componentClass === null) {
            abort(404, 'Unknown admin resource.');
        }

        return view('admin.crud.resource', [
            'componentClass' => $componentClass,
            'resource' => $resource,
        ]);
    }
}

