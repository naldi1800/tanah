<div class="min-h-screen bg-zinc-50/80 dark:bg-zinc-900/80 backdrop-blur-sm p-3 rounded-3xl">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4 mb-8">
            <div class="space-y-2">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Analisis Alternatif</h1>
                <p class="text-gray-600 dark:text-gray-400">Perbandingan berpasangan alternatif berdasarkan kriteria</p>
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

        <!-- Criterion Selection -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                Pilih Kriteria untuk Analisis
            </label>
            <select wire:model.live="selectedCriterion" class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                @foreach ($criteria as $criterion)
                    <option value="{{ $criterion }}">{{ $criterion }}</option>
                @endforeach
            </select>
        </div>

        <!-- Search -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="relative">
                <flux:input wire:model.live="search" type="text" placeholder="Cari alternatif..." icon="magnifying-glass" />
            </div>
        </div>

        <!-- Pairwise Matrix Section -->
        <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Matriks Perbandingan Alternatif</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-2 py-2 text-left font-semibold text-gray-900 dark:text-white">Alternatif</th>
                            @foreach ($alternatives as $alt)
                                <th class="px-2 py-2 text-center font-semibold text-gray-900 dark:text-white break-words">
                                    <span class="inline-block max-w-[80px]">{{ substr($alt, 0, 10) }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($alternatives as $rowIndex => $alt)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-2 py-2 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    <span class="inline-block max-w-[100px] truncate">{{ substr($alt, 0, 10) }}</span>
                                </td>
                                @foreach ($alternatives as $colIndex => $c)
                                    <td class="px-2 py-2 text-center">
                                        <span class="inline-flex px-1.5 py-1 rounded text-xs bg-blue-50 dark:bg-blue-900/30 text-blue-900 dark:text-blue-300 font-medium">
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
                Matriks perbandingan berpasangan alternatif berdasarkan kriteria: <strong>{{ $selectedCriterion }}</strong>
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
                            @foreach ($alternatives as $alt)
                                <th class="px-3 py-2 text-center font-semibold text-gray-900 dark:text-white text-xs">
                                    {{ substr($alt, 0, 10) }}
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
                            <th class="px-2 py-2 text-left font-semibold text-gray-900 dark:text-white">Alternatif</th>
                            @foreach ($alternatives as $alt)
                                <th class="px-2 py-2 text-center font-semibold text-gray-900 dark:text-white">
                                    {{ substr($alt, 0, 8) }}
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
                                <td class="px-2 py-2 font-medium text-gray-900 dark:text-white whitespace-nowrap text-xs">
                                    {{ substr($alternatives[$rowIndex], 0, 8) }}
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
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Priority Vector & Bobot Alternatif</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white">Alternatif</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-900 dark:text-white">Priority Vector</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-900 dark:text-white">Bobot (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($alternatives as $index => $alt)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    {{ $alt }}
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
                Bobot menunjukkan tingkat kualitas relatif setiap alternatif pada kriteria <strong>{{ $selectedCriterion }}</strong>.
            </p>
        </section>

        <!-- Modal for Update -->
        <flux:modal name="alternative-modal" variant="flyout" class="md:max-w-md">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Ubah Nilai Pairwise</h2>

                <div class="space-y-4">
                    <!-- Select Alternative 1 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Alternatif Pertama
                        </label>
                        <select wire:model.live="selectedAlternative1" class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih alternatif...</option>
                            @foreach ($alternatives as $alt)
                                <option value="{{ $alt }}">{{ $alt }}</option>
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

                    <!-- Select Alternative 2 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Alternatif Kedua
                        </label>
                        <select wire:model.live="selectedAlternative2" class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih alternatif...</option>
                            @foreach ($availableAlternatives2 as $alt)
                                <option value="{{ $alt }}">{{ $alt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Info Box -->
                    @if ($selectedAlternative1 && $selectedAlternative2 && $selectedScale)
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 text-sm text-blue-800 dark:text-blue-400">
                            <strong>{{ $selectedAlternative1 }}</strong> {{ $selectedScale }} <strong>{{ $selectedAlternative2 }}</strong>
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
