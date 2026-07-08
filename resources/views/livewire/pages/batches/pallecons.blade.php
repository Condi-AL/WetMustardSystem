<?php

use App\Features\Pallecon\AddPalleconRecordFeature;
use App\Models\BatchRecord;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] #[Title('Pallecon Filling')] class extends Component {
    public BatchRecord $batch;

    /** @var array<string, mixed> */
    public array $form = [
        'ticket_number' => '',
        'serial_number' => '',
        'top_seal_number' => '',
        'bottom_seal_number' => '',
        'liner_number' => '',
        'liner_batch_code' => '',
        'fill_weight' => '',
        'start_time' => '',
        'finish_time' => '',
    ];

    public function mount(BatchRecord $batch): void
    {
        $this->batch = $batch->load(['pallecons.checkedBy', 'manufacturingOrder']);
    }

    public function addPallecon(): void
    {
        $validated = $this->validate([
            'form.ticket_number' => ['nullable', 'string', 'max:255'],
            'form.serial_number' => ['required', 'string', 'max:255'],
            'form.top_seal_number' => ['nullable', 'string', 'max:255'],
            'form.bottom_seal_number' => ['nullable', 'string', 'max:255'],
            'form.liner_number' => ['nullable', 'string', 'max:255'],
            'form.liner_batch_code' => ['nullable', 'string', 'max:255'],
            'form.fill_weight' => ['nullable', 'numeric', 'min:0'],
            'form.start_time' => ['nullable', 'date'],
            'form.finish_time' => ['nullable', 'date'],
        ])['form'];

        $validated['start_time'] = $validated['start_time'] ? Carbon::parse($validated['start_time']) : null;
        $validated['finish_time'] = $validated['finish_time'] ? Carbon::parse($validated['finish_time']) : null;
        $validated['fill_weight'] = $validated['fill_weight'] !== '' ? $validated['fill_weight'] : null;

        app(AddPalleconRecordFeature::class)($this->batch, $validated, auth()->user());

        $this->reset('form');
        $this->batch = $this->batch->fresh(['pallecons.checkedBy', 'manufacturingOrder']);
    }
}; ?>

<div class="py-8">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Pallecon Filling</h2>
                <p class="text-sm text-gray-500">Batch {{ $batch->batch_number }} · bulk fill traceability (WM004)</p>
            </div>
            <a href="{{ route('batches.show', $batch) }}" wire:navigate class="text-sm text-indigo-600 hover:underline">← Batch record</a>
        </div>

        <form wire:submit="addPallecon" class="bg-white shadow-sm rounded-lg p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ([
                'serial_number' => 'Serial number *',
                'ticket_number' => 'Ticket number',
                'fill_weight' => 'Fill weight (kg)',
                'top_seal_number' => 'Top seal number',
                'bottom_seal_number' => 'Bottom seal number',
                'liner_number' => 'Liner number',
                'liner_batch_code' => 'Liner batch code',
            ] as $field => $label)
                <div>
                    <label class="block text-xs text-gray-600 mb-1">{{ $label }}</label>
                    <input wire:model="form.{{ $field }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" />
                    @error('form.'.$field) <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            @endforeach
            <div>
                <label class="block text-xs text-gray-600 mb-1">Start time</label>
                <input type="datetime-local" wire:model="form.start_time" class="w-full border-gray-300 rounded-md shadow-sm text-sm" />
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Finish time</label>
                <input type="datetime-local" wire:model="form.finish_time" class="w-full border-gray-300 rounded-md shadow-sm text-sm" />
            </div>
            <div class="flex items-end">
                <x-primary-button type="submit" class="w-full justify-center">Add pallecon</x-primary-button>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($batch->pallecons as $pallecon)
                <div class="bg-white shadow-sm rounded-lg p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="font-semibold text-gray-800">#{{ $pallecon->serial_number }}</div>
                        <div class="text-xs text-gray-400">{{ $pallecon->ticket_number }}</div>
                    </div>
                    <dl class="text-sm text-gray-600 space-y-1">
                        <div class="flex justify-between"><dt class="text-gray-500">Fill weight</dt><dd>{{ $pallecon->fill_weight ? rtrim(rtrim((string) $pallecon->fill_weight, '0'), '.').' kg' : '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Top seal</dt><dd>{{ $pallecon->top_seal_number ?? '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Bottom seal</dt><dd>{{ $pallecon->bottom_seal_number ?? '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Liner</dt><dd>{{ $pallecon->liner_number ?? '—' }} / {{ $pallecon->liner_batch_code ?? '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Checked by</dt><dd>{{ $pallecon->checkedBy?->name ?? '—' }}</dd></div>
                    </dl>
                </div>
            @empty
                <div class="col-span-full text-center text-sm text-gray-500 bg-white rounded-lg p-8">No pallecons recorded yet.</div>
            @endforelse
        </div>
    </div>
</div>
