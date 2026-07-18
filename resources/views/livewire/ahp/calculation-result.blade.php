<div class="min-h-screen bg-zinc-50/80 dark:bg-zinc-900/80 backdrop-blur-sm p-6 rounded-3xl">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4 mb-8">
            <div class="space-y-2">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Perhitungan AHP</h1>
                <p class="text-gray-600 dark:text-gray-400">Hasil akhir dan rekomendasi lokasi terbaik</p>
            </div>
        </div>

        <!-- Consistency Status -->
        @php
            $status = $criteriaReport['status'] ?? 'Tidak diketahui';
            $cr = $criteriaReport['cr'] ?? 0;
            $isConsistent = $status === 'Konsisten';
        @endphp
        <div class="{{ $isConsistent ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' }} rounded-3xl border p-6">
            <div class="flex gap-4">
                <flux:icon icon="{{ $isConsistent ? 'check-circle' : 'exclamation-circle' }}" class="w-6 h-6 {{ $isConsistent ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} flex-shrink-0 mt-0.5" />
                <div>
                    <h3 class="font-semibold {{ $isConsistent ? 'text-green-900 dark:text-green-300' : 'text-red-900 dark:text-red-300' }} mb-1">
                        Matrix {{ $status }}
                    </h3>
                    <p class="text-sm {{ $isConsistent ? 'text-green-800 dark:text-green-400' : 'text-red-800 dark:text-red-400' }}">
                        CR = {{ number_format($cr, 4) }} 
                        <span class="ml-2">{{ $isConsistent ? '✓ CR ≤ 0.1 (Konsisten)' : '✗ CR > 0.1 (Tidak Konsisten)' }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Criteria Weights Summary -->
        <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Bobot Kriteria</h2>
            
            <div class="grid gap-4 md:grid-cols-5">
                @foreach ($criteriaReport['criteria'] as $index => $criterion)
                    @php
                        $weight = $criteriaReport['weights'][$index] ?? 0;
                        $percentage = $weight * 100;
                    @endphp
                    <div class="rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/10 p-4 border border-blue-200 dark:border-blue-800">
                        <div class="text-xs text-blue-600 dark:text-blue-400 font-medium truncate">{{ $criterion }}</div>
                        <div class="text-2xl font-bold text-blue-900 dark:text-blue-300 mt-2">
                            {{ number_format($percentage, 1) }}%
                        </div>
                        <div class="text-xs text-blue-700 dark:text-blue-400 mt-1">
                            {{ number_format($weight, 4) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Top 5 Recommendations -->
        <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Top 5 Rekomendasi Lokasi</h2>
            
            <div class="space-y-3">
                @forelse ($topResults as $index => $result)
                    @php
                        $rank = $index + 1;
                        $score = $result['score'] ?? 0;
                        $percentage = min(100, max(0, ($score / 5) * 100));
                        $colors = [
                            1 => ['bg' => 'gold', 'text' => 'yellow'],
                            2 => ['bg' => 'silver', 'text' => 'gray'],
                            3 => ['bg' => 'orange', 'text' => 'orange'],
                            4 => ['bg' => 'blue', 'text' => 'blue'],
                            5 => ['bg' => 'green', 'text' => 'green'],
                        ];
                        $colorSet = $colors[$rank] ?? ['bg' => 'blue', 'text' => 'blue'];
                    @endphp
                    <div class="p-4 rounded-xl border-2 {{ $rank === 1 ? 'border-yellow-400 bg-yellow-50 dark:bg-yellow-900/10' : 'border-gray-200 dark:border-gray-700' }}">
                        <div class="flex items-center gap-4">
                            <!-- Rank Badge -->
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-12 h-12 rounded-full {{ $rank === 1 ? 'bg-yellow-400' : 'bg-gray-200 dark:bg-gray-700' }} font-bold {{ $rank === 1 ? 'text-white' : 'text-gray-900 dark:text-white' }}">
                                    #{{ $rank }}
                                </div>
                            </div>

                            <!-- Location Info -->
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 dark:text-white text-lg">
                                    {{ $result['street'] ?? 'Unknown' }}
                                </h3>
                                <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-full" @style(["width: {$percentage}%"])></div>
                                </div>
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400 flex justify-between">
                                    <span>Skor: {{ number_format($score, 4) }}/5</span>
                                    <span>{{ number_format($percentage, 1) }}%</span>
                                </div>
                            </div>

                            <!-- Score Badge -->
                            <div class="flex-shrink-0 text-right">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ number_format($score, 3) }}
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <flux:icon icon="inbox" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                        <p>Tidak ada data rekomendasi</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Detailed Results Table -->
        @if ($recommendation)
            <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Hasil Perhitungan Detail</h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white">Rank</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white">Alternatif</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-900 dark:text-white">Skor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($recommendation['ahp_final_ranking'] ?? [] as $index => $result)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                        #{{ $index + 1 }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                        {{ $result['street'] ?? 'Unknown' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-900 dark:text-blue-300 font-medium">
                                            {{ number_format($result['score'], 4) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        Tidak ada data
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <!-- Information Section -->
        <section class="bg-blue-50 dark:bg-blue-900/20 rounded-3xl border border-blue-200 dark:border-blue-800 p-6">
            <div class="flex gap-4">
                <flux:icon icon="information-circle" class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                <div>
                    <h3 class="font-semibold text-blue-900 dark:text-blue-300 mb-2">Tentang Perhitungan</h3>
                    <p class="text-sm text-blue-800 dark:text-blue-400 mb-2">
                        Hasil ini dihitung menggunakan metode Analytical Hierarchy Process (AHP) dengan tahapan:
                    </p>
                    <ul class="text-sm text-blue-800 dark:text-blue-400 list-disc list-inside space-y-1">
                        <li>Penentuan bobot kriteria melalui perbandingan berpasangan</li>
                        <li>Penilaian setiap alternatif (nama jalan) terhadap kriteria</li>
                        <li>Agregasi nilai dengan bobot untuk mendapatkan skor akhir</li>
                        <li>Perangkingan alternatif berdasarkan skor akhir</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>
</div>
