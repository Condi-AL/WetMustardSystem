<?php

use App\Features\Traceability\BackwardTraceFeature;
use App\Features\Traceability\ForwardTraceFeature;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] #[Title('Traceability')] class extends Component {
    public string $term = '';
    public string $mode = 'backward';
    public bool $searched = false;

    public function search(): void
    {
        $this->searched = trim($this->term) !== '';
    }

    #[Computed]
    public function results(): array
    {
        if (! $this->searched || trim($this->term) === '') {
            return [];
        }

        return $this->mode === 'forward'
            ? app(ForwardTraceFeature::class)($this->term)
            : app(BackwardTraceFeature::class)($this->term);
    }
}; ?>

<div class="py-8">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <h2 class="text-xl font-semibold text-gray-800">Traceability Search</h2>

        <form wire:submit="search" class="bg-white shadow-sm rounded-lg p-4 flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-64">
                <label class="block text-xs text-gray-600 mb-1">Search term</label>
                <x-text-input wire:model="term" type="text" class="block w-full"
                    placeholder="Batch / MO / pallecon / pallet / drum / lot number…" />
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Direction</label>
                <select wire:model="mode" class="border-gray-300 rounded-md shadow-sm text-sm">
                    <option value="backward">Backward (output → ingredients)</option>
                    <option value="forward">Forward (lot → impacted batches)</option>
                </select>
            </div>
            <x-primary-button type="submit">Trace</x-primary-button>
        </form>

        @if ($searched)
            @forelse ($this->results as $result)
                @php($batch = $result['batch'])
                <div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <a href="{{ route('batches.show', $batch) }}" wire:navigate class="text-lg font-semibold text-indigo-600 hover:underline">{{ $batch->batch_number }}</a>
                            <div class="text-sm text-gray-600">{{ $batch->product?->product_name }} · MO {{ $batch->manufacturingOrder?->mo_number ?? '—' }}</div>
                        </div>
                        <div class="text-right text-xs text-gray-500">
                            @foreach ($result['matches'] as $match)
                                <div><span class="text-gray-400">{{ $match['on'] }}:</span> {{ $match['value'] }}</div>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div>
                            <div class="font-medium text-gray-700 mb-1">Ingredient lots ({{ $batch->ingredientLots->count() }})</div>
                            <ul class="space-y-1 text-gray-600">
                                @forelse ($batch->ingredientLots as $lot)
                                    <li>{{ $lot->material_description ?? $lot->material_code }} — <span class="font-mono">{{ $lot->lot_number ?? '—' }}</span></li>
                                @empty
                                    <li class="text-gray-400">None</li>
                                @endforelse
                            </ul>
                        </div>
                        <div>
                            <div class="font-medium text-gray-700 mb-1">Packaging lots ({{ $batch->packagingLots->count() }})</div>
                            <ul class="space-y-1 text-gray-600">
                                @forelse ($batch->packagingLots as $pl)
                                    <li>{{ $pl->packaging_type }} — <span class="font-mono">{{ $pl->supplier_reference_number ?? $pl->lot_or_job_number ?? '—' }}</span></li>
                                @empty
                                    <li class="text-gray-400">None</li>
                                @endforelse
                            </ul>
                        </div>
                        <div>
                            <div class="font-medium text-gray-700 mb-1">Pallecons ({{ $batch->pallecons->count() }})</div>
                            <ul class="space-y-1 text-gray-600">
                                @forelse ($batch->pallecons as $p)
                                    <li>#{{ $p->serial_number }} · {{ $p->fill_weight }}kg</li>
                                @empty
                                    <li class="text-gray-400">None</li>
                                @endforelse
                            </ul>
                        </div>
                        <div>
                            <div class="font-medium text-gray-700 mb-1">Finished output</div>
                            <ul class="space-y-1 text-gray-600">
                                @foreach ($batch->packingRuns as $run)
                                    @foreach ($run->pallets as $pallet)
                                        <li>Pallet {{ $pallet->pallet_number }} ({{ $pallet->pallet_amount }})</li>
                                    @endforeach
                                @endforeach
                                @foreach ($batch->drumProcessingRuns as $run)
                                    @foreach ($run->pallets as $pallet)
                                        <li>Drum pallet {{ $pallet->pallet_ticket_number }} · {{ $pallet->drumRecords->count() }} drums</li>
                                    @endforeach
                                @endforeach
                                @if ($batch->packingRuns->isEmpty() && $batch->drumProcessingRuns->isEmpty())
                                    <li class="text-gray-400">None</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm rounded-lg p-8 text-center text-gray-500">
                    No records found for “{{ $term }}”.
                </div>
            @endforelse
        @endif
    </div>
</div>
