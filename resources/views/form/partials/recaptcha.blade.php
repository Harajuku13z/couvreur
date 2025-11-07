@php
    $recaptchaSiteKey = setting('recaptcha_site_key');
@endphp

@if($recaptchaSiteKey)
<script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Charger reCAPTCHA pour toutes les pages du formulaire
    // Le script est déjà chargé, les fonctions personnalisées dans chaque page gèrent la soumission
    console.log('reCAPTCHA v3 chargé pour le formulaire');
});
</script>
@endif

