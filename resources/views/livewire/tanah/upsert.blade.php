@if ($showFormModal)
    <flux:modal wire:model="showFormModal" class="md:w-[600px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $isEdit ? 'Edit Data Tanah' : 'Tambah Data Tanah' }}
                </flux:heading>
                <flux:text class="mt-1">
                    Lengkapi data tanah berikut
                </flux:text>
            </div>

            <div class="space-y-4">
                <flux:input label="Alamat" wire:model.defer="form.Alamat" />

                <flux:select label="Jenis Tanah" wire:model.defer="form.jenis_tanah_id">
                    <option value="">Pilih</option>
                    @foreach ($jenisTanahs as $jenisTanah)
                        <option value="{{ $jenisTanah->id }}">{{ $jenisTanah->jenis }}</option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:input label="pH Tanah" type="number" step="0.01" wire:model.defer="form.PH_Tanah" />
                    <flux:input label="Kelembaban Tanah (%)" type="number" step="0.01" wire:model.defer="form.Kelembaban_Tanah" />
                    <flux:input label="Suhu Tanah (°C)" type="number" step="0.01" wire:model.defer="form.Suhu_Tanah" />
                    <flux:select label="Drainase" wire:model.defer="form.drainase">
                        <option value="">Pilih</option>
                        <option value="Baik">Baik</option>
                        <option value="Sedang">Sedang</option>
                        <option value="Buruk">Buruk</option>
                    </flux:select>
                </div>
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
