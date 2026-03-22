    <!-- Stats Section -->
    @if(!empty($homeConfig['stats']))
    <section class="py-16 bg-white">
        <div class="site-shell">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                @foreach($homeConfig['stats'] as $stat)
                <div class="text-center">
                    <div class="w-20 h-20 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas {{ $stat['icon'] }} text-white text-2xl"></i>
                    </div>
                    <div class="text-4xl font-bold text-gray-900 mb-2">{{ $stat['value'] }}</div>
                    <div class="text-gray-600 font-medium">{{ $stat['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
