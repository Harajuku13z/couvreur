/**
 * Service de tracking des appels téléphoniques
 * Système robuste avec queue, retry et fallback
 */

(function() {
    'use strict';
    
    // Configuration
    const TRACKING_ENDPOINT = '/api/track-phone-call';
    const RETRY_DELAYS = [100, 500, 1000, 2000];
    
    // État global
    window.phoneCallTrackingQueue = window.phoneCallTrackingQueue || [];
    window.phoneCallTrackingInProgress = window.phoneCallTrackingInProgress || false;
    window.phoneCallTrackingInitialized = window.phoneCallTrackingInitialized || false;
    
    /**
     * Fonction principale de tracking
     */
    window.trackPhoneCall = function(phoneNumber = null, sourcePage = null) {
        const phone = phoneNumber || getDefaultPhoneNumber();
        const page = sourcePage || window.location.pathname;
        
        const payload = {
            source_page: page,
            phone_number: phone,
            referrer_url: document.referrer || window.location.href
        };
        
        // Ajouter à la queue si un envoi est en cours
        if (window.phoneCallTrackingInProgress) {
            window.phoneCallTrackingQueue.push(payload);
            console.log('📞 Appel ajouté à la queue');
            return;
        }
        
        // Envoyer immédiatement
        sendPhoneCallTracking(payload);
    };
    
    /**
     * Envoyer le tracking avec plusieurs méthodes de fallback
     */
    function sendPhoneCallTracking(payload) {
        window.phoneCallTrackingInProgress = true;
        const data = JSON.stringify(payload);
        const csrfToken = getCsrfToken();
        
        // Méthode 1: sendBeacon (le plus fiable pour les liens tel:)
        if (navigator.sendBeacon) {
            try {
                const formData = new FormData();
                formData.append('data', data);
                const sent = navigator.sendBeacon(TRACKING_ENDPOINT, formData);
                if (sent) {
                    console.log('✅ Tracking envoyé via sendBeacon');
                    processQueue();
                    return;
                }
            } catch (e) {
                console.warn('sendBeacon failed, trying fetch:', e);
            }
        }
        
        // Méthode 2: fetch avec keepalive
        fetch(TRACKING_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: data,
            keepalive: true
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
            .then(data => {
                if (data.success) {
                    console.log('✅ Appel tracké (ID: ' + (data.id || 'N/A') + ')');
                    
                    // Envoyer l'événement à Google Analytics
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'phone_call', {
                            'event_category': 'Contact',
                            'event_label': payload.source_page,
                            'value': 1,
                            'phone_number': payload.phone_number,
                            'source_page': payload.source_page,
                            'referrer_url': payload.referrer_url
                        });
                        console.log('✅ Événement envoyé à Google Analytics');
                    }
                } else {
                    console.error('❌ Erreur tracking:', data.error);
                }
            })
        .catch(err => {
            console.error('❌ Erreur tracking:', err);
            // Retry avec XMLHttpRequest en dernier recours
            retryWithXHR(payload);
        })
        .finally(() => {
            processQueue();
        });
    }
    
    /**
     * Retry avec XMLHttpRequest
     */
    function retryWithXHR(payload) {
        try {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', TRACKING_ENDPOINT, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-CSRF-TOKEN', getCsrfToken());
            xhr.send(JSON.stringify(payload));
            console.log('🔄 Retry avec XMLHttpRequest');
        } catch (e) {
            console.error('❌ Toutes les méthodes ont échoué:', e);
        }
    }
    
    /**
     * Traiter la queue
     */
    function processQueue() {
        window.phoneCallTrackingInProgress = false;
        if (window.phoneCallTrackingQueue.length > 0) {
            const nextPayload = window.phoneCallTrackingQueue.shift();
            setTimeout(() => sendPhoneCallTracking(nextPayload), 100);
        }
    }
    
    /**
     * Obtenir le token CSRF
     */
    function getCsrfToken() {
        return window.Laravel?.csrfToken 
            || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || '';
    }
    
    /**
     * Obtenir le numéro de téléphone par défaut
     */
    function getDefaultPhoneNumber() {
        return window.Laravel?.defaultPhone || '';
    }
    
    /**
     * Attacher le tracking à un lien
     */
    function attachPhoneTracking(link) {
        // Vérifier si le tracking est déjà attaché
        if (link.dataset.trackingAttached === 'true') {
            return;
        }
        
        // Extraire le numéro du href
        const phoneNumber = link.getAttribute('href')?.replace('tel:', '') || '';
        const sourcePage = window.location.pathname;
        
        if (!phoneNumber) {
            return;
        }
        
        // Fonction de tracking avec le numéro et la page
        const trackThisLink = function(e) {
            // Ne pas empêcher le comportement par défaut pour ne pas bloquer l'appel
            // Mais tracker immédiatement
            trackPhoneCall(phoneNumber, sourcePage);
        };
        
        // Pour mobile (touchstart se déclenche AVANT click - le plus important)
        // Utiliser once: true pour ne tracker qu'une fois
        link.addEventListener('touchstart', function(e) {
            // Tracker immédiatement
            trackPhoneCall(phoneNumber, sourcePage);
        }, { 
            passive: true, // Ne pas bloquer le scroll
            capture: true,
            once: false // Permettre plusieurs trackings si nécessaire
        });
        
        // Pour desktop (mousedown se déclenche AVANT click)
        link.addEventListener('mousedown', function(e) {
            trackPhoneCall(phoneNumber, sourcePage);
        }, {
            capture: true,
            passive: true
        });
        
        // Aussi sur le clic en fallback (capture phase - très tôt)
        link.addEventListener('click', function(e) {
            trackPhoneCall(phoneNumber, sourcePage);
        }, {
            capture: true,
            passive: true
        });
        
        // Marquer comme attaché
        link.dataset.trackingAttached = 'true';
    }
    
    /**
     * Attacher le tracking à tous les liens existants
     */
    function attachTrackingToAllLinks() {
        const links = document.querySelectorAll('a[href^="tel:"]');
        if (links.length > 0) {
            console.log('📞 Trouvé ' + links.length + ' lien(s) téléphone à tracker');
            links.forEach(link => {
                attachPhoneTracking(link);
            });
        }
    }
    
    /**
     * Initialiser le système de tracking
     */
    function initTracking() {
        if (window.phoneCallTrackingInitialized) {
            return;
        }
        
        window.phoneCallTrackingInitialized = true;
        
        // Attacher le tracking au chargement de la page
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', attachTrackingToAllLinks);
        } else {
            // Déjà chargé
            attachTrackingToAllLinks();
        }
        
        // Observer les changements du DOM pour capturer les liens ajoutés dynamiquement
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver(function(mutations) {
                let foundNewLinks = false;
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            // Vérifier si c'est un lien tel:
                            if (node.tagName === 'A' && node.getAttribute('href')?.startsWith('tel:')) {
                                attachPhoneTracking(node);
                                foundNewLinks = true;
                            }
                            // Vérifier les enfants
                            if (node.querySelectorAll) {
                                const childLinks = node.querySelectorAll('a[href^="tel:"]');
                                if (childLinks.length > 0) {
                                    childLinks.forEach(link => {
                                        attachPhoneTracking(link);
                                    });
                                    foundNewLinks = true;
                                }
                            }
                        }
                    });
                });
                if (foundNewLinks) {
                    console.log('📞 Nouveaux liens téléphone détectés et trackés');
                }
            });
            
            // Observer les changements dans le body
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
        
        // Attacher aussi après des délais pour capturer les liens chargés après DOMContentLoaded
        RETRY_DELAYS.forEach(delay => {
            setTimeout(attachTrackingToAllLinks, delay);
        });
        
        // Attacher aussi quand la page devient visible (pour les pages chargées en arrière-plan)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                setTimeout(attachTrackingToAllLinks, 100);
            }
        });
    }
    
    // Initialiser immédiatement
    initTracking();
    
    // Réinitialiser si la page est déjà chargée
    if (document.readyState === 'complete') {
        setTimeout(attachTrackingToAllLinks, 100);
    }
})();

