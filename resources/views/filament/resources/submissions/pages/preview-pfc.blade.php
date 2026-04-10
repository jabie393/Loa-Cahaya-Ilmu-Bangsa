<div x-data="{ print: {{ request()->has('print') ? 'true' : 'false' }} }" x-init="if (print) { setTimeout(() => { window.print(); }, 500); }">
    @include('filament.pfc.pfc_pdf', ['record' => $this->record])
</div>
