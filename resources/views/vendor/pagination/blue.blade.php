@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col sm:flex-row items-center justify-between gap-3">

        <p class="text-sm text-gray-600">
            Menampilkan <span class="font-semibold">{{ $paginator->firstItem() }}</span>
            sampai <span class="font-semibold">{{ $paginator->lastItem() }}</span>
            dari <span class="font-semibold">{{ $paginator->total() }}</span> data
        </p>

        <div class="inline-flex rounded-lg border border-blue-600 overflow-hidden divide-x divide-blue-600 text-sm font-semibold">

            {{-- Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="px-4 py-2 bg-white text-blue-300 cursor-not-allowed" aria-disabled="true">
                    <i class="fa-solid fa-chevron-left"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya"
                   class="px-4 py-2 bg-white text-blue-600 hover:bg-blue-50 transition">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            @endif

            {{-- Nomor halaman --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-4 py-2 bg-white text-gray-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="px-4 py-2 bg-blue-600 text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Halaman {{ $page }}"
                               class="px-4 py-2 bg-white text-blue-600 hover:bg-blue-50 transition">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya"
                   class="px-4 py-2 bg-white text-blue-600 hover:bg-blue-50 transition">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            @else
                <span class="px-4 py-2 bg-white text-blue-300 cursor-not-allowed" aria-disabled="true">
                    <i class="fa-solid fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </nav>
@endif