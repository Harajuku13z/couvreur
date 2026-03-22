    <!-- Comment ça marche — plein conteneur, design modernisé -->
    <section class="relative py-16 md:py-24 overflow-hidden bg-gradient-to-b from-slate-50 via-white to-slate-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 border-y border-gray-100/80 dark:border-slate-800">
        <div class="site-shell w-full max-w-full">
            <header class="text-center mb-12 md:mb-16 px-1">
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 dark:text-white tracking-tight">
                    Comment ça marche ?
                </h2>
                <div class="w-24 h-1.5 mx-auto mt-5 rounded-full" style="background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));"></div>
                <p class="mt-5 text-lg md:text-xl text-gray-600 dark:text-slate-400 max-w-3xl mx-auto leading-relaxed">
                    Un processus simple et transparent en <span class="font-semibold text-gray-800 dark:text-slate-200">4 étapes</span>
                </p>
            </header>

            {{-- Ligne de progression (desktop xl uniquement) --}}
            <div class="relative w-full">
                <div class="hidden xl:block absolute top-14 left-[8%] right-[8%] h-[3px] rounded-full opacity-40 pointer-events-none z-0"
                     style="background: linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--accent-color));"></div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 lg:gap-8 xl:gap-5 relative z-[1] w-full">
                    @php
                        $steps = [
                            [
                                'n' => 1,
                                'title' => 'Demande de devis',
                                'text' => 'Remplissez notre formulaire en ligne pour recevoir un devis personnalisé et gratuit.',
                                'icon' => 'fas fa-file-signature',
                            ],
                            [
                                'n' => 2,
                                'title' => 'Étude du projet',
                                'text' => 'Nos experts analysent vos besoins et vous proposent la meilleure solution.',
                                'icon' => 'fas fa-search-location',
                            ],
                            [
                                'n' => 3,
                                'title' => 'Planification',
                                'text' => 'Nous planifions les travaux selon vos disponibilités et nos délais d’intervention.',
                                'icon' => 'fas fa-calendar-check',
                            ],
                            [
                                'n' => 4,
                                'title' => 'Réalisation',
                                'text' => 'Nos équipes qualifiées réalisent vos travaux avec professionnalisme et qualité.',
                                'icon' => 'fas fa-hard-hat',
                            ],
                        ];
                    @endphp

                    @foreach($steps as $step)
                    <article class="group relative flex flex-col h-full min-w-0">
                        <div class="flex-1 flex flex-col rounded-2xl md:rounded-3xl border border-gray-200/90 dark:border-slate-700 bg-white dark:bg-slate-800/90 shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-[color:var(--primary-color)]/30 overflow-hidden">
                            {{-- Bandeau couleur en haut --}}
                            <div class="h-1.5 w-full shrink-0" style="background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));"></div>
                            <div class="p-6 sm:p-7 md:p-8 flex flex-col flex-1 text-center sm:text-left">
                                <div class="flex items-center justify-center sm:justify-between gap-4 mb-5">
                                    <span class="inline-flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-2xl text-white text-xl sm:text-2xl font-bold shadow-lg shrink-0"
                                          style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                                        {{ $step['n'] }}
                                    </span>
                                    <span class="hidden sm:flex w-12 h-12 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-700/80 text-slate-600 dark:text-slate-300 group-hover:text-[color:var(--primary-color)] transition-colors">
                                        <i class="{{ $step['icon'] }} text-xl" aria-hidden="true"></i>
                                    </span>
                                </div>
                                <div class="flex justify-center sm:hidden mb-4 -mt-2 text-3xl opacity-90" style="color: var(--primary-color);">
                                    <i class="{{ $step['icon'] }}" aria-hidden="true"></i>
                                </div>
                                <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-3 leading-snug">
                                    {{ $step['title'] }}
                                </h3>
                                <p class="text-sm sm:text-base text-gray-600 dark:text-slate-300 leading-relaxed flex-1">
                                    {{ $step['text'] }}
                                </p>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
