@if ($paginator->hasPages())
    <ul class="pagination">
        {{-- 前へ --}}
        @if ($paginator->onFirstPage())
            <li class="disabled"><span>前へ</span></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">前へ</a></li>
        @endif

        {{-- ページ番号 --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="disabled"><span>{{ $element }}</span></li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active"><span>{{ $page }}</span></li>
                    @else
                        <li><a href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- 次へ --}}
        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">次へ</a></li>
        @else
            <li class="disabled"><span>次へ</span></li>
        @endif
    </ul>
@endif
