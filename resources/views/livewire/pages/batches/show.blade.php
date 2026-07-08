<?php

use App\Domains\Batch\Exceptions\BatchException;
use App\Domains\Batch\Jobs\SignIngredientLotJob;
use App\Domains\Batch\Jobs\ValidateBatchCompletionJob;
use App\Features\Batches\AddIngredientLotFeature;
use App\Features\Batches\AddProcessStepFeature;
use App\Features\Batches\ApproveBatchQaFeature;
use App\Features\Batches\CompleteBatchFeature;
use App\Features\Batches\CompleteProcessStepFeature;
use App\Features\Batches\RecordProcessParameterFeature;
use App\Features\Batches\RejectBatchQaFeature;
use App\Features\Batches\SignIngredientLotFeature;
use App\Features\Batches\GetAvailableIngredientLotsFeature;
use App\Features\Booking\BookFinishedGoodsFeature;
use App\Domains\WinMan\Exceptions\WinManException;
use App\Operations\AllocateBomIngredientOperation;
use App\Models\BatchIngredientLot;
use App\Models\BatchRecord;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] #[Title('Batch Record')] class extends Component {
    public BatchRecord $batch;

    /** @var array<string, mixed> */
    public array $newLot = [
        'material_code' => '',
        'material_description' => '',
        'lot_number' => '',
        'actual_quantity' => '',
        'uom' => '',
    ];

    public string $newStepName = '';

    /** @var array<string, mixed> */
    public array $newParam = [
        'parameter_name' => '',
        'value' => '',
        'uom' => '',
    ];

    /** @var array<int, string> */
    public array $completionIssues = [];

    /** @var array<int, array{code:string,description:string}> */
    public array $materialOptions = [];

    /** @var array<int, array{lot_number:string,quantity_outstanding:float}> */
    public array $inventoryLotOptions = [];

    public ?string $inventoryLotsMessage = null;

    public ?string $activeBomMaterialCode = null;

    public ?int $activeBomComponentSnapshotId = null;

    public ?string $activeBomMaterialDescription = null;

    /** @var array<int, array{lot_number:string,quantity_outstanding:float}> */
    public array $activeBomLotOptions = [];

    public ?string $activeBomLotNumber = null;

    public string $activeBomActualQty = '';

    public ?string $activeBomMessage = null;

    public string $rejectReason = '';

    public string $bookQuantityKg = '';

    public string $bookLotNumber = '';

    public ?string $bookFlash = null;

    public function mount(BatchRecord $batch): void
    {
        $this->batch = $batch;
        $this->reload();
    }

    public function updatedNewLotMaterialCode(?string $materialCode): void
    {
        $materialCode = trim((string) $materialCode);

        $selected = collect($this->materialOptions)->firstWhere('code', $materialCode);

        if ($selected !== null) {
            $this->newLot['material_description'] = $selected['description'];
            $this->newLot['uom'] = $this->newLot['uom'] !== '' ? $this->newLot['uom'] : 'kg';
        }

        $this->newLot['lot_number'] = '';
        $this->loadInventoryLotOptions($materialCode);
    }

    public function useBomMaterial(string $materialCode, string $materialDescription): void
    {
        if (! $this->editable) {
            return;
        }

        $materialCode = trim($materialCode);

        $this->newLot['material_code'] = $materialCode;
        $this->newLot['material_description'] = trim($materialDescription);
        $this->newLot['lot_number'] = '';
        $this->newLot['uom'] = $this->newLot['uom'] !== '' ? $this->newLot['uom'] : 'kg';

        $this->loadInventoryLotOptions($materialCode);
        $this->dispatch('switch-batch-tab', tab: 'allocation');
    }

    public function openBomAllocation(int $componentSnapshotId, string $materialCode, string $materialDescription, ?string $suggestedQty = null): void
    {
        $this->activeBomComponentSnapshotId = $componentSnapshotId;
        $this->activeBomMaterialCode = trim($materialCode);
        $this->activeBomMaterialDescription = trim($materialDescription);
        $this->activeBomLotNumber = null;
        $this->activeBomActualQty = $suggestedQty !== null ? trim($suggestedQty) : '';
        $this->activeBomMessage = null;

        $this->loadActiveBomLotOptions();

        if ($this->activeBomLotNumber === null && count($this->activeBomLotOptions) > 0) {
            $this->activeBomLotNumber = $this->activeBomLotOptions[0]['lot_number'];
        }
    }

    public function cancelBomAllocation(): void
    {
        $this->activeBomComponentSnapshotId = null;
        $this->activeBomMaterialCode = null;
        $this->activeBomMaterialDescription = null;
        $this->activeBomLotOptions = [];
        $this->activeBomLotNumber = null;
        $this->activeBomActualQty = '';
        $this->activeBomMessage = null;
    }

    public function allocateBomIngredient(): void
    {
        $this->authorizeEditable();

        $this->activeBomMessage = null;

        $validated = $this->validate([
            'activeBomComponentSnapshotId' => ['required', 'integer'],
            'activeBomMaterialCode' => ['required', 'string', 'max:255'],
            'activeBomMaterialDescription' => ['required', 'string', 'max:255'],
            'activeBomLotNumber' => ['required', 'string', 'max:255'],
            'activeBomActualQty' => ['required', 'numeric', 'min:0.001'],
        ]);

        $component = $this->batch->componentSnapshots
            ->firstWhere('id', (int) $validated['activeBomComponentSnapshotId']);

        if ($component === null) {
            $this->activeBomMessage = 'Could not find the selected BOM line. Please refresh and try again.';

            return;
        }

        try {
            app(AllocateBomIngredientOperation::class)(
                $this->batch,
                $component,
                (string) $validated['activeBomLotNumber'],
                (float) $validated['activeBomActualQty'],
                auth()->user(),
            );
        } catch (WinManException $e) {
            $this->activeBomMessage = $e->getMessage();

            return;
        } catch (\Throwable $e) {
            report($e);
            $this->activeBomMessage = 'Could not allocate and issue this ingredient right now. Please try again.';

            return;
        }

        $this->reload();
        $this->cancelBomAllocation();
        $this->dispatch('switch-batch-tab', tab: 'allocation');
        session()->flash('status', 'Ingredient lot allocated and issued in WinMan.');
    }

    public function getEditableProperty(): bool
    {
        return $this->batch->status === BatchRecord::STATUS_IN_PROGRESS;
    }

    public function getBookingEnabledProperty(): bool
    {
        return (bool) config('winman.booking.enabled');
    }

    public function addLot(): void
    {
        $this->authorizeEditable();

        $validated = $this->validate([
            'newLot.material_description' => ['required', 'string', 'max:255'],
            'newLot.lot_number' => ['required', 'string', 'max:255'],
            'newLot.actual_quantity' => ['required', 'numeric', 'min:0'],
            'newLot.uom' => ['nullable', 'string', 'max:20'],
            'newLot.material_code' => ['nullable', 'string', 'max:255'],
        ])['newLot'];

        app(AddIngredientLotFeature::class)($this->batch, $validated, auth()->user());

        $this->newLot = ['material_code' => '', 'material_description' => '', 'lot_number' => '', 'actual_quantity' => '', 'uom' => ''];
        $this->inventoryLotOptions = [];
        $this->inventoryLotsMessage = null;
        $this->reload();
    }

    public function signWeighed(int $lotId): void
    {
        $this->signLot($lotId, SignIngredientLotJob::PURPOSE_WEIGHED);
    }

    public function signTipped(int $lotId): void
    {
        $this->signLot($lotId, SignIngredientLotJob::PURPOSE_TIPPED);
    }

    public function addStep(): void
    {
        $this->authorizeEditable();

        $validated = $this->validate([
            'newStepName' => ['required', 'string', 'max:255'],
        ]);

        app(AddProcessStepFeature::class)($this->batch, $validated['newStepName'], null, true, auth()->user());

        $this->newStepName = '';
        $this->reload();
    }

    public function completeStep(int $stepId): void
    {
        $this->authorizeEditable();

        $step = $this->batch->processSteps()->whereKey($stepId)->firstOrFail();
        app(CompleteProcessStepFeature::class)($step, auth()->user());

        $this->reload();
    }

    public function addParameter(): void
    {
        $this->authorizeEditable();

        $validated = $this->validate([
            'newParam.parameter_name' => ['required', 'string', 'max:255'],
            'newParam.value' => ['nullable', 'string', 'max:255'],
            'newParam.uom' => ['nullable', 'string', 'max:20'],
        ])['newParam'];

        app(RecordProcessParameterFeature::class)(
            $this->batch,
            $validated['parameter_name'],
            $validated['value'] ?: null,
            $validated['uom'] ?: null,
            auth()->user(),
        );

        $this->newParam = ['parameter_name' => '', 'value' => '', 'uom' => ''];
        $this->reload();
    }

    public function complete(): void
    {
        $this->authorizeEditable();

        try {
            app(CompleteBatchFeature::class)($this->batch, auth()->user());
        } catch (BatchException $e) {
            $this->completionIssues = $e->issues;

            return;
        }

        $this->reload();
        session()->flash('status', 'Batch completed.');
    }

    public function approve(): void
    {
        if ($this->batch->status !== BatchRecord::STATUS_COMPLETED) {
            return;
        }

        app(ApproveBatchQaFeature::class)($this->batch, auth()->user());
        $this->reload();
        session()->flash('status', 'Batch approved and closed by QA.');
    }

    public function reject(): void
    {
        if ($this->batch->status !== BatchRecord::STATUS_COMPLETED) {
            return;
        }

        $validated = $this->validate(['rejectReason' => ['required', 'string', 'max:500']]);

        app(RejectBatchQaFeature::class)($this->batch, auth()->user(), $validated['rejectReason']);
        $this->rejectReason = '';
        $this->reload();
        session()->flash('status', 'Batch returned to production for correction.');
    }

    public function book(): void
    {
        if (! $this->bookingEnabled) {
            return;
        }

        $data = $this->validate([
            'bookQuantityKg' => ['required', 'numeric', 'min:0.001'],
            'bookLotNumber' => ['required', 'string', 'max:100'],
        ]);

        $shelfDays = $this->batch->product?->shelf_life_days ?? 180;
        $finished = now();
        $expiry = now()->addDays($shelfDays)->endOfMonth();

        try {
            $log = app(BookFinishedGoodsFeature::class)(
                $this->batch,
                (float) $data['bookQuantityKg'],
                $data['bookLotNumber'],
                [$data['bookLotNumber']],
                $finished,
                $expiry,
                auth()->user(),
            );
        } catch (WinManException $e) {
            $this->bookFlash = $e->getMessage();

            return;
        }

        $this->bookFlash = $log->booking_status === 'success'
            ? "Booked to WinMan (Inventory {$log->winman_inventory_id})."
            : "Booking {$log->booking_status}: {$log->error_message}";
        $this->reload();
    }

    private function signLot(int $lotId, string $purpose): void
    {
        $this->authorizeEditable();

        /** @var BatchIngredientLot $lot */
        $lot = $this->batch->ingredientLots()->whereKey($lotId)->firstOrFail();

        try {
            app(SignIngredientLotFeature::class)($lot, $purpose, auth()->user());
        } catch (BatchException $e) {
            $this->addError('signoff', $e->getMessage());

            return;
        }

        $this->reload();
    }

    private function authorizeEditable(): void
    {
        abort_unless($this->editable, 403, 'This batch is no longer editable.');
    }

    private function reload(): void
    {
        $this->batch = $this->batch->fresh([
            'manufacturingOrder',
            'product',
            'variant',
            'componentSnapshots',
            'ingredientLots.weighedBy',
            'ingredientLots.tippedBy',
            'processSteps.completedBy',
            'processParameters',
            'metalDetectorChecks',
            'pallecons',
            'bookingLogs',
            'issueLogs',
        ]);

        $this->materialOptions = $this->batch->componentSnapshots
            ->filter(fn ($component): bool =>
                filled($component->winman_component_product_id)
                && strtoupper((string) $component->item_type) === 'C'
            )
            ->map(fn ($component): array => [
                'code' => (string) $component->winman_component_product_id,
                'description' => (string) $component->component_description,
            ])
            ->unique('code')
            ->values()
            ->all();

        $this->loadInventoryLotOptions($this->newLot['material_code'] ?? null);

        $this->completionIssues = app(ValidateBatchCompletionJob::class)($this->batch);
    }

    private function loadInventoryLotOptions(?string $materialCode): void
    {
        $materialCode = trim((string) $materialCode);

        if ($materialCode === '') {
            $this->inventoryLotOptions = [];
            $this->inventoryLotsMessage = 'Select a material code to load available WinMan lots.';

            return;
        }

        try {
            $this->inventoryLotOptions = app(GetAvailableIngredientLotsFeature::class)($materialCode, 100);
            $this->inventoryLotsMessage = $this->inventoryLotOptions === []
                ? 'No available WinMan lots found for the selected material. Enter lot manually.'
                : null;
        } catch (\Throwable $e) {
            report($e);
            $this->inventoryLotOptions = [];
            $this->inventoryLotsMessage = 'Unable to load WinMan lots right now. Enter lot manually.';
        }
    }

    private function loadActiveBomLotOptions(): void
    {
        $materialCode = trim((string) $this->activeBomMaterialCode);

        if ($materialCode === '') {
            $this->activeBomLotOptions = [];
            $this->activeBomMessage = 'No material selected.';

            return;
        }

        try {
            $this->activeBomLotOptions = app(GetAvailableIngredientLotsFeature::class)($materialCode, 100);
            if ($this->activeBomLotNumber === null && count($this->activeBomLotOptions) > 0) {
                $this->activeBomLotNumber = $this->activeBomLotOptions[0]['lot_number'];
            }
            $this->activeBomMessage = $this->activeBomLotOptions === []
                ? 'No available WinMan lots found for this BOM material.'
                : null;
        } catch (\Throwable $e) {
            report($e);
            $this->activeBomLotOptions = [];
            $this->activeBomMessage = 'Unable to load WinMan lots right now.';
        }
    }
}; ?>

<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6" x-data="{ tab: 'allocation' }" x-on:switch-batch-tab.window="tab = $event.detail.tab">

        @if (session('status'))
            <div class="bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="bg-white shadow-sm rounded-lg p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="text-sm text-gray-500">Batch Number</div>
                    <div class="text-2xl font-semibold text-gray-800">{{ $batch->batch_number }}</div>
                    <div class="mt-1 text-sm text-gray-600">
                        {{ $batch->product?->product_name ?? $batch->manufacturingOrder?->winman_product_id }}
                        @if ($batch->variant)
                            <span class="text-gray-400">·</span> {{ $batch->variant->variant_name }}
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <span @class([
                        'inline-flex px-3 py-1 rounded-full text-xs font-medium',
                        'bg-amber-100 text-amber-800' => $batch->status === BatchRecord::STATUS_IN_PROGRESS,
                        'bg-blue-100 text-blue-800' => $batch->status === BatchRecord::STATUS_COMPLETED,
                        'bg-purple-100 text-purple-800' => $batch->status === BatchRecord::STATUS_QA_REVIEW,
                        'bg-gray-200 text-gray-700' => $batch->status === BatchRecord::STATUS_CLOSED,
                    ])>
                        {{ \Illuminate\Support\Str::headline($batch->status) }}
                    </span>
                    <a href="{{ route('manufacturing-orders.search') }}" wire:navigate
                        class="block mt-3 text-sm text-indigo-600 hover:underline">← MO Search</a>
                </div>
            </div>

            <dl class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div><dt class="text-gray-500">MO Reference</dt><dd class="font-medium text-gray-800">{{ $batch->manufacturingOrder?->mo_number ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Recipe Code</dt><dd class="font-medium text-gray-800">{{ $batch->manufacturingOrder?->recipe_code ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Planned Qty</dt><dd class="font-medium text-gray-800">{{ $batch->planned_quantity ? rtrim(rtrim((string) $batch->planned_quantity, '0'), '.') : '—' }}</dd></div>
                <div><dt class="text-gray-500">Production Date</dt><dd class="font-medium text-gray-800">{{ $batch->production_date?->toFormattedDateString() }}</dd></div>
            </dl>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('batches.metal-detector', $batch) }}" wire:navigate class="text-sm px-3 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700">Metal Detector ({{ $batch->metalDetectorChecks->count() }})</a>
                <a href="{{ route('batches.pallecons', $batch) }}" wire:navigate class="text-sm px-3 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700">Pallecons ({{ $batch->pallecons->count() }})</a>
                <a href="{{ route('batches.packing', $batch) }}" wire:navigate class="text-sm px-3 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700">Bucket Packing</a>
                <a href="{{ route('batches.drum', $batch) }}" wire:navigate class="text-sm px-3 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700">Drum Processing</a>
                <a href="{{ route('batches.packaging', $batch) }}" wire:navigate class="text-sm px-3 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700">Packaging Lots</a>
                <a href="{{ route('batches.export', $batch) }}" class="text-sm px-3 py-1.5 rounded-md bg-indigo-50 hover:bg-indigo-100 text-indigo-700">Export</a>
            </div>
        </div>

        @unless ($this->editable)
            <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm rounded-lg px-4 py-3">
                This batch is <strong>{{ \Illuminate\Support\Str::headline($batch->status) }}</strong> and is read-only.
            </div>
        @endunless

        {{-- Tabs --}}
        <div class="bg-white shadow-sm rounded-lg">
            <div class="border-b border-gray-200 px-4">
                <nav class="-mb-px flex flex-wrap gap-6 text-sm font-medium">
                    @foreach (['allocation' => 'Allocation', 'steps' => 'Process Steps', 'parameters' => 'Parameters', 'review' => 'Review & Complete'] as $key => $label)
                        <button @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="py-3 border-b-2">{{ $label }}</button>
                    @endforeach
                </nav>
            </div>

            <div class="p-6">
                {{-- Unified Allocation Workspace --}}
                <div x-show="tab === 'allocation'" class="space-y-4">
                    <div class="text-sm text-gray-600">Allocate against each WinMan BOM line. Add allocation now posts issue to WinMan immediately and records the lot locally.</div>

                    @if ($batch->componentSnapshots->isEmpty())
                        <div class="text-sm text-gray-500">No component snapshot stored for this MO.</div>
                    @else
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="text-left text-xs text-gray-500 uppercase bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2">Type</th>
                                        <th class="px-3 py-2">Product</th>
                                        <th class="px-3 py-2">Description</th>
                                        <th class="px-3 py-2 text-right">Outstanding</th>
                                        <th class="px-3 py-2 text-right">Allocated</th>
                                        <th class="px-3 py-2 text-right"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($batch->componentSnapshots as $component)
                                        @php
                                            $componentCode = (string) $component->winman_component_product_id;
                                            $allocatedLots = $batch->ingredientLots->filter(fn ($lot): bool =>
                                                (string) ($lot->material_code ?? '') === $componentCode
                                                || ((string) ($lot->material_code ?? '') === ''
                                                    && (string) ($lot->material_description ?? '') === (string) $component->component_description)
                                            );
                                            $allocatedQty = (float) $allocatedLots->sum(fn ($lot): float => (float) ($lot->actual_quantity ?? 0));
                                        @endphp
                                        <tr>
                                            <td class="px-3 py-2">{{ $component->item_type }}</td>
                                            <td class="px-3 py-2 text-gray-500">{{ $componentCode }}</td>
                                            <td class="px-3 py-2">{{ $component->component_description }}</td>
                                            <td class="px-3 py-2 text-right">{{ rtrim(rtrim((string) $component->quantity_outstanding, '0'), '.') }}</td>
                                            <td class="px-3 py-2 text-right font-medium">{{ rtrim(rtrim((string) $allocatedQty, '0'), '.') }}</td>
                                            <td class="px-3 py-2 text-right">
                                                <button
                                                    wire:click="openBomAllocation({{ $component->id }}, @js($componentCode), @js((string) $component->component_description), @js((string) rtrim(rtrim((string) $component->quantity_outstanding, '0'), '.')))"
                                                    type="button"
                                                    class="text-indigo-600 hover:underline">
                                                    {{ strtoupper((string) $component->item_type) === 'C' && $this->editable ? 'Allocate ingredient' : 'View allocation' }}
                                                </button>
                                            </td>
                                        </tr>

                                        @if ($activeBomComponentSnapshotId === (int) $component->id)
                                            <tr class="bg-indigo-50/60">
                                                <td colspan="6" class="px-3 py-3 space-y-3">
                                                    <div class="text-sm font-medium text-gray-800">{{ $activeBomMaterialCode }} - {{ $activeBomMaterialDescription }}</div>

                                                    @if ($this->editable && strtoupper((string) $component->item_type) === 'C')
                                                        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                                                            <div>
                                                                <label class="block text-xs text-gray-600 mb-1">Lot number</label>
                                                                <select wire:model="activeBomLotNumber" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                                                    <option value="">- select lot -</option>
                                                                    @foreach ($activeBomLotOptions as $lot)
                                                                        <option value="{{ $lot['lot_number'] }}">{{ $lot['lot_number'] }} ({{ rtrim(rtrim((string) $lot['quantity_outstanding'], '0'), '.') }} available)</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('activeBomLotNumber') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs text-gray-600 mb-1">Actual qty</label>
                                                                <input wire:model="activeBomActualQty" type="number" step="0.001" class="w-full border-gray-300 rounded-md shadow-sm text-sm" />
                                                                @error('activeBomActualQty') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                                            </div>
                                                            <div class="md:col-span-3 flex gap-2 justify-end">
                                                                <x-secondary-button type="button" wire:click="cancelBomAllocation">Close</x-secondary-button>
                                                                <x-primary-button type="button" wire:click="allocateBomIngredient" :disabled="count($activeBomLotOptions) === 0">Add allocation</x-primary-button>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if ($activeBomMessage)
                                                        <div class="text-xs text-gray-600">{{ $activeBomMessage }}</div>
                                                    @endif

                                                    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                                                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                                                            <thead class="text-left text-xs text-gray-500 uppercase bg-gray-50">
                                                                <tr>
                                                                    <th class="px-3 py-2">Allocated Lot</th>
                                                                    <th class="px-3 py-2 text-right">Qty</th>
                                                                    <th class="px-3 py-2">UOM</th>
                                                                    <th class="px-3 py-2">WinMan</th>
                                                                    <th class="px-3 py-2">Weighed</th>
                                                                    <th class="px-3 py-2">Tipped</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-gray-100">
                                                                @forelse ($allocatedLots as $lot)
                                                                    @php
                                                                        $issueLog = $batch->issueLogs
                                                                            ->where('batch_ingredient_lot_id', $lot->id)
                                                                            ->sortByDesc('id')
                                                                            ->first();
                                                                    @endphp
                                                                    <tr>
                                                                        <td class="px-3 py-2">{{ $lot->lot_number }}</td>
                                                                        <td class="px-3 py-2 text-right">{{ rtrim(rtrim((string) $lot->actual_quantity, '0'), '.') }}</td>
                                                                        <td class="px-3 py-2">{{ $lot->uom }}</td>
                                                                        <td class="px-3 py-2">
                                                                            @if ($issueLog?->issue_status === 'success')
                                                                                <span class="text-green-700">Issued</span>
                                                                            @elseif ($issueLog?->issue_status === 'rejected')
                                                                                <span class="text-amber-700" title="{{ $issueLog->error_message }}">Rejected</span>
                                                                            @elseif ($issueLog?->issue_status === 'failed')
                                                                                <span class="text-red-700" title="{{ $issueLog->error_message }}">Failed</span>
                                                                            @else
                                                                                <span class="text-gray-500">-</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="px-3 py-2">{{ $lot->weighed_by ? ($lot->weighedBy?->name ?? 'Signed') : '—' }}</td>
                                                                        <td class="px-3 py-2">{{ $lot->tipped_by ? ($lot->tippedBy?->name ?? 'Signed') : '—' }}</td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="6" class="px-3 py-4 text-center text-gray-500">No allocations recorded yet for this BOM line.</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Ingredients --}}
                <div x-show="tab === 'ingredients'" class="space-y-4">
                    @if ($this->editable)
                        <form wire:submit="addLot" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end bg-gray-50 rounded-lg p-4">
                            <div class="md:col-span-2">
                                <label class="block text-xs text-gray-600 mb-1">Material code (WinMan)</label>
                                <select wire:model.live="newLot.material_code" class="w-full border-gray-300 rounded-md shadow-sm text-sm mb-2">
                                    <option value="">— select material code —</option>
                                    @foreach ($materialOptions as $material)
                                        <option value="{{ $material['code'] }}">{{ $material['code'] }} — {{ \Illuminate\Support\Str::limit($material['description'], 45) }}</option>
                                    @endforeach
                                </select>

                                <label class="block text-xs text-gray-600 mb-1">Material</label>
                                <input list="materials" wire:model="newLot.material_description" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Material name" />
                                <datalist id="materials">
                                    @foreach ($batch->componentSnapshots as $component)
                                        @if (strtoupper((string) $component->item_type) === 'C')
                                            <option value="{{ $component->component_description }}">{{ $component->winman_component_product_id }}</option>
                                        @endif
                                    @endforeach
                                </datalist>
                                @error('newLot.material_description') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Lot number</label>
                                @if (count($inventoryLotOptions) > 0)
                                    <select wire:model="newLot.lot_number" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                        <option value="">— select lot from WinMan —</option>
                                        @foreach ($inventoryLotOptions as $lot)
                                            <option value="{{ $lot['lot_number'] }}">
                                                {{ $lot['lot_number'] }} ({{ rtrim(rtrim((string) $lot['quantity_outstanding'], '0'), '.') }} available)
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input wire:model="newLot.lot_number" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Enter lot number" />
                                @endif
                                @if ($inventoryLotsMessage)
                                    <div class="text-xs text-gray-500 mt-1">{{ $inventoryLotsMessage }}</div>
                                @endif
                                @error('newLot.lot_number') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Actual qty</label>
                                <input wire:model="newLot.actual_quantity" type="number" step="0.001" class="w-full border-gray-300 rounded-md shadow-sm text-sm" />
                                @error('newLot.actual_quantity') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">UOM</label>
                                <input wire:model="newLot.uom" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="kg" />
                            </div>
                            <div>
                                <x-primary-button type="submit" class="w-full justify-center">Add lot</x-primary-button>
                            </div>
                        </form>
                    @endif

                    @error('signoff') <div class="text-sm text-red-600">{{ $message }}</div> @enderror

                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="text-left text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="py-2">Material</th>
                                <th class="py-2">Lot</th>
                                <th class="py-2 text-right">Actual</th>
                                <th class="py-2">Weighed</th>
                                <th class="py-2">Tipped</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($batch->ingredientLots as $lot)
                                <tr>
                                    <td class="py-2">{{ $lot->material_description ?? $lot->material_code }}</td>
                                    <td class="py-2">{{ $lot->lot_number }}</td>
                                    <td class="py-2 text-right">{{ rtrim(rtrim((string) $lot->actual_quantity, '0'), '.') }} {{ $lot->uom }}</td>
                                    <td class="py-2">
                                        @if ($lot->weighed_by)
                                            <span class="text-green-700">✓ {{ $lot->weighedBy?->name }}</span>
                                        @elseif ($this->editable)
                                            <button wire:click="signWeighed({{ $lot->id }})" class="text-indigo-600 hover:underline">Sign weighed</button>
                                        @else — @endif
                                    </td>
                                    <td class="py-2">
                                        @if ($lot->tipped_by)
                                            <span class="text-green-700">✓ {{ $lot->tippedBy?->name }}</span>
                                        @elseif ($this->editable)
                                            <button wire:click="signTipped({{ $lot->id }})" class="text-indigo-600 hover:underline">Sign tipped</button>
                                        @else — @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-gray-500">No ingredient lots recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Process Steps --}}
                <div x-show="tab === 'steps'" x-cloak class="space-y-4">
                    @if ($this->editable)
                        <form wire:submit="addStep" class="flex gap-3 items-end bg-gray-50 rounded-lg p-4">
                            <div class="flex-1">
                                <label class="block text-xs text-gray-600 mb-1">Step name</label>
                                <input wire:model="newStepName" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="e.g. Mixing" />
                                @error('newStepName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <x-primary-button type="submit">Add step</x-primary-button>
                        </form>
                    @endif

                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="text-left text-xs text-gray-500 uppercase">
                            <tr><th class="py-2">Step</th><th class="py-2">Required</th><th class="py-2">Completed</th><th class="py-2"></th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($batch->processSteps as $step)
                                <tr>
                                    <td class="py-2">{{ $step->step_name }}</td>
                                    <td class="py-2">{{ $step->required_flag ? 'Yes' : 'No' }}</td>
                                    <td class="py-2">
                                        @if ($step->completed_by)
                                            <span class="text-green-700">✓ {{ $step->completedBy?->name }} · {{ $step->completed_at?->diffForHumans() }}</span>
                                        @else <span class="text-gray-400">Pending</span> @endif
                                    </td>
                                    <td class="py-2 text-right">
                                        @if (! $step->completed_by && $this->editable)
                                            <button wire:click="completeStep({{ $step->id }})" class="text-indigo-600 hover:underline">Complete</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-6 text-center text-gray-500">No process steps defined.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Parameters --}}
                <div x-show="tab === 'parameters'" x-cloak class="space-y-4">
                    @if ($this->editable)
                        <form wire:submit="addParameter" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end bg-gray-50 rounded-lg p-4">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Parameter</label>
                                <input wire:model="newParam.parameter_name" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="e.g. Temperature" />
                                @error('newParam.parameter_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Value</label>
                                <input wire:model="newParam.value" class="w-full border-gray-300 rounded-md shadow-sm text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">UOM</label>
                                <input wire:model="newParam.uom" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="°C" />
                            </div>
                            <x-primary-button type="submit">Add parameter</x-primary-button>
                        </form>
                    @endif

                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="text-left text-xs text-gray-500 uppercase">
                            <tr><th class="py-2">Parameter</th><th class="py-2">Value</th><th class="py-2">Recorded</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($batch->processParameters as $param)
                                <tr>
                                    <td class="py-2">{{ $param->parameter_name }}</td>
                                    <td class="py-2">{{ $param->value }} {{ $param->uom }}</td>
                                    <td class="py-2 text-gray-500">{{ $param->entered_at?->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-6 text-center text-gray-500">No parameters recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- WinMan BOM --}}
                <div x-show="tab === 'components'" x-cloak>
                    @if ($batch->componentSnapshots->isEmpty())
                        <div class="text-sm text-gray-500">No component snapshot stored for this MO.</div>
                    @else
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="text-left text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="py-2">Type</th>
                                    <th class="py-2">Product</th>
                                    <th class="py-2">Description</th>
                                    <th class="py-2 text-right">Outstanding</th>
                                    <th class="py-2 text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($batch->componentSnapshots as $component)
                                    <tr>
                                        <td class="py-2">{{ $component->item_type }}</td>
                                        <td class="py-2 text-gray-500">{{ $component->winman_component_product_id }}</td>
                                        <td class="py-2">{{ $component->component_description }}</td>
                                        <td class="py-2 text-right">{{ rtrim(rtrim((string) $component->quantity_outstanding, '0'), '.') }}</td>
                                        <td class="py-2 text-right">
                                            @if ($this->editable && strtoupper((string) $component->item_type) === 'C')
                                                <button
                                                    wire:click="openBomAllocation({{ $component->id }}, @js((string) $component->winman_component_product_id), @js((string) $component->component_description), @js((string) rtrim(rtrim((string) $component->quantity_outstanding, '0'), '.')))"
                                                    type="button"
                                                    class="text-indigo-600 hover:underline">
                                                    Allocate ingredient
                                                </button>
                                            @endif
                                        </td>
                                    </tr>

                                    @if ($activeBomComponentSnapshotId === (int) $component->id && $this->editable && strtoupper((string) $component->item_type) === 'C')
                                        <tr class="bg-indigo-50/60">
                                            <td colspan="5" class="py-3">
                                                <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                                                    <div>
                                                        <div class="text-xs text-gray-600 mb-1">Material</div>
                                                        <div class="text-sm font-medium text-gray-800">{{ $activeBomMaterialCode }} - {{ $activeBomMaterialDescription }}</div>
                                                        @error('activeBomMaterialCode') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                                        @error('activeBomMaterialDescription') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-600 mb-1">Lot number</label>
                                                        <select wire:model="activeBomLotNumber" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                                            <option value="">- select lot -</option>
                                                            @foreach ($activeBomLotOptions as $lot)
                                                                <option value="{{ $lot['lot_number'] }}">{{ $lot['lot_number'] }} ({{ rtrim(rtrim((string) $lot['quantity_outstanding'], '0'), '.') }} available)</option>
                                                            @endforeach
                                                        </select>
                                                        @error('activeBomLotNumber') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-600 mb-1">Actual qty</label>
                                                        <input wire:model="activeBomActualQty" type="number" step="0.001" class="w-full border-gray-300 rounded-md shadow-sm text-sm" />
                                                        @error('activeBomActualQty') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div class="md:col-span-2 flex gap-2 justify-end">
                                                        <x-secondary-button type="button" wire:click="cancelBomAllocation">Cancel</x-secondary-button>
                                                        <x-primary-button type="button" wire:click="allocateBomIngredient" :disabled="count($activeBomLotOptions) === 0">Add to ingredients</x-primary-button>
                                                    </div>
                                                </div>
                                                @if ($activeBomMessage)
                                                    <div class="mt-2 text-xs text-gray-600">{{ $activeBomMessage }}</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                {{-- Review & Complete --}}
                <div x-show="tab === 'review'" x-cloak class="space-y-4">
                    @if (count($completionIssues) > 0)
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <div class="font-medium text-amber-900 mb-2">Outstanding before completion:</div>
                            <ul class="list-disc list-inside text-sm text-amber-800 space-y-1">
                                @foreach ($completionIssues as $issue)
                                    <li>{{ $issue }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm text-green-800">
                            All mandatory data is present. This batch is ready for completion.
                        </div>
                    @endif

                    @if ($this->editable)
                        <x-primary-button wire:click="complete" wire:loading.attr="disabled" :disabled="count($completionIssues) > 0">
                            Complete batch
                        </x-primary-button>
                    @endif

                    @if ($batch->status === BatchRecord::STATUS_COMPLETED)
                        <div class="border-t border-gray-200 pt-4 mt-4 space-y-3">
                            <div class="font-medium text-gray-800">QA Review</div>
                            <div class="flex flex-wrap items-end gap-3">
                                <x-primary-button wire:click="approve" wire:loading.attr="disabled">Approve &amp; close</x-primary-button>
                                <div>
                                    <input wire:model="rejectReason" placeholder="Reason for rejection" class="border-gray-300 rounded-md shadow-sm text-sm" />
                                    @error('rejectReason') <span class="block text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>
                                <x-secondary-button wire:click="reject" type="button">Reject to production</x-secondary-button>
                            </div>
                        </div>
                    @endif

                    @if ($this->bookingEnabled && in_array($batch->status, [BatchRecord::STATUS_COMPLETED, BatchRecord::STATUS_CLOSED]))
                        <div class="border-t border-gray-200 pt-4 mt-4 space-y-3">
                            <div class="font-medium text-gray-800">WinMan Finished-Goods Booking</div>
                            @if ($bookFlash)
                                <div class="text-sm text-indigo-700">{{ $bookFlash }}</div>
                            @endif
                            @if ($batch->bookingLogs->where('booking_status', 'success')->isNotEmpty())
                                <div class="text-sm text-green-700">Already booked (Inventory {{ $batch->bookingLogs->firstWhere('booking_status', 'success')?->winman_inventory_id }}).</div>
                            @else
                                <div class="flex flex-wrap items-end gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Quantity (kg)</label>
                                        <input wire:model="bookQuantityKg" type="number" step="0.001" class="border-gray-300 rounded-md shadow-sm text-sm w-32" />
                                        @error('bookQuantityKg') <span class="block text-xs text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Lot / IBC number</label>
                                        <input wire:model="bookLotNumber" class="border-gray-300 rounded-md shadow-sm text-sm" />
                                        @error('bookLotNumber') <span class="block text-xs text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                    <x-primary-button wire:click="book" wire:loading.attr="disabled">Book to WinMan</x-primary-button>
                                </div>
                            @endif
                            @if ($batch->bookingLogs->isNotEmpty())
                                <table class="min-w-full divide-y divide-gray-200 text-sm mt-2">
                                    <thead class="text-left text-xs text-gray-500 uppercase"><tr><th class="py-1">Status</th><th class="py-1">Inventory</th><th class="py-1">Qty (kg)</th><th class="py-1">When</th></tr></thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($batch->bookingLogs->sortByDesc('id') as $log)
                                            <tr>
                                                <td class="py-1">{{ $log->booking_status }}</td>
                                                <td class="py-1">{{ $log->winman_inventory_id ?? '—' }}</td>
                                                <td class="py-1">{{ $log->quantity_booked_kg }}</td>
                                                <td class="py-1 text-gray-500">{{ $log->booking_date?->diffForHumans() }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
