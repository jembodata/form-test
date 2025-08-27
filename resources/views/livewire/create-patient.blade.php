<div
    class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-2 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
    <div class="m-2">

        <h2 class="text-2xl font-bold mb-4">LHK Digital</h2>
        <h4 class="text-sm font-bold mb-4">Laporan Hasil Kerja Digital</h4>

        <div class="bg-white p-6 rounded-md shadow-sm">
            <form wire:submit="create">
                {{ $this->form }}

                <button type="submit" class="bg-primary-600 text-white font-bold rounded-md mt-4 p-2">
                    Create
                </button>

            </form>
        </div>

        <x-filament-actions::modals />
    </div>
</div>
