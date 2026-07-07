@if ($showDeleteModal && $deletingJenisTanah)
    <flux:modal wire:model="showDeleteModal" class="md:w-[400px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Konfirmasi Hapus Jenis Tanah</flux:heading>
                <flux:text class="mt-1">
                    Apakah Anda yakin ingin menghapus jenis tanah berikut?
                </flux:text>
            </div>

            <div class="space-y-2 rounded-xl bg-gray-50 p-4 dark:bg-gray-700/50">
                <p><strong>Jenis:</strong> {{ $deletingJenisTanah->jenis }}</p>
                <p><strong>Ciri-ciri:</strong> {{ $deletingJenisTanah->ciri_ciri }}</p>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="closeDeleteModal">
                    Batal
                </flux:button>

                <flux:button variant="primary" color="red" wire:click="destroy">
                    Hapus
                </flux:button>
            </div>
        </div>
    </flux:modal>
@endif
