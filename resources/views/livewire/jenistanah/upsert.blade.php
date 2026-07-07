@if ($showFormModal)
    <flux:modal wire:model="showFormModal" class="md:w-[600px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $isEdit ? 'Edit Jenis Tanah' : 'Tambah Jenis Tanah' }}
                </flux:heading>
                <flux:text class="mt-1">
                    Kelola nama dan ciri-ciri tanah yang dapat dipilih di form Tanah.
                </flux:text>
            </div>

            <div class="space-y-4">
                <flux:input label="Jenis Tanah" wire:model.defer="form.jenis" />

                <flux:textarea label="Ciri-ciri" wire:model.defer="form.ciri_ciri" rows="4" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="closeFormModal">
                    Batal
                </flux:button>

                <flux:button variant="primary" wire:click="save">
                    {{ $isEdit ? 'Update' : 'Simpan' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
@endif
