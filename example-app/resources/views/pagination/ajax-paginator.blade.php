{{-- 独立分页组件（异步版）：
      - 上一页 / 下一页
      - 数字页码窗（带省略号）
      - 每页条数选择
      - 跳转到指定页
      - 共 N 条数据
      用法：@include('pagination.ajax-paginator', [
            'paginator'      => $topics,
            'perPageName'    => 'per_page',
            'perPageOptions' => [10, 15, 20, 50, 100],
            ]) --}}
@php
    $paginator      = $paginator ?? null;
    $perPageName    = $perPageName ?? 'per_page';
    $perPageOptions = $perPageOptions ?? [10, 15, 20, 50, 100];

    // 取出当前每页条数（来自 query / paginator）
    $currentPerPage = (int) ($paginator ? $paginator->perPage() : 15);
    if (!in_array($currentPerPage, $perPageOptions, true)) {
        $perPageOptions[] = $currentPerPage;
        sort($perPageOptions);
    }

    $hasPaginator = $paginator && method_exists($paginator, 'hasPages');
    $hasMultiplePages = $paginator && (int) $paginator->lastPage() > 1;
@endphp

@if ($paginator && (int) $paginator->total() > 0)
    @php
        $current = (int) $paginator->currentPage();
        $last    = (int) $paginator->lastPage();
        $side    = 2;
        $start   = max(1, $current - $side);
        $end     = min($last, $current + $side);
        $total   = (int) $paginator->total();
    @endphp

    <div class="pagination-wrap" data-ajax-pagination
         data-per-page-name="{{ $perPageName }}"
         data-last-page="{{ $last }}">

        {{-- 左侧：翻页控件（上一页 / 页码窗 / 下一页） --}}
        <div class="pg-left">
            @if ($hasMultiplePages)
                {{-- 上一页 --}}
                @if ($paginator->onFirstPage())
                    <span class="page-btn disabled" title="已是第一页">‹</span>
                @else
                    <a class="page-btn" href="{{ $paginator->url($current - 1) }}" title="上一页" rel="prev">‹</a>
                @endif

                {{-- 左侧省略号 --}}
                @if ($start > 1)
                    <a class="page-num" href="{{ $paginator->url(1) }}">1</a>
                    @if ($start > 2)
                        <span class="ellipsis">…</span>
                    @endif
                @endif

                {{-- 数字页码（当前页为椭圆高亮） --}}
                @for ($i = $start; $i <= $end; $i++)
                    @if ($i == $current)
                        <span class="page-num active">{{ $i }}</span>
                    @else
                        <a class="page-num" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                    @endif
                @endfor

                {{-- 右侧省略号 --}}
                @if ($end < $last)
                    @if ($end < $last - 1)
                        <span class="ellipsis">…</span>
                    @endif
                    <a class="page-num" href="{{ $paginator->url($last) }}">{{ $last }}</a>
                @endif

                {{-- 下一页 --}}
                @if ($paginator->hasMorePages())
                    <a class="page-btn" href="{{ $paginator->url($current + 1) }}" title="下一页" rel="next">›</a>
                @else
                    <span class="page-btn disabled" title="已是最后一页">›</span>
                @endif
            @else
                {{-- 只有一页时，简洁显示当前页标识，避免视觉空洞 --}}
                <span class="page-num active">1</span>
            @endif
        </div>

        {{-- 中间：每页条数 + 跳转到指定页 --}}
        <div class="pg-mid">
            <span class="page-text">每页</span>
            <select class="per-page-select" aria-label="每页条数">
                @foreach ($perPageOptions as $pp)
                    <option value="{{ $pp }}"{{ $pp == $currentPerPage ? ' selected' : '' }}>{{ $pp }} 条/页</option>
                @endforeach
            </select>

            <span class="page-text" style="margin-left:6px;">跳转</span>
            <input class="page-jump-input" type="number" min="1" max="{{ $last }}" value="{{ $current }}" aria-label="跳转页码">
            <span class="page-text">页</span>
            <button type="button" class="page-jump-btn">GO</button>
        </div>

        {{-- 右侧：汇总 --}}
        <div class="pg-right">
            共 <span class="total-num">{{ $total }}</span> 条数据
        </div>
    </div>
@endif