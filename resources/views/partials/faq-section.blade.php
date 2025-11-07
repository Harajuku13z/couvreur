{{-- Section FAQ Interactive --}}
@if(isset($faqs) && is_array($faqs) && count($faqs) > 0)
<section class="py-20 bg-white" id="faq">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-800 mb-4">
                {{ setting('faq_title', 'Questions Fréquentes') }}
            </h2>
            <p class="text-xl text-gray-600">
                {{ setting('faq_subtitle', 'Trouvez rapidement les réponses à vos questions') }}
            </p>
        </div>
        
        {{-- Barre de recherche FAQ --}}
        <div class="mb-8">
            <div class="relative">
                <input type="text" 
                       id="faqSearch" 
                       placeholder="Rechercher dans les questions..." 
                       class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>
        
        {{-- Liste des FAQ --}}
        <div class="space-y-4" id="faqList">
            @foreach($faqs as $index => $faq)
            <div class="faq-item bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow" 
                 data-question="{{ strtolower($faq['question'] ?? '') }}" 
                 data-answer="{{ strtolower($faq['answer'] ?? '') }}">
                <button class="w-full px-6 py-4 text-left flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-primary rounded-lg" 
                        onclick="toggleFaq({{ $index }})">
                    <span class="font-semibold text-gray-800 pr-4">{{ $faq['question'] ?? '' }}</span>
                    <i class="fas fa-chevron-down text-primary faq-icon-{{ $index }} transition-transform"></i>
                </button>
                <div class="faq-answer-{{ $index }} hidden px-6 pb-4">
                    <div class="text-gray-600 leading-relaxed">
                        {!! nl2br(e($faq['answer'] ?? '')) !!}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        @if(empty($faqs))
        <div class="text-center py-12 text-gray-500">
            <i class="fas fa-question-circle text-4xl mb-4"></i>
            <p>Aucune question fréquente disponible pour le moment.</p>
        </div>
        @endif
    </div>
</section>

<script>
function toggleFaq(index) {
    const answer = document.querySelector(`.faq-answer-${index}`);
    const icon = document.querySelector(`.faq-icon-${index}`);
    
    if (answer.classList.contains('hidden')) {
        // Fermer toutes les autres FAQ
        document.querySelectorAll('[class*="faq-answer-"]').forEach(el => {
            if (!el.classList.contains(`faq-answer-${index}`)) {
                el.classList.add('hidden');
            }
        });
        document.querySelectorAll('[class*="faq-icon-"]').forEach(el => {
            if (!el.classList.contains(`faq-icon-${index}`)) {
                el.classList.remove('rotate-180');
            }
        });
        
        // Ouvrir celle-ci
        answer.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        answer.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}

// Recherche dans les FAQ
document.getElementById('faqSearch')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.getAttribute('data-question') || '';
        const answer = item.getAttribute('data-answer') || '';
        
        if (question.includes(searchTerm) || answer.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>
@endif

