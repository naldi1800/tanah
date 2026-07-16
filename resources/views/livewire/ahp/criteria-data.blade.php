<div class="min-h-screen bg-zinc-50/80 dark:bg-zinc-900/80 backdrop-blur-sm p-3 rounded-3xl">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4 mb-8">
            <div class="space-y-2">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Data Kriteria</h1>
                <p class="text-gray-600 dark:text-gray-400">Daftar kriteria yang digunakan dalam proses AHP</p>
            </div>
        </div>

        <!-- Search Section -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="relative">
                <flux:input wire:model.live="search" type="text" placeholder="Cari kriteria..." icon="magnifying-glass" />
            </div>
        </div>

        <!-- Criteria Table Section -->
        <section class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Daftar Kriteria</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                No
                            </th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                Nama Kriteria
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($criteria as $index => $criterion)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 font-medium">
                                    {{ $loop->index + 1 }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">
                                    {{ $criterion }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex justify-center items-center gap-2">
                                        <flux:icon icon="inbox" class="w-5 h-5" />
                                        <span>Tidak ada kriteria yang sesuai</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                <p>Total kriteria: <span class="font-semibold">{{ count($criteria) }}</span></p>
            </div>
        </section>

        <!-- Information Section -->
        <section class="bg-blue-50 dark:bg-blue-900/20 rounded-3xl border border-blue-200 dark:border-blue-800 p-6">
            <div class="flex gap-4">
                <flux:icon icon="information-circle" class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                <div>
                    <h3 class="font-semibold text-blue-900 dark:text-blue-300 mb-2">Tentang Kriteria</h3>
                    <p class="text-sm text-blue-800 dark:text-blue-400">
                        Kriteria merupakan aspek-aspek yang digunakan untuk mengevaluasi alternatif (nama jalan) dalam sistem AHP. 
                        Setiap kriteria akan dibandingkan secara berpasangan untuk menentukan bobot pentingnya.
                    </p>
                </div>
            </div>
        </section>
    </div>
</div>
