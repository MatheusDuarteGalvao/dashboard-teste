<div class="bg-white rounded-lg shadow-sm p-4 flex flex-col justify-between">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-sm text-gray-500">{{ $title }}</h3>
            <p class="mt-1 text-2xl font-semibold text-gray-800">{{ $value }}</p>
        </div>
        @if(!empty($icon))
            <div class="bg-gray-100 p-2 rounded-full">
                {!! $icon !!}
            </div>
        @endif
    </div>
    @if(!empty($meta))
        <div class="mt-3 text-xs text-gray-500">{{ $meta }}</div>
    @endif
</div>
