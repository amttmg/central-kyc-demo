<x-filament::page>
    <form wire:submit.prevent="filter">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" class="mt-2" icon="heroicon-m-magnifying-glass">
                Filter
            </x-filament::button>
        </div>
    </form>

    <div class="mt-3">
        @if ($filtered)
            {{ $this->table }}
        @endif
    </div>
</x-filament::page>
