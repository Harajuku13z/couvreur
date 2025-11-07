{{-- Popup de sortie (Exit Intent) --}}
@if(setting('exit_popup_enabled', false))
<div id="exitPopup" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 relative animate-fadeIn">
        <button onclick="closeExitPopup()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
        </button>
        
        <div class="p-8 text-center">
            <div class="w-20 h-20 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-gift text-white text-3xl"></i>
            </div>
            
            <h3 class="text-2xl font-bold text-gray-800 mb-2">
                {{ setting('exit_popup_title', 'Ne partez pas !') }}
            </h3>
            
            <p class="text-gray-600 mb-6">
                {{ setting('exit_popup_message', 'Obtenez votre devis gratuit en moins de 2 minutes !') }}
            </p>
            
            <div class="space-y-3">
                <a href="{{ route('form.step', 'propertyType') }}" 
                   class="block w-full bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-secondary transition-colors">
                    <i class="fas fa-calculator mr-2"></i>
                    {{ setting('exit_popup_button_text', 'Demander mon devis gratuit') }}
                </a>
                
                <button onclick="closeExitPopup()" class="text-gray-500 hover:text-gray-700 text-sm">
                    Non merci, je continue
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let exitPopupShown = false;

// Détecter la sortie de la souris (exit intent)
document.addEventListener('mouseout', function(e) {
    if (!exitPopupShown && !e.toElement && !e.relatedTarget && e.clientY < 10) {
        showExitPopup();
    }
});

function showExitPopup() {
    const popup = document.getElementById('exitPopup');
    if (popup && !exitPopupShown) {
        popup.classList.remove('hidden');
        popup.classList.add('flex');
        exitPopupShown = true;
        
        // Ne pas réafficher pendant 1 jour
        localStorage.setItem('exitPopupShown', Date.now().toString());
    }
}

function closeExitPopup() {
    const popup = document.getElementById('exitPopup');
    if (popup) {
        popup.classList.add('hidden');
        popup.classList.remove('flex');
    }
}

// Ne pas afficher si déjà vu aujourd'hui
if (localStorage.getItem('exitPopupShown')) {
    const lastShown = parseInt(localStorage.getItem('exitPopupShown'));
    const oneDay = 24 * 60 * 60 * 1000;
    if (Date.now() - lastShown < oneDay) {
        exitPopupShown = true;
    }
}
</script>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}
</style>
@endif

