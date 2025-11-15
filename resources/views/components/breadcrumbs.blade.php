@props(['breadcrumbs' => []])

@if(!empty($breadcrumbs) && count($breadcrumbs) > 1)
<nav aria-label="Fil d'Ariane" class="mb-6">
    <ol itemscope itemtype="https://schema.org/BreadcrumbList" class="flex flex-wrap items-center gap-2 text-sm text-gray-600">
        @foreach($breadcrumbs as $index => $item)
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="flex items-center">
                @if(isset($item['url']) && $item['url'] && $index < count($breadcrumbs) - 1)
                    <a itemprop="item" href="{{ $item['url'] }}" class="hover:text-blue-600 transition-colors">
                        <span itemprop="name">{{ $item['label'] }}</span>
                    </a>
                    <meta itemprop="position" content="{{ $index + 1 }}">
                @else
                    <span itemprop="name" class="text-gray-900 font-medium">{{ $item['label'] }}</span>
                    <meta itemprop="position" content="{{ $index + 1 }}">
                @endif
                @if($index < count($breadcrumbs) - 1)
                    <span class="mx-2 text-gray-400">/</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif

