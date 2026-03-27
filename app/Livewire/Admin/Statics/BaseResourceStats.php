<?php

namespace App\Livewire\Admin\Statics;

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

abstract class BaseResourceStats extends Component
{
    abstract protected function modelClass(): string;

    abstract protected function isOpenColumn(): string;

    public function render()
    {
        $class = $this->modelClass();
        $model = new $class();

        $this->authorize('viewAny', $model);

        $query = $class::query();

        $total = (int) $query->count();
        $trashed = method_exists($class, 'onlyTrashed')
            ? (int) $class::onlyTrashed()->count()
            : 0;

        $active = (int) $query->where($this->isOpenColumn(), true)->count();

        return view('livewire.admin.statics.base-resource-stats', [
            'total' => $total,
            'active' => $active,
            'trashed' => $trashed,
        ]);
    }
}

