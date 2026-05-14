@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $startPage = max(1, $currentPage - 1);
        $endPage = min($lastPage, $currentPage + 1);

        if ($currentPage <= 2) {
            $endPage = min($lastPage, 3);
        }

        if ($currentPage >= $lastPage - 1) {
            $startPage = max(1, $lastPage - 2);
        }
    @endphp

    <div class="mt-6 flex items-center justify-between gap-4 rounded-xl border border-slate-800 bg-[#07111f] p-4">
        <div class="text-xs font-medium text-slate-500">
            Showing <span class="text-slate-300 font-bold">{{ $paginator->firstItem() }}</span> to 
            <span class="text-slate-300 font-bold">{{ $paginator->lastItem() }}</span> of 
            <span class="text-slate-300 font-bold">{{ $paginator->total() }}</span> results
        </div>

        <div class="flex gap-2">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <button disabled class="inline-flex items-center gap-1 rounded-lg border border-slate-800 bg-slate-950 px-3 py-2 text-xs font-semibold text-slate-600 cursor-not-allowed">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-700 bg-[#07111f] px-3 py-2 text-xs font-semibold text-slate-300 hover:border-cyan-400/30 hover:text-cyan-300 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                </a>
            @endif

            {{-- Pagination Elements --}}
            <div class="flex gap-1">
                @if ($startPage > 1)
                    <a href="{{ $paginator->url(1) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-700 text-xs font-semibold text-slate-300 hover:border-cyan-400/30 hover:text-cyan-300 transition">
                        1
                    </a>
                @endif

                @if ($startPage > 2)
                    <span class="flex items-center text-slate-500">...</span>
                @endif

                @for ($page = $startPage; $page <= $endPage; $page++)
                    @if ($page == $paginator->currentPage())
                        <button disabled class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-cyan-400/30 bg-cyan-400/10 text-xs font-bold text-cyan-300">
                            {{ $page }}
                        </button>
                    @else
                        <a href="{{ $paginator->url($page) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-700 text-xs font-semibold text-slate-300 hover:border-cyan-400/30 hover:text-cyan-300 transition">
                            {{ $page }}
                        </a>
                    @endif
                @endfor

                @if ($endPage < $lastPage - 1)
                    <span class="flex items-center text-slate-500">...</span>
                @endif

                @if ($endPage < $lastPage)
                    <a href="{{ $paginator->url($paginator->lastPage()) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-700 text-xs font-semibold text-slate-300 hover:border-cyan-400/30 hover:text-cyan-300 transition">
                        {{ $paginator->lastPage() }}
                    </a>
                @endif
            </div>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-700 bg-[#07111f] px-3 py-2 text-xs font-semibold text-slate-300 hover:border-cyan-400/30 hover:text-cyan-300 transition">
                    Next
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <button disabled class="inline-flex items-center gap-1 rounded-lg border border-slate-800 bg-slate-950 px-3 py-2 text-xs font-semibold text-slate-600 cursor-not-allowed">
                    Next
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            @endif
        </div>
    </div>
@endif
