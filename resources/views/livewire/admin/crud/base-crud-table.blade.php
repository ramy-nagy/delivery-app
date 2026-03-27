<div>
    <div style="display:flex; gap: 10px; align-items:center; flex-wrap: wrap;">
        <button class="btn" type="button" wire:click="toggleTrashed(false)" @if(!$showTrashed) disabled @endif>
            Active
        </button>
        <button class="btn" type="button" wire:click="toggleTrashed(true)" @if($showTrashed) disabled @endif>
            Trashed
        </button>

        <div style="flex: 1;"></div>

        @if($panel !== 'create' && $panel !== 'edit')
            <button class="btn primary" type="button" wire:click="create">
                Create
            </button>
        @endif
    </div>

    <div style="margin-top: 12px;">
        <table>
            <thead>
                <tr>
                    @foreach($columns as $col)
                        <th>{{ $col['label'] }}</th>
                    @endforeach
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        @foreach($columns as $col)
                            <td>
                                {{ data_get($row, $col['key']) }}
                            </td>
                        @endforeach
                        <td class="row-actions">
                            <button class="btn" type="button" wire:click="view({{ $row->id }})">View</button>
                            @if(!$showTrashed)
                                <button class="btn" type="button" wire:click="edit({{ $row->id }})">Edit</button>
                                <button class="btn danger" type="button" wire:click="delete({{ $row->id }})">Delete</button>
                            @else
                                <button class="btn primary" type="button" wire:click="restore({{ $row->id }})">Restore</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 12px;">
            {{ $rows->links() }}
        </div>
    </div>

    <div style="margin-top: 20px; border-top:1px solid #eee; padding-top: 16px;">
        @if($panel === 'create' || $panel === 'edit')
            <h3 style="margin: 0 0 10px;">{{ $panel === 'create' ? 'Create' : 'Edit' }}</h3>

            @if ($errors->any())
                <div class="error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form wire:submit.prevent="save">
                @foreach($formFields as $key => $def)
                    <div class="field">
                        <label>{{ $def['label'] }}</label>
                        @if(($def['type'] ?? '') === 'bool')
                            <input type="checkbox" wire:model.defer="form.{{ $key }}" @checked((bool)($form[$key] ?? false))>
                        @else
                            <input
                                type="text"
                                wire:model.defer="form.{{ $key }}"
                                value="{{ $form[$key] ?? '' }}"
                            >
                        @endif
                    </div>
                @endforeach

                <div style="display:flex; gap: 10px; margin-top: 14px; align-items:center;">
                    <button class="btn primary" type="submit">Save</button>
                    <button
                        class="btn"
                        type="button"
                        wire:click="cancelForm"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        @else
            <h3 style="margin: 0 0 10px;">Details</h3>

            @if($panel === 'view' && $selected)
                <div style="background:#fafafa; border:1px solid #eee; border-radius:12px; padding: 12px;">
                    <div style="margin-bottom: 8px;">
                        <strong>ID:</strong> {{ $selected->id }}
                    </div>

                    @foreach($formFields as $key => $def)
                        <div style="margin: 6px 0;">
                            <span style="color:#666; font-size:12px;">{{ $def['label'] }}:</span>
                            <span>{{ data_get($selected, $key) }}</span>
                        </div>
                    @endforeach

                    <div style="margin-top: 14px;">
                        @if(!$showTrashed)
                            <button class="btn" type="button" wire:click="edit({{ $selected->id }})">Edit</button>
                            <button class="btn danger" type="button" wire:click="delete({{ $selected->id }})">Delete</button>
                        @else
                            <button class="btn primary" type="button" wire:click="restore({{ $selected->id }})">Restore</button>
                        @endif
                    </div>
                </div>
            @else
                <div style="color:#666;">
                    Select a row and click <strong>View</strong>.
                </div>
            @endif
        @endif
    </div>
</div>

