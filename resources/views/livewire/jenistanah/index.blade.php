<?php

use Livewire\Volt\Component;
use App\Models\JenisTanah;

new class extends Component {
    public bool $showFormModal = false;
    public bool $showDeleteModal = false;
    public bool $isEdit = false;
    public ?JenisTanah $deletingJenisTanah = null;
    public ?int $jenisTanahId = null;

    public array $form = [
        'jenis' => '',
        'ciri_ciri' => '',
    ];

    public function create(): void
    {
        $this->reset('form', 'jenisTanahId');
        $this->isEdit = false;
        $this->showFormModal = true;
    }

    public function edit(JenisTanah $jenisTanah): void
    {
        $this->jenisTanahId = $jenisTanah->id;
        $this->form = $jenisTanah->only(array_keys($this->form));
        $this->isEdit = true;
        $this->showFormModal = true;
    }

    public function delete(JenisTanah $jenisTanah): void
    {
        $this->deletingJenisTanah = $jenisTanah;
        $this->showDeleteModal = true;
    }

    public function destroy(): void
    {
        if ($this->deletingJenisTanah) {
            $this->deletingJenisTanah->delete();
            $this->dispatch('toast', type: 'success', message: 'Jenis tanah berhasil dihapus');
        }

        $this->showDeleteModal = false;
        $this->deletingJenisTanah = null;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->reset('form', 'jenisTanahId');
        $this->isEdit = false;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingJenisTanah = null;
    }

    public function save(): void
    {
        $data = $this->validate([
            'form.jenis' => 'required|string|max:255',
            'form.ciri_ciri' => 'required|string|max:1024',
        ], [
            'form.jenis.required' => 'Nama jenis tanah wajib diisi',
            'form.ciri_ciri.required' => 'Ciri-ciri tanah wajib diisi',
        ])['form'];

        if ($this->isEdit && $this->jenisTanahId) {
            $jenisTanah = JenisTanah::findOrFail($this->jenisTanahId);
            $jenisTanah->update($data);
            $this->dispatch('toast', type: 'success', message: 'Jenis tanah berhasil diupdate');
        } else {
            JenisTanah::create($data);
            $this->dispatch('toast', type: 'success', message: 'Jenis tanah berhasil disimpan');
        }

        $this->showFormModal = false;
        $this->reset('form', 'jenisTanahId');
        $this->isEdit = false;
    }

    public function with(): array
    {
        return [
            'jenisTanahs' => JenisTanah::latest()->get(),
        ];
    }
}; ?>

<div class="min-h-screen bg-zinc-50/80 dark:bg-zinc-900/80 backdrop-blur-sm p-6 rounded-3xl">
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex items-center justify-between mb-8">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-sky-100 dark:bg-sky-900 rounded-lg">
                        <flux:icon name="circle-stack" class="w-6 h-6 text-sky-600 dark:text-sky-400" />
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Jenis Tanah</h1>
                        <p class="text-gray-600 dark:text-gray-400">Kelola tipe dan ciri-ciri tanah</p>
                    </div>
                </div>
            </div>
            <flux:button wire:click="create" variant="primary" icon="plus">Tambah Jenis</flux:button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if ($jenisTanahs->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/60">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">No</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Jenis</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Ciri-ciri</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @foreach ($jenisTanahs as $index => $jenisTanah)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $jenisTanah->jenis }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $jenisTanah->ciri_ciri }}</td>
                                    <td class="px-4 py-3 text-center space-x-2">
                                        <flux:button wire:click="edit({{ $jenisTanah->id }})" variant="ghost" size="sm" icon="pencil" title="Edit Jenis Tanah"></flux:button>
                                        <flux:button wire:click="delete({{ $jenisTanah->id }})" variant="danger" size="sm" icon="trash" title="Hapus Jenis Tanah"></flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="inline-block p-4 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                        <flux:icon name="circle-stack" class="text-gray-600 dark:text-gray-400 size-12" />
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 text-lg font-medium">Belum ada jenis tanah</p>
                    <p class="text-gray-500 dark:text-gray-500 text-sm mt-2">Tambah jenis tanah agar dapat memilihnya saat membuat data tanah</p>
                </div>
            @endif
        </div>
    </div>

    @include('livewire.jenistanah.upsert')
    @include('livewire.jenistanah.delete')
</div>
