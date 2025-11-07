@php
    $recaptchaSiteKey = setting('recaptcha_site_key');
@endphp

@if($recaptchaSiteKey)
<script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('phoneForm') || document.getElementById('emailForm');
    const submitBtn = document.getElementById('submitBtn');
    const recaptchaTokenInput = document.getElementById('recaptcha_token');
    
    if (form && recaptchaTokenInput) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Vérification...';
            }
            
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ $recaptchaSiteKey }}', {action: 'submit'}).then(function(token) {
                    recaptchaTokenInput.value = token;
                    form.submit();
                }).catch(function(error) {
                    console.error('reCAPTCHA error:', error);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Suivant <i class="fas fa-arrow-right ml-2"></i>';
                    }
                    alert('Erreur de vérification. Veuillez réessayer.');
                });
            });
        });
    }
});
</script>
@endif

