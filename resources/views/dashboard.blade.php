<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <!-- Welcome Header -->
        <div class="space-y-2">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Selamat Datang!</h1>
            <p class="text-gray-600 dark:text-gray-400">Sistem Pendukung Keputusan Pemilihan Lokasi Tanah dengan Metode AHP</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid gap-4 md:grid-cols-3">
            <!-- Jumlah Data Tanah -->
            <div class="rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/10 p-6 border border-blue-200 dark:border-blue-800">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <flux:icon icon="map" class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Jumlah Data Tanah</p>
                        <p class="text-3xl font-bold text-blue-900 dark:text-blue-300">{{ $tanahCount }}</p>
                    </div>
                </div>
            </div>

            <!-- Jumlah Kriteria -->
            <div class="rounded-xl bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-900/10 p-6 border border-green-200 dark:border-green-800">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <flux:icon icon="list" class="w-8 h-8 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <p class="text-sm text-green-600 dark:text-green-400 font-medium">Jumlah Kriteria</p>
                        <p class="text-3xl font-bold text-green-900 dark:text-green-300">{{ $criteriaCount }}</p>
                    </div>
                </div>
            </div>

            <!-- Jumlah Alternatif -->
            <div class="rounded-xl bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-900/10 p-6 border border-purple-200 dark:border-purple-800">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <flux:icon icon="layers" class="w-8 h-8 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <p class="text-sm text-purple-600 dark:text-purple-400 font-medium">Jumlah Alternatif</p>
                        <p class="text-3xl font-bold text-purple-900 dark:text-purple-300">{{ $alternativesCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Informasi Sistem</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="flex items-center gap-3">
                    <flux:icon icon="information-circle" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Sistem ini menggunakan metode Analytical Hierarchy Process (AHP) untuk membantu dalam pemilihan lokasi tanah terbaik.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <flux:icon icon="chart-bar" class="w-5 h-5 text-green-600 dark:text-green-400" />
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Silakan navigasi ke menu AHP untuk memulai perhitungan dan analisis.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
