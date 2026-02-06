@php
    /** @var string $adminUsername */
    /** @var string $adminEmail */
    /** @var string $passwordDisplay */
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Réinitialisation mot de passe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .card {
            background: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
            padding: 2rem 2.5rem;
            width: 100%;
            max-width: 480px;
        }
        h1 {
            margin: 0 0 0.5rem;
            font-size: 1.5rem;
            color: #0f172a;
        }
        p.subtitle {
            margin: 0 0 1.5rem;
            font-size: 0.95rem;
            color: #64748b;
        }
        .current-info {
            background: #f8fafc;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #0f172a;
        }
        .current-info strong {
            display: inline-block;
            min-width: 130px;
            color: #475569;
        }
        .alert {
            font-size: 0.8rem;
            color: #b91c1c;
            background: #fef2f2;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
        }
        .success {
            font-size: 0.85rem;
            color: #166534;
            background: #dcfce7;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        label {
            font-size: 0.9rem;
            color: #0f172a;
            margin-bottom: 0.25rem;
            display: block;
        }
        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #cbd5f5;
            padding: 0.6rem 0.75rem;
            font-size: 0.9rem;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.25);
        }
        .hint {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        button[type="submit"] {
            margin-top: 0.75rem;
            background: #2563eb;
            color: #ffffff;
            border-radius: 999px;
            border: none;
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease, transform 0.05s ease;
        }
        button[type="submit"]:hover {
            background: #1d4ed8;
        }
        button[type="submit"]:active {
            transform: translateY(1px);
        }
        .footer-note {
            margin-top: 1.25rem;
            font-size: 0.75rem;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Gestion admin</h1>
        <p class="subtitle">
            Page privée pour réinitialiser les identifiants d'accès admin.
        </p>

        @if (session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert">
                <strong>Erreur :</strong><br>
                @foreach ($errors->all() as $error)
                    - {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <div class="current-info">
            <div><strong>Email actuel :</strong> {{ $adminEmail }}</div>
            <div><strong>Username admin :</strong> {{ $adminUsername }}</div>
            <div><strong>Mot de passe actuel :</strong> {{ $passwordDisplay }}</div>
        </div>

        <form method="POST" action="{{ route('useradmin.update') }}">
            @csrf

            <div>
                <label for="email">Nouvel email admin</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $adminEmail) }}"
                    required
                >
                <div class="hint">
                    Cet email servira comme identifiant pour la connexion admin.
                </div>
            </div>

            <div>
                <label for="password">Nouveau mot de passe admin</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >
                <div class="hint">
                    Minimum 6 caractères. Le mot de passe sera stocké de façon sécurisée (hashé).
                </div>
            </div>

            <button type="submit">
                Mettre à jour les identifiants
            </button>
        </form>

        <div class="footer-note">
            Accès à cette page via authentification HTTP Basic (elizo / elizo).
        </div>
    </div>
</body>
</html>

