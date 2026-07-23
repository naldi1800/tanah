<?php

use Livewire\Volt\Component;
use App\Models\JenisTanah;
use App\Models\Tanah;

new class extends Component {
    public bool $showFormModal = false;
    public bool $isEdit = false;
    public bool $showDeleteModal = false;
    public ?Tanah $deletingTanah = null;
    public ?int $tanahId = null;

    public array $form = [
        'Alamat' => '',
        'jenis_tanah_id' => '',
        'PH_Tanah' => '',
        'Kelembaban_Tanah' => '',
        'Suhu_Tanah' => '',
        'drainase' => '',
    ];

    public function create(): void
    {
        $this->reset('form', 'tanahId');
        $this->isEdit = false;
        $this->showFormModal = true;
    }

    public function edit(Tanah $tanah): void
    {
        $this->tanahId = $tanah->id;
        $this->form = $tanah->only(array_keys($this->form));
        $this->isEdit = true;
        $this->showFormModal = true;
    }

    public function delete(Tanah $tanah): void
    {
        $this->deletingTanah = $tanah;
        $this->showDeleteModal = true;
    }

    public function destroy(): void
    {
        if ($this->deletingTanah) {
            $this->deletingTanah->delete();
            $this->dispatch('toast', type: 'success', message: 'Data tanah berhasil dihapus');
        }

        $this->showDeleteModal = false;
        $this->deletingTanah = null;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->reset('form', 'tanahId');
        $this->isEdit = false;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingTanah = null;
    }

    public function save(): void
    {
        $data = $this->validate([
            'form.Alamat' => 'required|string|max:255',
            'form.jenis_tanah_id' => 'required|exists:jenis_tanahs,id',
            'form.PH_Tanah' => 'required|numeric',
            'form.Kelembaban_Tanah' => 'required|numeric',
            'form.Suhu_Tanah' => 'required|numeric',
            'form.drainase' => 'required|in:Baik,Sedang,Buruk',
        ], [
            'form.Alamat.required' => 'Alamat wajib diisi',
            'form.jenis_tanah_id.required' => 'Jenis tanah wajib dipilih',
            'form.jenis_tanah_id.exists' => 'Jenis tanah tidak valid',
            'form.PH_Tanah.required' => 'Nilai pH tanah wajib diisi',
            'form.PH_Tanah.numeric' => 'Nilai pH tanah harus angka',
            'form.Kelembaban_Tanah.required' => 'Kelembaban tanah wajib diisi',
            'form.Kelembaban_Tanah.numeric' => 'Kelembaban tanah harus angka',
            'form.Suhu_Tanah.required' => 'Suhu tanah wajib diisi',
            'form.Suhu_Tanah.numeric' => 'Suhu tanah harus angka',
            'form.drainase.required' => 'Drainase wajib dipilih',
            'form.drainase.in' => 'Drainase harus Baik, Sedang, atau Buruk',
        ])['form'];

        if ($this->isEdit && $this->tanahId) {
            $tanah = Tanah::findOrFail($this->tanahId);
            $tanah->update($data);
            $this->dispatch('toast', type: 'success', message: 'Data tanah berhasil diupdate');
        } else {
            Tanah::create($data);
            $this->dispatch('toast', type: 'success', message: 'Data tanah berhasil disimpan');
        }

        $this->showFormModal = false;
        $this->reset('form', 'tanahId');
        $this->isEdit = false;
    }

    public function with(): array
    {
        return [
            'tanahs' => Tanah::with('jenisTanah')->latest()->get(),
            'jenisTanahs' => JenisTanah::orderBy('jenis')->get(),
        ];
    }
};
?>

<div class="min-h-screen bg-zinc-50/80 dark:bg-zinc-900/80 backdrop-blur-sm p-6 rounded-3xl">
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex items-center justify-between mb-8">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900 rounded-lg">
                        <flux:icon name="globe-asia-australia" class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Tanah</h1>
                        <p class="text-gray-600 dark:text-gray-400">Kelola data tanah dan parameter lingkungan</p>
                    </div>
                </div>
            </div>
            <flux:button wire:click="create" variant="primary" icon="plus">Tambah Tanah</flux:button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if ($tanahs->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/60">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">No</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Alamat</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Jenis</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">pH</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Kelembaban</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Suhu</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Drainase</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @foreach ($tanahs as $index => $tanah)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $tanah->Alamat }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $tanah->jenisTanah?->jenis ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-400">{{ number_format($tanah->PH_Tanah, 2) }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-400">{{ number_format($tanah->Kelembaban_Tanah, 0) }}%</td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-400">{{ number_format($tanah->Suhu_Tanah, 0) }}°C</td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-400">{{ $tanah->drainase ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center space-x-2">
                                        <flux:button wire:click="edit({{ $tanah->id }})" variant="ghost" size="sm" icon="pencil" title="Edit Data Tanah"></flux:button>
                                        <flux:button wire:click="delete({{ $tanah->id }})" variant="danger" size="sm" icon="trash" title="Hapus Data"></flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="inline-block p-4 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                        <flux:icon name="globe-asia-australia" class="text-gray-600 dark:text-gray-400 size-12" />
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 text-lg font-medium">Belum ada data tanah</p>
                    <p class="text-gray-500 dark:text-gray-500 text-sm mt-2">Klik tombol “Tambah Tanah” untuk membuat data baru</p>
                </div>
            @endif
        </div>
    </div>

    @include('livewire.tanah.upsert')
    @include('livewire.tanah.delete')
</div>
