<?php

namespace App\Livewire\Admin\Crud;

use App\Models\User;
use App\Repositories\Admin\CrudRepository;
use App\Services\Admin\CrudService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

abstract class BaseCrudTable extends Component
{
    use WithPagination;

    /**
     * When true, show only soft-deleted records.
     */
    public bool $showTrashed = false;

    /**
     * "view" mode shows read-only details; "edit" and "create" show forms.
     */
    public string $panel = 'view';

    public ?int $editingId = null;

    /**
     * Holds only the fields included in formFields() mapping.
     *
     * @var array<string, mixed>
     */
    public array $form = [];

    /**
     * Loaded model (for view mode).
     */
    public ?Model $selected = null;

    protected string $view = 'livewire.admin.crud.base-crud-table';

    abstract protected function modelClass(): string;

    /**
     * @return array<string, array{label: string, rules: array|string, type: string}>
     */
    abstract protected function formFields(): array;

    /**
     * @return array<int, array{key: string, label: string}>
     */
    abstract protected function tableColumns(): array;

    protected function perPage(): int
    {
        return 10;
    }

    public function mount(): void
    {
        $class = $this->modelClass();
        $this->authorize('viewAny', new $class());

        $this->resetForm();
    }

    public function render()
    {
        $rows = $this->service()->paginate($this->perPage(), $this->showTrashed);

        return view($this->view, [
            'rows' => $rows,
            'columns' => $this->tableColumns(),
            'formFields' => $this->formFields(),
            'panel' => $this->panel,
            'editingId' => $this->editingId,
            'form' => $this->form,
            'showTrashed' => $this->showTrashed,
        ]);
    }

    public function toggleTrashed(bool $value): void
    {
        $this->showTrashed = $value;
        $this->editingId = null;
        $this->panel = 'view';
        $this->selected = null;
        $this->resetPage();
    }

    public function cancelForm(): void
    {
        $this->panel = 'view';
        $this->editingId = null;
        $this->selected = null;
        $this->resetForm();
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorize('create', new ($this->modelClass())());

        $this->panel = 'create';
        $this->editingId = null;
        $this->selected = null;
        $this->resetForm();
    }

    public function view(int $id): void
    {
        $modelClass = $this->modelClass();
        $model = $modelClass::withTrashed()->findOrFail($id);

        $this->authorize('view', $model);

        $this->panel = 'view';
        $this->editingId = null;
        $this->selected = $model;

        $this->form = $this->extractFormFromModel($model);
    }

    public function edit(int $id): void
    {
        $modelClass = $this->modelClass();
        $model = $modelClass::withTrashed()->findOrFail($id);

        $this->authorize('update', $model);

        $this->panel = 'edit';
        $this->editingId = $id;
        $this->selected = null;
        $this->form = $this->extractFormFromModel($model);
    }

    public function save(): void
    {
        $panel = $this->panel;
        if ($panel !== 'create' && $panel !== 'edit') {
            abort(422, 'Invalid panel state for save.');
        }

        $rules = $this->rulesForPanel();
        $validated = $this->validate($rules);

        $data = $this->onlyFormKeys($validated);

        if ($panel === 'create') {
            $this->authorize('create', new ($this->modelClass())());
            $model = $this->service()->create($data);
            $this->panel = 'view';
            $this->selected = $model->fresh();
            $this->editingId = null;
        } else {
            $model = $this->service()->find((int) $this->editingId, true);
            if ($model === null) {
                abort(404, 'Record not found.');
            }

            $this->authorize('update', $model);
            $model = $this->service()->update((int) $this->editingId, $data);
            $this->panel = 'view';
            $this->selected = $model->fresh();
            $this->editingId = null;
        }

        $this->resetForm();
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $modelClass = $this->modelClass();
        $model = $modelClass::withTrashed()->findOrFail($id);

        $this->authorize('delete', $model);
        $this->service()->delete($model);

        if (! $this->showTrashed) {
            $this->toggleTrashed(true);
        }

        $this->resetPage();
    }

    public function restore(int $id): void
    {
        $modelClass = $this->modelClass();
        $model = $modelClass::withTrashed()->findOrFail($id);

        $this->authorize('restore', $model);
        $this->service()->restore($model);

        // After restore, it is no longer trashed.
        $this->toggleTrashed(false);
    }

    protected function resetForm(): void
    {
        $this->panel = 'view';
        $this->editingId = null;
        $this->selected = null;

        $this->form = [];
        foreach (array_keys($this->formFields()) as $key) {
            $this->form[$key] = $this->defaultValueForField($key);
        }
    }

    protected function defaultValueForField(string $key): mixed
    {
        foreach ($this->formFields() as $field => $def) {
            if ($field === $key) {
                if (($def['type'] ?? '') === 'bool') {
                    return false;
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function extractFormFromModel(Model $model): array
    {
        $data = [];
        foreach (array_keys($this->formFields()) as $key) {
            $data[$key] = $model->{$key} ?? null;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    protected function onlyFormKeys(array $validated): array
    {
        $keys = array_keys($this->formFields());
        return array_intersect_key($validated, array_flip($keys));
    }

    protected function rulesForPanel(): array
    {
        $rules = [];
        foreach ($this->formFields() as $key => $def) {
            $rules[$key] = $def['rules'];
        }

        // If editing, convert "required" for some boolean fields can be problematic.
        // Keep it simple; resources can override via formFields() rules.
        return $rules;
    }

    protected function service(): CrudService
    {
        $repo = new CrudRepository($this->modelClass());
        return new CrudService($repo);
    }

    public function updatedShowTrashed(): void
    {
        $this->resetPage();
    }

    public function hydrateSelected(): void
    {
        // placeholder for future enhancements.
    }

    protected function isAuthorized(string $ability, ?Model $model = null): bool
    {
        $class = $this->modelClass();

        if ($model === null) {
            $model = new $class();
        }

        return Gate::allows($ability, $model);
    }
}

