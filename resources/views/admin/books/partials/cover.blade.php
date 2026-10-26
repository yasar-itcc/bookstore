{{-- Book cover thumbnail, or a placeholder when there is no image. --}}
@if ($book->coverUrl())
    <img src="{{ $book->coverUrl() }}" alt="" class="{{ $size ?? 'size-10' }} shrink-0 rounded object-cover">
@else
    <span class="{{ $size ?? 'size-10' }} flex shrink-0 items-center justify-center rounded bg-primary-soft text-primary"><i class="ti ti-book"></i></span>
@endif
