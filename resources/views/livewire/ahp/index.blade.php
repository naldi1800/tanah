@php
    $report = $report ?? [];
    $recommendation = $recommendation ?? [];
    $criteria = $report['criteria'] ?? [];
    $columnSums = $report['columnSums'] ?? [];
    $normalized = $report['normalized'] ?? [];
    $priorityVector = $report['priorityVector'] ?? [];
    $weights = $report['weights'] ?? [];
    $lambdaMax = $report['lambdaMax'] ?? 0;
    $ci = $report['ci'] ?? 0;
    $cr = $report['cr'] ?? 0;
    $status = $report['status'] ?? 'Tidak diketahui';
@endphp

<x-layouts::app :title="__('AHP Proses')">
    <div class="min-h-screen bg-zinc-50/80 dark:bg-zinc-900/80 backdrop-blur-sm p-6 rounded-3xl">
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="flex items-start justify-between gap-4 mb-8">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-violet-100 dark:bg-violet-900 rounded-lg">
                            <flux:icon name="chart-bar" class="w-6 h-6 text-violet-600 dark:text-violet-400" />
                        </div>
                        <div>
                            <h1 class="text-4xl font-bold text-gray-900 dark:text-white">AHP Proses</h1>
                            <p class="text-gray-600 dark:text-gray-400">Perhitungan bobot kriteria dan ranking jalan berdasarkan data lapangan.</p>
                        </div>
                    </div>
                </div>

                <flux:button
                    href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}"
                    variant="ghost"
                    icon="arrow-left"
                >
                    Kembali
                </flux:button>
            </div>

            <div class="grid gap-6">
            <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Data Tanah</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Seluruh data tanah digunakan untuk menilai alternatif jalan berdasarkan rerata dan modus.</p>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/60">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Alamat</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Jenis Tanah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">pH</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Kelembapan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Suhu</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Ketinggian</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach (App\Models\Tanah::with('jenisTanah')->get() as $tanah)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $tanah->Alamat }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $tanah->jenisTanah?->jenis ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($tanah->PH_Tanah, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($tanah->Kelembaban_Tanah, 2) }}%</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($tanah->Suhu_Tanah, 2) }}°C</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($tanah->Ketinggian_Tanah, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Keterangan: Tabel menampilkan titik pengamatan data tanah asli dengan atribut seperti alamat, jenis tanah (relasi `jenisTanah`), dan pengukuran pH, kelembapan, suhu, dan ketinggian dari basis data.</p>
            </section>

            <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Matriks Perbandingan Kriteria</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/60">
                            <tr>
                                <th class="px-4 py-3"></th>
                                @foreach ($criteria as $criterion)
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">{{ $criterion }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($report['matrix'] as $rowIndex => $row)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $criteria[$rowIndex] }}</td>
                                    @foreach ($row as $value)
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($value, 4) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Keterangan: Matriks perbandingan berpasangan dibangun dari input perbandingan kriteria. Setiap elemen A_ij menunjukkan seberapa penting kriteria i dibandingkan kriteria j menurut skala AHP.</p>
            </section>

            <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Jumlah Kolom</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/60">
                            <tr>
                                @foreach ($criteria as $criterion)
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">{{ $criterion }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800">
                            <tr>
                                @foreach ($columnSums as $sum)
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($sum, 4) }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Keterangan: Nilai pada baris ini adalah penjumlahan tiap kolom dari matriks perbandingan. Nilai ini digunakan untuk menormalisasi matriks (membagi setiap elemen kolom dengan jumlah kolom tersebut).</p>
            </section>

            <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Matriks Normalisasi</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/60">
                            <tr>
                                <th class="px-4 py-3"></th>
                                @foreach ($criteria as $criterion)
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">{{ $criterion }}</th>
                                @endforeach
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Jumlah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Prioritas</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Eigen</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($normalized as $rowIndex => $row)
                                @php
                                    $rowSum = array_sum($row);
                                    $n = count($normalized);
                                    $prioritas = $n > 0 ? $rowSum / $n : 0;
                                    // weighted sum for eigen calculation using original matrix and weights
                                    $weightedSum = 0.0;
                                    foreach (($report['matrix'][$rowIndex] ?? []) as $colIndex => $val) {
                                        $weightedSum += (float) $val * ($weights[$colIndex] ?? 0);
                                    }
                                    $eigen = ($priorityVector[$rowIndex] ?? 0) != 0 ? $weightedSum / ($priorityVector[$rowIndex] ?? 1) : 0;
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $criteria[$rowIndex] }}</td>
                                    @foreach ($row as $value)
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($value, 4) }}</td>
                                    @endforeach
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($rowSum, 4) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($prioritas, 4) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($eigen, 4) }}</td>
                                </tr>
                            @endforeach
                            {{-- Baris Jumlah: total per kolom pada matriks normalisasi --}}
                            <tr class="font-semibold bg-gray-50 dark:bg-gray-700/40">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">Jumlah</td>
                                @for ($colIndex = 0; $colIndex < count($criteria); $colIndex++)
                                    @php
                                        $colTotal = 0.0;
                                        foreach ($normalized as $r) {
                                            $colTotal += $r[$colIndex] ?? 0;
                                        }
                                    @endphp
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($colTotal, 4) }}</td>
                                @endfor
                                @php
                                    // total of row sums (should be equal to n)
                                    $totalRowSums = 0.0;
                                    foreach ($normalized as $r) {
                                        $totalRowSums += array_sum($r);
                                    }
                                @endphp
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($totalRowSums, 4) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">-</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($lambdaMax ?? 0, 4) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Keterangan: Kolom matriks normalisasi diperoleh dengan membagi tiap elemen matriks perbandingan dengan jumlah kolom yang bersesuaian (lihat "Jumlah Kolom"). Baris <strong>Jumlah</strong> di bawah adalah penjumlahan tiap kolom matriks normalisasi. <strong>Prioritas</strong> pada tiap baris diperoleh dari jumlah nilai pada baris matriks normalisasi dibagi dengan jumlah kriteria (n). <strong>Eigen</strong> pada tiap baris dihitung sebagai rasio antara jumlah berbobot baris pada matriks perbandingan awal terhadap nilai prioritas baris tersebut (digunakan untuk menghitung λmax).</p>
            </section>

            <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Priority Vector & Bobot Kriteria</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/60">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Kriteria</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Priority Vector</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Bobot</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($criteria as $index => $criterion)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $criterion }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($priorityVector[$index] ?? 0, 4) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($weights[$index] ?? 0, 4) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Keterangan: 'Priority Vector' diperoleh dari jumlah tiap baris pada matriks normalisasi dibagi dengan jumlah kriteria (n). 'Bobot' adalah nilai bobot akhir kriteria (serupa dengan priority vector yang dinormalisasi) dan digunakan untuk evaluasi alternatif.</p>
            </section>

            <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">λmax, CI, CR</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-4">
                    <div class="rounded-3xl bg-gray-50 dark:bg-gray-700/50 p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">λmax</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($lambdaMax, 4) }}</p>
                    </div>
                    <div class="rounded-3xl bg-gray-50 dark:bg-gray-700/50 p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">CI</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($ci, 4) }}</p>
                    </div>
                    <div class="rounded-3xl bg-gray-50 dark:bg-gray-700/50 p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">CR</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($cr, 4) }}</p>
                    </div>
                    <div class="rounded-3xl bg-emerald-50 dark:bg-emerald-900/30 p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $status }}</p>
                    </div>
                </div>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Keterangan: λmax dihitung sebagai rata-rata rasio jumlah berbobot baris terhadap prioritas baris. CI = (λmax - n) / (n - 1). CR = CI / RI (Random Index). Jika CR ≤ 0.1 maka matrix dianggap konsisten.</p>
            </section>

            <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Tabel Skor Alternatif & Ranking Jalan</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/60">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Jalan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Jenis Dominan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Skor Jenis</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Skor pH</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Skor Kelembapan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Skor Suhu</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Skor Ketinggian</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Total Skor</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($recommendation['evaluated_streets']->take(5) ?? [] as $stats)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $stats['street'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $stats['jenis_dominan'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $stats['jenis_score'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $stats['ph_score'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $stats['kelembapan_score'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $stats['suhu_score'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $stats['ketinggian_score'] }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($stats['total_score'], 4) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Keterangan: Skor masing-masing atribut (Jenis, pH, Kelembapan, Suhu, Ketinggian) dihitung dengan fungsi pemetaan menjadi skala 1–5 berdasarkan nilai rata-rata jalan tersebut. Total Skor adalah jumlah bobot kriteria dikalikan skor atribut masing-masing (menggunakan bobot dari tabel bobot kriteria).</p>
            </section>

            {{-- Per-criterion AHP reports --}}
            @foreach ($report['criteria'] as $criterion)
                @php $cReport = $recommendation['per_criterion'][$criterion] ?? null; @endphp
                @if ($cReport)
                    <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mt-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Perbandingan Alternatif — {{ $criterion }}</h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Matriks perbandingan antar alternatif berdasarkan kriteria <strong>{{ $criterion }}</strong>.</p>

                        <div class="mt-4 overflow-x-auto">
                            <h3 class="font-semibold">1. Matriks Perbandingan</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">(Simbolik / belum dinormalisasi)</p>
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 mt-2">
                                <thead class="bg-gray-50 dark:bg-gray-700/60">
                                    <tr>
                                        <th class="px-4 py-3"></th>
                                        @foreach ($cReport['labels'] as $label)
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">{{ $label }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($cReport['raw_matrix'] as $rIndex => $row)
                                        <tr>
                                            <td class="px-4 py-3 font-semibold">{{ $cReport['labels'][$rIndex] }}</td>
                                            @foreach ($row as $val)
                                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $val }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <h3 class="font-semibold mt-4">2. Jumlah Matriks Perbandingan</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">(Nilai numerik matriks perbandingan dan jumlah setiap kolom)</p>
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 mt-2">
                                <thead class="bg-gray-50 dark:bg-gray-700/60">
                                    <tr>
                                        <th class="px-4 py-3"></th>
                                        @foreach ($cReport['labels'] as $label)
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">{{ $label }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($cReport['matrix'] as $rIndex => $row)
                                        <tr>
                                            <td class="px-4 py-3 font-semibold">{{ $cReport['labels'][$rIndex] }}</td>
                                            @foreach ($row as $val)
                                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($val, 4) }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                    <tr class="font-semibold bg-gray-50 dark:bg-gray-700/40">
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">Jumlah</td>
                                        @foreach ($cReport['report']['columnSums'] as $sum)
                                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($sum,4) }}</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>

                            <h3 class="font-semibold mt-4">3. Normalisasi / Prioritas / Eigen</h3>
                            <div class="mt-2 overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 mt-2">
                                    <thead class="bg-gray-50 dark:bg-gray-700/60">
                                        <tr>
                                            <th class="px-4 py-3"></th>
                                            @foreach ($cReport['labels'] as $label)
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">{{ $label }}</th>
                                            @endforeach
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Jumlah</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Prioritas</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Eigen</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($cReport['report']['normalized'] as $rIndex => $row)
                                            <tr>
                                                <td class="px-4 py-3 font-semibold">{{ $cReport['labels'][$rIndex] }}</td>
                                                @foreach ($row as $col)
                                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($col,4) }}</td>
                                                @endforeach
                                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format(array_sum($row),4) }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format($cReport['report']['priorityVector'][$rIndex] ?? 0,4) }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ number_format(($cReport['report']['weights'][$rIndex] ?? 0),4) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="font-semibold mt-4">4. Tabel Nilai IR (1-11)</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nilai Random Index (RI) standar internasional untuk n = 1..11. Baris yang gelap menunjukkan ukuran matriks saat ini (n = jumlah alternatif).</p>
                            @php
                                $riTable = [1 => 0.00, 2 => 0.00, 3 => 0.58, 4 => 0.90, 5 => 1.12, 6 => 1.24, 7 => 1.32, 8 => 1.41, 9 => 1.45, 10 => 1.49, 11 => 1.51];
                                $curN = count($cReport['labels'] ?? []);
                            @endphp
                            <div class="mt-2 overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                    <thead class="bg-gray-50 dark:bg-gray-700/60">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">n</th>
                                            @foreach ($riTable as $k => $ri)
                                                @php $isCur = $k === $curN; @endphp
                                                <th class="px-3 py-2 text-center text-xs font-semibold {{ $isCur ? 'bg-black text-white' : 'text-gray-600' }}">{{ $k }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800">
                                        <tr>
                                            <td class="px-3 py-2 font-semibold">RI</td>
                                            @foreach ($riTable as $k => $ri)
                                                @php $isCur = $k === $curN; @endphp
                                                <td class="px-3 py-2 text-center {{ $isCur ? 'bg-black text-white' : 'text-gray-600 dark:text-gray-400' }}">{{ number_format($ri, 2) }}</td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="mt-3">
                                    <div class="bg-black text-white rounded-lg px-4 py-2 inline-block" style="min-width:220px;">
                                        <div class="text-sm">Paling dasar (n = {{ $curN }}): RI = {{ number_format($riTable[$curN] ?? 0, 2) }}</div>
                                    </div>
                                </div>
                            </div>

                            <h3 class="font-semibold mt-4">5. CI / CR / λmax</h3>
                            <div class="mt-2 grid grid-cols-3 gap-4">
                                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-2xl">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">λmax</p>
                                    <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ number_format($cReport['report']['lambdaMax'] ?? 0,4) }}</p>
                                </div>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-2xl">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">CI</p>
                                    <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ number_format($cReport['report']['ci'] ?? 0,4) }}</p>
                                </div>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-2xl">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">CR</p>
                                    <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ number_format($cReport['report']['cr'] ?? 0,4) }}</p>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
            @endforeach


            {{-- Final AHP aggregation (top 5) --}}
            <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Perhitungan AHP Final — Top 5</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Ranking akhir dihitung sebagai ∑(bobots_kriteria × prioritas_alternatif_pada_kriteria).</p>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/60">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Rank</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Jalan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Score</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Kontribusi (per kriteria)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach (($recommendation['ahp_final_ranking'] ?? []) as $i => $row)
                                @if ($i < 5)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $i + 1 }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['street'] }}</td>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($row['score'] ?? 0, 4) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                            @foreach ($row['contributions'] as $crit => $val)
                                                <div>{{ $crit }}: {{ number_format($val,4) }}</div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

			</div>
        </div>
    </div>
</x-layouts::app>
