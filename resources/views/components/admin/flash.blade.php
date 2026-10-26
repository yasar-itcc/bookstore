@if (session('status'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
         class="mb-5 flex items-center gap-3 rounded-md bg-leaf-soft px-4 py-3 text-sm text-leaf-dark" role="status">
        <i class="ti ti-circle-check text-xl"></i>
        <span class="flex-1">{{ session('status') }}</span>
        <button type="button" @click="show = false" aria-label="Dismiss"><i class="ti ti-x"></i></button>
    </div>
@endif

@if (session('warning'))
    <div class="mb-5 flex items-center gap-3 rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-800" role="alert">
        <i class="ti ti-alert-triangle text-xl"></i>
        <span>{{ session('warning') }}</span>
    </div>
@endif
