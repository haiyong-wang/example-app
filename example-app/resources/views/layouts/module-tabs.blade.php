@php
    $tab = $activeTab ?? '';
    $toolsUrl = $toolsUrl ?? url('/tools');
@endphp
<!-- 模块切换 Tab -->
<div class="tabs">
    <a class="tab {{ $tab === 'products' ? 'active' : '' }}" href="{{ url('/products') }}">手机产品</a>
    <a class="tab {{ $tab === 'daily' ? 'active' : '' }}" href="{{ url('/reports/daily') }}">机型筛选</a>
    <a class="tab {{ $tab === 'tools' ? 'active' : '' }}" href="{{ $toolsUrl }}">实用工具</a>
</div>
