<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Tracking Appels</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .test-section {
            background: #f5f5f5;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .test-button {
            display: inline-block;
            padding: 15px 30px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px;
        }
        .log {
            background: #000;
            color: #0f0;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            max-height: 400px;
            overflow-y: auto;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>🧪 Test du Tracking des Appels Téléphoniques</h1>
    
    <div class="test-section">
        <h2>1. Vérification du script</h2>
        <p id="script-status">Chargement...</p>
        <p id="function-status">Vérification...</p>
    </div>
    
    <div class="test-section">
        <h2>2. Test manuel</h2>
        <p>Cliquez sur le bouton pour tester le tracking :</p>
        <button onclick="testTracking()" class="test-button">Tester le Tracking</button>
        <a href="tel:+33612345678" class="test-button">Lien tel: direct</a>
    </div>
    
    <div class="test-section">
        <h2>3. Logs Console</h2>
        <div id="console-log" class="log"></div>
    </div>
    
    <script>
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            defaultPhone: '{{ setting("company_phone_raw") }}'
        };
        
        // Intercepter console.log pour afficher dans la page
        const originalLog = console.log;
        const originalError = console.error;
        const originalWarn = console.warn;
        const logDiv = document.getElementById('console-log');
        
        function addLog(type, ...args) {
            const message = args.map(arg => typeof arg === 'object' ? JSON.stringify(arg, null, 2) : arg).join(' ');
            const color = type === 'error' ? '#f00' : type === 'warn' ? '#ff0' : '#0f0';
            logDiv.innerHTML += `<div style="color: ${color}">[${new Date().toLocaleTimeString()}] ${message}</div>`;
            logDiv.scrollTop = logDiv.scrollHeight;
        }
        
        console.log = function(...args) {
            originalLog.apply(console, args);
            addLog('log', ...args);
        };
        
        console.error = function(...args) {
            originalError.apply(console, args);
            addLog('error', ...args);
        };
        
        console.warn = function(...args) {
            originalWarn.apply(console, args);
            addLog('warn', ...args);
        };
        
        // Vérifier le chargement du script
        window.addEventListener('load', function() {
            const scriptStatus = document.getElementById('script-status');
            const functionStatus = document.getElementById('function-status');
            
            if (typeof window.trackPhoneCall === 'function') {
                scriptStatus.innerHTML = '✅ Script chargé';
                functionStatus.innerHTML = '✅ Fonction trackPhoneCall disponible';
            } else {
                scriptStatus.innerHTML = '❌ Script non chargé';
                functionStatus.innerHTML = '❌ Fonction trackPhoneCall non disponible';
            }
        });
        
        function testTracking() {
            console.log('🧪 Test manuel du tracking');
            if (typeof window.trackPhoneCall === 'function') {
                window.trackPhoneCall('0612345678', 'test-page');
            } else {
                console.error('❌ trackPhoneCall n\'est pas disponible');
            }
        }
    </script>
    
    <script src="{{ asset('js/phone-tracking.js') }}?v={{ time() }}"></script>
</body>
</html>

