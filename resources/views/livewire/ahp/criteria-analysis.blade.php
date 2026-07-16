<div class="min-h-screen bg-zinc-50/80 dark:bg-zinc-900/80 backdrop-blur-sm p-3 rounded-3xl">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4 mb-8">
            <div class="space-y-2">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Analisis Kriteria</h1>
                <p class="text-gray-600 dark:text-gray-400">Perbandingan berpasangan antar kriteria</p>
            </div>
            <div class="flex gap-2">
                <flux:button wire:click="openModal" variant="primary" icon="pencil">
                    Ubah Nilai
                </flux:button>
                <flux:button wire:click="resetToDefault" variant="ghost" icon="arrow-path">
                    Reset
                </flux:button>
            </div>
        </div>

        <!-- Pairwise Matrix Section -->
        <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Matriks Perbandingan Kriteria</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white">Kriteria</th>
                            @foreach ($criteria as $criterion)
                                <th class="px-3 py-2 text-center font-semibold text-gray-900 dark:text-white text-xs">
                                    {{ substr($criterion, 0, 8) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($criteria as $rowIndex => $criterion)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-3 py-2 font-medium text-gray-900 dark:text-white whitespace-nowrap text-xs">
                                    {{ substr($criterion, 0, 8) }}
                                </td>
                                @foreach ($criteria as $colIndex => $c)
                                    <td class="px-3 py-2 text-center">
                                        <span class="inline-flex px-2 py-1 rounded bg-blue-50 dark:bg-blue-900/30 text-blue-900 dark:text-blue-300 font-medium text-xs">
                                            @php
                                                $value = $pairwiseMatrix[$rowIndex][$colIndex] ?? 1;
                                                $scale = $configService->numericToScale($value);
                                                echo is_numeric($scale) ? number_format($value, 2) : $scale;
                                            @endphp
                                        </span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                Matriks perbandingan berpasangan menunjukkan tingkat kepentingan relatif antar kriteria.
            </p>
        </section>

        <!-- Column Sums Section -->
        <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Jumlah Kolom</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-gray-900 dark:text-white">Jumlah</th>
                            @foreach ($criteria as $criterion)
                                <th class="px-3 py-2 text-center font-semibold text-gray-900 dark:text-white text-xs">
                                    {{ substr($criterion, 0, 8) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-blue-50 dark:bg-blue-900/20">
                            <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">Total</td>
                            @foreach ($columnSums as $sum)
                                <td class="px-3 py-2 text-center font-medium text-gray-900 dark:text-white">
                                    {{ number_format($sum, 4) }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                Nilai pada baris ini adalah penjumlahan tiap kolom dari matriks perbandingan.
            </p>
        </section>

        <!-- Normalized Matrix Section -->
        <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Matriks Normalisasi</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-2 py-2 text-left font-semibold text-gray-900 dark:text-white">Kriteria</th>
                            @foreach ($criteria as $criterion)
                                <th class="px-2 py-2 text-center font-semibold text-gray-900 dark:text-white">
                                    {{ substr($criterion, 0, 6) }}
                                </th>
                            @endforeach
                            <th class="px-2 py-2 text-center font-semibold text-gray-900 dark:text-white bg-green-50 dark:bg-green-900/20">Jumlah</th>
                            <th class="px-2 py-2 text-center font-semibold text-gray-900 dark:text-white bg-blue-50 dark:bg-blue-900/20">Prioritas</th>
                            <th class="px-2 py-2 text-center font-semibold text-gray-900 dark:text-white bg-orange-50 dark:bg-orange-900/20">Eigen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($normalizedMatrix as $rowIndex => $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-2 py-2 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ substr($criteria[$rowIndex], 0, 6) }}
                                </td>
                                @foreach ($row as $value)
                                    <td class="px-2 py-2 text-center text-gray-600 dark:text-gray-400">
                                        {{ number_format($value, 4) }}
                                    </td>
                                @endforeach
                                <td class="px-2 py-2 text-center font-medium bg-green-50 dark:bg-green-900/20 text-green-900 dark:text-green-300">
                                    {{ number_format(array_sum($row), 4) }}
                                </td>
                                <td class="px-2 py-2 text-center font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-900 dark:text-blue-300">
                                    {{ number_format($priorityVector[$rowIndex], 4) }}
                                </td>
                                <td class="px-2 py-2 text-center font-medium bg-orange-50 dark:bg-orange-900/20 text-orange-900 dark:text-orange-300">
                                    {{ number_format($rowEigenValues[$rowIndex], 4) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                Kolom matriks normalisasi diperoleh dengan membagi tiap elemen matriks perbandingan dengan jumlah kolom yang bersesuaian.
            </p>
        </section>

        <!-- Priority Vector & Weights Section -->
        <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Priority Vector & Bobot Kriteria</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white">Kriteria</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-900 dark:text-white">Priority Vector</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-900 dark:text-white">Bobot (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($criteria as $index => $criterion)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    {{ $criterion }}
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">
                                    {{ number_format($priorityVector[$index], 4) }}
                                </td>
                                <td class="px-4 py-3 text-center font-medium">
                                    <span class="inline-flex px-3 py-1 rounded-full bg-green-50 dark:bg-green-900/30 text-green-900 dark:text-green-300">
                                        {{ number_format($weights[$index] * 100, 2) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                Bobot menunjukkan tingkat kepentingan relatif setiap kriteria dalam pengambilan keputusan.
            </p>
        </section>

        <!-- Lambda Max, CI, CR Section -->
        <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">λmax, CI, CR</h2>
            
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/10 p-4 border border-blue-200 dark:border-blue-800">
                    <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">λmax</div>
                    <div class="text-2xl font-bold text-blue-900 dark:text-blue-300 mt-2">
                        {{ number_format($lambdaMax, 4) }}
                    </div>
                </div>
                <div class="rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-900/10 p-4 border border-purple-200 dark:border-purple-800">
                    <div class="text-sm text-purple-600 dark:text-purple-400 font-medium">CI</div>
                    <div class="text-2xl font-bold text-purple-900 dark:text-purple-300 mt-2">
                        {{ number_format($ci, 4) }}
                    </div>
                </div>
                <div class="rounded-2xl bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-900/10 p-4 border border-orange-200 dark:border-orange-800">
                    <div class="text-sm text-orange-600 dark:text-orange-400 font-medium">CR</div>
                    <div class="text-2xl font-bold text-orange-900 dark:text-orange-300 mt-2">
                        {{ number_format($cr, 4) }}
                    </div>
                </div>
                <div class="rounded-2xl bg-gradient-to-br {{ $status === 'Konsisten' ? 'from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-900/10' : 'from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-900/10' }} p-4 border {{ $status === 'Konsisten' ? 'border-green-200 dark:border-green-800' : 'border-red-200 dark:border-red-800' }}">
                    <div class="text-sm {{ $status === 'Konsisten' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-medium">Status</div>
                    <div class="text-2xl font-bold {{ $status === 'Konsisten' ? 'text-green-900 dark:text-green-300' : 'text-red-900 dark:text-red-300' }} mt-2">
                        {{ $status }}
                    </div>
                </div>
            </div>

            <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                <h3 class="font-semibold text-blue-900 dark:text-blue-300 mb-2">Penjelasan:</h3>
                <ul class="text-sm text-blue-800 dark:text-blue-400 space-y-1">
                    <li>• <strong>λmax:</strong> Rata-rata rasio jumlah berbobot baris terhadap prioritas</li>
                    <li>• <strong>CI:</strong> Consistency Index = (λmax - n) / (n - 1)</li>
                    <li>• <strong>CR:</strong> Consistency Ratio = CI / RI</li>
                    <li>• <strong>Status:</strong> {{ $status === 'Konsisten' ? 'CR ≤ 0.1 maka matrix dianggap KONSISTEN' : 'CR > 0.1 maka matrix dianggap TIDAK KONSISTEN' }}</li>
                </ul>
            </div>
        </section>

        <!-- Modal for Update -->
        <flux:modal name="criteria-modal" variant="flyout" class="md:max-w-md">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Ubah Nilai Pairwise</h2>

                <div class="space-y-4">
                    <!-- Select Criteria 1 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Kriteria Pertama
                        </label>
                        <select wire:model.live="selectedCriteria1" class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih kriteria...</option>
                            @foreach ($criteria as $criterion)
                                <option value="{{ $criterion }}">{{ $criterion }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Select Scale -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Skala Perbandingan
                        </label>
                        <select wire:model.live="selectedScale" class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih skala...</option>
                            @foreach ($saatySaleOptions as $value => $label)
                                <option value="{{ $value }}">{{ $value }} - {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Select Criteria 2 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Kriteria Kedua
                        </label>
                        <select wire:model.live="selectedCriteria2" class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih kriteria...</option>
                            @foreach ($availableCriteria2 as $criterion)
                                <option value="{{ $criterion }}">{{ $criterion }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Info Box -->
                    @if ($selectedCriteria1 && $selectedCriteria2 && $selectedScale)
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 text-sm text-blue-800 dark:text-blue-400">
                            <strong>{{ $selectedCriteria1 }}</strong> {{ $selectedScale }} <strong>{{ $selectedCriteria2 }}</strong>
                        </div>
                    @endif
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 mt-6">
                    <flux:button wire:click="updateValue" variant="primary" class="flex-1">
                        Simpan Perubahan
                    </flux:button>
                    <flux:button type="button" @click="$wire.closeModal()" variant="ghost" class="flex-1">
                        Batal
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </div>

    <script>
        document.addEventListener('livewire:navigating', () => {
            $wire.closeModal();
        });

        window.addEventListener('notify', (e) => {
            // Toast notification can be added here
            console.log(e.detail.message);
        });
    </script>
</div>
