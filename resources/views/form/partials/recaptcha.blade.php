@php
    $recaptchaSiteKey = setting('recaptcha_site_key');
    $recaptchaSecretKey = setting('recaptcha_secret_key');
    $recaptchaEnabled = setting('recaptcha_enabled', false);
@endphp

@if($recaptchaEnabled && $recaptchaSiteKey && $recaptchaSecretKey)
<script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}" async defer></script>
<script>
// S'assurer que reCAPTCHA est chargé avant utilisation
window.recaptchaReady = false;

document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si grecaptcha est déjà disponible
    if (typeof grecaptcha !== 'undefined') {
        window.recaptchaReady = true;
        console.log('✅ reCAPTCHA v3 chargé pour le formulaire');
    } else {
        // Attendre le chargement du script
        var checkRecaptcha = setInterval(function() {
            if (typeof grecaptcha !== 'undefined') {
                window.recaptchaReady = true;
                clearInterval(checkRecaptcha);
                console.log('✅ reCAPTCHA v3 chargé pour le formulaire (chargement différé)');
            }
        }, 100);
        
        // Timeout après 5 secondes
        setTimeout(function() {
            if (!window.recaptchaReady) {
                clearInterval(checkRecaptcha);
                console.warn('⚠️ reCAPTCHA v3 n\'a pas pu être chargé dans les temps');
            }
        }, 5000);
    }
});
</script>
@endif

