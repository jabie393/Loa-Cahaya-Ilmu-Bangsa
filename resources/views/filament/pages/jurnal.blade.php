<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($this->getJurnals() as $jurnal)
            <div class="group relative flex flex-col overflow-hidden rounded-3xl bg-white shadow-[0_20px_50px_rgba(0,0,0,0.05)] transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_40px_80px_rgba(37,99,235,0.08)] dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                <!-- Cover Image Container -->
                <div class="relative aspect-[3/4] overflow-hidden">
                    <img src="{{ $jurnal['cover'] }}" 
                         alt="{{ $jurnal['title'] }}"
                         class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110">
                    
                    <!-- Overlay Gradient -->
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                </div>

                <!-- Content Area -->
                <div class="flex flex-1 flex-col p-6">
                    <h3 class="mb-2 text-xl font-bold tracking-tight text-slate-900 transition-colors duration-300 group-hover:text-blue-600 dark:text-white">
                        {{ $jurnal['title'] }}
                    </h3>
                    <!-- Buttons Group -->
                    <div class="mt-auto grid grid-cols-2 gap-3">
                        <a href="{{ $jurnal['url'] }}" class="relative flex items-center justify-center overflow-hidden rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white transition-all duration-300 hover:bg-blue-700 active:scale-95 shadow-[0_10px_20px_rgba(37,99,235,0.2)]">
                            Situs Jurnal
                        </a>
                        <a href="" class="flex items-center justify-center gap-1.5 rounded-xl border-2 border-slate-100 bg-slate-50 px-4 py-2.5 text-xs font-bold text-slate-900 transition-all duration-300 hover:border-blue-100 hover:bg-blue-50 hover:text-blue-600 active:scale-95 dark:border-slate-800 dark:bg-slate-800 dark:text-white">
                            Request
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Additional Modern CSS -->
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</x-filament-panels::page>
