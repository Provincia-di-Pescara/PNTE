@if(session()->has('impersonated_by'))
<div class="bg-amber-500 text-white text-[13px] font-medium px-4 py-2 flex items-center justify-between shrink-0">
    <div class="flex items-center gap-2">
        <x-icon name="user" size="14" />
        <span>Stai operando come <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->getRoleNames()->first() }})</span>
    </div>
    <form method="POST" action="{{ route('admin.impersonate.leave') }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-white underline text-[12px] hover:no-underline">
            Termina impersonazione
        </button>
    </form>
</div>
@endif
