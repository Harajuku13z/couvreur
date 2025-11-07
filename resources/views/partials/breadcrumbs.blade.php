{{-- Breadcrumbs Navigation --}}
@if(isset($breadcrumbs) && is_array($breadcrumbs) && count($breadcrumbs) > 0)
<nav aria-label="Breadcrumb" class="bg-gray-50 py-3">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <ol class="flex items-center space-x-2 text-sm">
            @foreach($breadcrumbs as $index => $breadcrumb)
                @if($index < count($breadcrumbs) - 1)
                    <li class="flex items-center">
                        <a href="{{ $breadcrumb['url'] ?? '#' }}" class="text-gray-500 hover:text-primary transition-colors">
                            {{ $breadcrumb['name'] ?? '' }}
                        </a>
                        <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                    </li>
                @else
                    <li class="text-gray-900 font-medium" aria-current="page">
                        {{ $breadcrumb['name'] ?? '' }}
                    </li>
                @endif
            @endforeach
        </ol>
    </div>
</nav>
@endif

