<div x-data="{
    isFs: false,
    showPrompt: true,
    async toggleFS(el = document.documentElement) {
        try {
            if (!document.fullscreenElement) {
                await el.requestFullscreen();
                this.isFs = true;
            } else {
                await document.exitFullscreen();
                this.isFs = false;
            }
        } catch (e) {
            console.error(e);
        }
    },
    cancelFullscreenPrompt() {
        // Redirect kalau user tolak
        window.location.href = '/';
    }
}"
    class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-2 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
    <template x-if="showPrompt">
        <div class="bg-white p-6 rounded-md shadow-md text-center">
            <h2 class="text-xl font-bold mb-4">Masuk Fullscreen?</h2>
            <div class="flex justify-center gap-4">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
                    @click="toggleFS(); showPrompt = false">
                    Ya, Lanjutkan
                </button>
                <button class="bg-gray-300 hover:bg-gray-400 text-black px-4 py-2 rounded"
                    @click="cancelFullscreenPrompt()">
                    Tidak
                </button>
            </div>
        </div>
    </template>

    <template x-if="!showPrompt">
        <div class="m-2 w-full max-w-4xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">LHK Digital</h2>
                <div class="flex gap-2">
                    <button type="button" @click="toggleFS()"
                        class="bg-white border border-gray-300 shadow-md rounded-lg px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-100 transition"
                        :title="isFs ? 'Exit Fullscreen' : 'Enter Fullscreen'">
                        <span x-text="isFs ? 'Exit Fullscreen' : 'Fullscreen'"></span>
                    </button>
                    <a href="/"
                        class="bg-white border border-gray-300 shadow-md rounded-lg px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-100 transition">
                        ← Back to Home
                    </a>
                </div>
            </div>

            <h4 class="text-sm font-bold mb-4">Laporan Hasil Kerja Digital</h4>

            <div class="bg-white p-6 rounded-md shadow-sm">
                <form wire:submit="create">
                    {{ $this->form }}

                    <div class="flex justify-end">
                        {{-- <x-filament::button form="create" type="submit"
                            class="bg-primary-500 mt-4 lg:w-40 sm:w-auto hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                            Submit
                        </x-filament::button> --}}
                        {{-- {{ $this->submitForm }} --}}
                        <div class="mt-4">
                            {{ $this->createAction }}
                        </div>
                    </div>
                </form>
            </div>

            <x-filament-actions::modals />
        </div>
    </template>
</div>
