<div x-data="{ print: {{ request()->has('print') ? 'true' : 'false' }} }" x-init="if (print) { setTimeout(() => { window.print(); }, 500); }">
@php
    $view = $this->getTemplateView();
@endphp

@if (view()->exists($view))
    @include($view, ['record' => $this->record])
@else
    <div class="p-8 text-center text-gray-500">
        Template LOA untuk jurnal ini ({{ $view }}) belum tersedia.
    </div>
@endif
</div>
