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
    window.phoneCallTrackingSent = window.phoneCallTrackingSent || new Set(); // Pour éviter les doublons
    
    /**
     * Fonction principale de tracking avec déduplication
     */
    window.trackPhoneCall = function(phoneNumber = null, sourcePage = null) {
        const phone = phoneNumber || getDefaultPhoneNumber();
        const page = sourcePage || window.location.pathname;
        
        if (!phone) {
            console.error('❌ Pas de numéro de téléphone disponible');
            return;
        }
        
        // Créer une clé unique pour ce tracking (phone + page + timestamp arrondi à la seconde)
        const trackingKey = `${phone}_${page}_${Math.floor(Date.now() / 1000)}`;
        
        // Vérifier si on a déjà envoyé ce tracking dans les 2 dernières secondes
        if (window.phoneCallTrackingSent.has(trackingKey)) {
            console.log('⚠️ Tracking déjà envoyé, ignoré (déduplication)');
            return;
        }
        
        // Marquer comme envoyé
        window.phoneCallTrackingSent.add(trackingKey);
        
        // Nettoyer les anciennes clés (garder seulement les 2 dernières secondes)
        setTimeout(() => {
            window.phoneCallTrackingSent.delete(trackingKey);
        }, 2000);
        
        const payload = {
            source_page: page,
            phone_number: phone,
            referrer_url: document.referrer || window.location.href
        };
        
        console.log('📞 trackPhoneCall appelé', { phoneNumber, sourcePage, trackingKey });
        
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
        
        // Méthode 1: sendBeacon avec FormData (le plus fiable pour les liens tel:)
        if (navigator.sendBeacon) {
            try {
                const formData = new FormData();
                formData.append('phone_number', payload.phone_number);
                formData.append('source_page', payload.source_page);
                formData.append('referrer_url', payload.referrer_url);
                console.log('📤 Tentative sendBeacon vers:', TRACKING_ENDPOINT);
                const sent = navigator.sendBeacon(TRACKING_ENDPOINT, formData);
                if (sent) {
                    console.log('✅ Tracking envoyé via sendBeacon (FormData)');
                    processQueue();
                    return;
                } else {
                    console.warn('⚠️ sendBeacon retourné false');
                }
            } catch (e) {
                console.error('❌ sendBeacon FormData failed:', e);
            }
        } else {
            console.warn('⚠️ navigator.sendBeacon non disponible');
        }
        
        // Méthode 1b: sendBeacon avec Blob (fallback)
        if (navigator.sendBeacon) {
            try {
                const blob = new Blob([data], { type: 'application/json' });
                const sent = navigator.sendBeacon(TRACKING_ENDPOINT, blob);
                if (sent) {
                    console.log('✅ Tracking envoyé via sendBeacon (Blob)');
                    processQueue();
                    return;
                }
            } catch (e) {
                console.warn('sendBeacon Blob failed, trying fetch:', e);
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
            console.log('⚠️ Tracking déjà attaché à ce lien, ignoré');
            return;
        }
        
        // Pour le bouton fixe, vérifier aussi par ID
        if (link.id === 'floatingCallBtn' && link.dataset.trackingAttached === 'true') {
            console.log('⚠️ Bouton fixe déjà tracké, ignoré');
            return;
        }
        
        // Extraire le numéro du href
        const phoneNumber = link.getAttribute('href')?.replace('tel:', '') || '';
        const sourcePage = window.location.pathname;
        
        if (!phoneNumber) {
            return;
        }
        
        // Utiliser UN SEUL événement pour éviter les doublons
        // Sur mobile, touchstart est le plus fiable
        // Sur desktop, mousedown est le plus fiable
        // On utilise une seule fonction avec un flag pour éviter les doubles appels
        
        let linkTrackingLock = false;
        
        const trackThisLink = function(e) {
            // Si déjà en cours de tracking, ignorer
            if (linkTrackingLock) {
                e.stopPropagation(); // Empêcher la propagation pour éviter les autres handlers
                return;
            }
            
            // Verrouiller immédiatement
            linkTrackingLock = true;
            
            // Tracker
            trackPhoneCall(phoneNumber, sourcePage);
            
            // Déverrouiller après 2 secondes
            setTimeout(() => {
                linkTrackingLock = false;
            }, 2000);
        };
        
        // Détecter si on est sur mobile ou desktop
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        
        if (isMobile) {
            // Mobile : utiliser uniquement touchstart (le plus fiable)
            link.addEventListener('touchstart', trackThisLink, { 
                passive: true,
                capture: true,
                once: false
            });
        } else {
            // Desktop : utiliser uniquement mousedown (le plus fiable)
            link.addEventListener('mousedown', trackThisLink, {
                capture: true,
                passive: true,
                once: false
            });
        }
        
        // Fallback : click uniquement si les autres n'ont pas fonctionné (avec vérification)
        link.addEventListener('click', function(e) {
            // Si le lock est toujours actif, c'est que touchstart/mousedown n'a pas fonctionné
            if (!linkTrackingLock) {
                trackThisLink(e);
            }
        }, {
            capture: true,
            passive: true,
            once: false
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
        // Réduire le nombre de tentatives pour éviter les doublons
        setTimeout(attachTrackingToAllLinks, 100);
        setTimeout(attachTrackingToAllLinks, 500);
        
        // Attacher aussi quand la page devient visible (pour les pages chargées en arrière-plan)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                setTimeout(attachTrackingToAllLinks, 100);
            }
        });
    }
    
    // Initialiser une seule fois
    if (!window.phoneCallTrackingInitialized) {
        initTracking();
    }
})();

