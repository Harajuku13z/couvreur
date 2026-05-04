    <!-- Barre de stats — fond blanc -->
    <section class="py-14 bg-white border-b border-gray-100">
        <div class="site-shell">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
                @php
                    $statsData = [
                        [
                            'icon'  => 'fas fa-tree',
                            'color' => 'emerald',
                            'val'   => '500+',
                            'label' => 'Arbres traités',
                            'sub'   => 'dans l\'Oise (60)',
                        ],
                        [
                            'icon'  => 'fas fa-calendar-check',
                            'color' => 'blue',
                            'val'   => '15+',
                            'label' => 'Ans d\'expérience',
                            'sub'   => 'élagage & abattage',
                        ],
                        [
                            'icon'  => 'fas fa-map-marker-alt',
                            'color' => 'violet',
                            'val'   => '60+',
                            'label' => 'Communes couvertes',
                            'sub'   => 'département Oise',
                        ],
                        [
                            'icon'  => 'fas fa-star',
                            'color' => 'amber',
                            'val'   => $averageRating > 0 ? number_format($averageRating,1).'/5' : '4.9/5',
                            'label' => 'Note clients',
                            'sub'   => ($totalReviews > 0 ? $totalReviews . ' avis vérifiés' : 'Avis vérifiés'),
                        ],
                    ];
                    $colorsMap = [
                        'emerald' => ['bg'=>'bg-emerald-50','icon'=>'text-emerald-600','bar'=>'bg-emerald-500'],
                        'blue'    => ['bg'=>'bg-blue-50',   'icon'=>'text-blue-600',   'bar'=>'bg-blue-500'],
                        'violet'  => ['bg'=>'bg-violet-50', 'icon'=>'text-violet-600', 'bar'=>'bg-violet-500'],
                        'amber'   => ['bg'=>'bg-amber-50',  'icon'=>'text-amber-600',  'bar'=>'bg-amber-500'],
                    ];
                @endphp

                @foreach($statsData as $stat)
                @php $c = $colorsMap[$stat['color']]; @endphp
                <div class="group flex flex-col items-center text-center p-6 rounded-2xl border border-gray-100 hover:border-transparent hover:shadow-xl transition-all duration-300 bg-white hover:bg-gray-50 cursor-default">
                    <div class="w-14 h-14 rounded-2xl {{ $c['bg'] }} flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="{{ $stat['icon'] }} {{ $c['icon'] }} text-xl"></i>
                    </div>
                    <div class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-1 leading-none">
                        {{ $stat['val'] }}
                    </div>
                    <div class="font-bold text-gray-800 text-sm">{{ $stat['label'] }}</div>
                    <div class="text-gray-400 text-xs mt-0.5">{{ $stat['sub'] }}</div>
                    <div class="mt-3 w-8 h-1 rounded-full {{ $c['bar'] }} opacity-60 group-hover:w-14 transition-all duration-500"></div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
