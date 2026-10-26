@props(['active' => true, 'on' => 'Active', 'off' => 'Inactive'])

<span @class([
    'inline-flex items-center rounded px-2 py-0.5 text-xs font-medium',
    'bg-leaf-soft text-leaf-dark' => $active,
    'bg-slate-100 text-slate-500' => ! $active,
])>{{ $active ? $on : $off }}</span>
