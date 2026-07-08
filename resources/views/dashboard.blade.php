<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Production Dashboard
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($loadError)
                <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-amber-900 text-sm">
                    {{ $loadError }}
                </div>
            @endif

            @if ($otherClassifications > 0)
                <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-blue-900 text-sm">
                    {{ $otherClassifications }} order(s) are outside Classification 30 (Intermediate) and 29 (Wet Packed), and are not shown in the grouped panels below.
                </div>
            @endif

            <div class="space-y-6">
                <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Daily Metal Detection</h3>
                            <p class="text-sm text-gray-500">Standalone daily register for start, hourly, and end-of-shift verification.</p>
                        </div>
                        <a href="{{ route('metal-detector.daily') }}" wire:navigate class="text-sm text-indigo-600 hover:underline">Open daily register</a>
                    </div>

                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                                <div class="text-slate-700">Checks recorded today</div>
                                <div class="text-2xl font-semibold text-slate-900">{{ $metalDetector['today_total'] }}</div>
                            </div>
                            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
                                <div class="text-emerald-700">Passes today</div>
                                <div class="text-2xl font-semibold text-emerald-900">{{ $metalDetector['pass_count'] }}</div>
                            </div>
                            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                                <div class="text-red-700">Failures today</div>
                                <div class="text-2xl font-semibold text-red-900">{{ $metalDetector['fail_count'] }}</div>
                            </div>
                        </div>

                        <div>
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                                <h4 class="text-sm font-medium text-gray-900">Today&apos;s recent checks</h4>
                                <div class="text-sm text-gray-500">Last check: {{ $metalDetector['last_check_time'] }}</div>
                            </div>

                            @if (count($metalDetector['recent_checks']) === 0)
                                <div class="text-sm text-gray-500">No metal detector checks have been recorded yet today.</div>
                            @else
                                <div class="overflow-x-auto rounded-lg border border-gray-200">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <th class="px-4 py-2">Time</th>
                                                <th class="px-4 py-2">Type</th>
                                                <th class="px-4 py-2">Context</th>
                                                <th class="px-4 py-2">Result</th>
                                                <th class="px-4 py-2">Signed by</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach ($metalDetector['recent_checks'] as $row)
                                                <tr>
                                                    <td class="px-4 py-2 text-gray-700">{{ $row['time'] }}</td>
                                                    <td class="px-4 py-2 text-gray-700">{{ \Illuminate\Support\Str::headline($row['type']) }}</td>
                                                    <td class="px-4 py-2 text-gray-700">{{ $row['context'] }}</td>
                                                    <td class="px-4 py-2">
                                                        <span @class([
                                                            'inline-flex px-2 py-0.5 rounded-full text-xs font-medium',
                                                            'bg-green-100 text-green-800' => $row['result'] === 'pass',
                                                            'bg-red-100 text-red-800' => $row['result'] === 'fail',
                                                        ])>
                                                            {{ ucfirst($row['result']) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-2 text-gray-700">{{ $row['signed_by'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>

                @foreach ($sections as $section)
                    <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Classification {{ $section['classification'] }} - {{ $section['label'] }}
                                </h3>
                                <p class="text-sm text-gray-500">Grouped by UnitOfMeasure (Wet Packed uses 2 = IBC and 44 = Buckets)</p>
                            </div>
                            <div class="text-sm text-gray-700">
                                <span class="font-semibold">{{ $section['order_count'] }}</span> order(s) | Outstanding <span class="font-semibold">{{ rtrim(rtrim((string) $section['outstanding_total'], '0'), '.') }}</span>
                            </div>
                        </div>

                        <div class="p-6 space-y-5">
                            @forelse ($section['uom_groups'] as $group)
                                <div class="rounded-lg border border-gray-200 overflow-hidden">
                                    <div class="px-4 py-3 bg-gray-50 flex flex-wrap items-center justify-between gap-2">
                                        <div class="font-medium text-gray-800">UnitOfMeasure: {{ $group['uom_label'] }}</div>
                                        <div class="text-xs text-gray-600">{{ $group['order_count'] }} order(s) | Outstanding {{ rtrim(rtrim((string) $group['outstanding_total'], '0'), '.') }}</div>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                                            <thead class="bg-white">
                                                <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    <th class="px-4 py-2">MO Ref</th>
                                                    <th class="px-4 py-2">Product</th>
                                                    <th class="px-4 py-2 text-right">Outstanding</th>
                                                    <th class="px-4 py-2">Due</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach ($group['rows'] as $row)
                                                    <tr>
                                                        <td class="px-4 py-2 font-medium text-gray-900">{{ $row['mo_ref'] }}</td>
                                                        <td class="px-4 py-2 text-gray-700">
                                                            <div class="text-gray-500 text-xs">{{ $row['product_id'] }}</div>
                                                            {{ \Illuminate\Support\Str::limit($row['product_description'], 70) }}
                                                        </td>
                                                        <td class="px-4 py-2 text-right">{{ rtrim(rtrim((string) $row['outstanding'], '0'), '.') }}</td>
                                                        <td class="px-4 py-2">{{ $row['due_date'] ? \Illuminate\Support\Str::of($row['due_date'])->before(' ') : '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-gray-500">No outstanding orders found for this classification.</div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
