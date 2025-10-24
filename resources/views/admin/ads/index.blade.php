@extends('layouts.admin')

@section('title', 'Annonces')

@section('content')
<div class="max-w-6xl mx-auto py-10">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Toutes les annonces</h1>
        <div class="space-x-3">
            <a href="{{ route('admin.ads.service-cities') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-robot mr-2"></i>Service + Villes
            </a>
            <a href="{{ route('admin.ads.keyword-cities') }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                <i class="fas fa-key mr-2"></i>Mot-clé + Villes
            </a>
            <a href="{{ route('admin.ads.manual') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-edit mr-2"></i>Créer manuellement
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <!-- Modal IA -->
    <div id="aiModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold">Créer des landing pages via IA</h3>
                        <button onclick="closeAIModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="aiForm" class="space-y-4" onsubmit="return submitAI(event)">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Service</label>
                                <select name="service_slug" class="w-full border rounded p-2" required>
                                    <option value="">-- Choisir un service --</option>
                                    @foreach(($services ?? []) as $s)
                                        <option value="{{ $s['slug'] }}">{{ $s['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Taille du lot</label>
                                <input type="number" name="batch_size" min="1" max="50" value="20" class="w-full border rounded p-2" />
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Région (optionnel)</label>
                                <select name="region" class="w-full border rounded p-2">
                                    <option value="">-- Aucune --</option>
                                    @foreach(($regions ?? []) as $r)
                                        <option value="{{ $r }}">{{ $r }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Département (optionnel)</label>
                                <select name="department" class="w-full border rounded p-2">
                                    <option value="">-- Aucun --</option>
                                    @foreach(($departments ?? []) as $d)
                                        <option value="{{ $d }}">{{ $d }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Villes spécifiques (optionnel)</label>
                            <select multiple name="city_ids[]" class="w-full border rounded p-2 h-32">
                                @foreach(($cities ?? []) as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->postal_code }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Si aucune ville n'est sélectionnée, la région/département seront utilisés.</p>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Prompt IA (optionnel)</label>
                            <textarea name="ai_prompt" rows="3" class="w-full border rounded p-2" placeholder="Tu es un expert en marketing digital et SEO local. Génère une description optimisée pour une annonce de service local. Inclus des mots-clés locaux, des avantages clients, et un appel à l'action. Le contenu doit être unique et engageant."></textarea>
                            <p class="text-xs text-gray-500 mt-1">Laissez vide pour utiliser le prompt par défaut</p>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeAIModal()" class="px-4 py-2 border rounded">Annuler</button>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Générer</button>
                        </div>
                        <div id="aiStatus" class="text-sm text-gray-600"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Mot-clé + Villes -->
    <div id="keywordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold">Créer des landing pages par mot-clé</h3>
                        <button onclick="closeKeywordModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="keywordForm" class="space-y-4" onsubmit="return submitKeyword(event)">
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Mot-clé</label>
                            <input type="text" name="keyword" class="w-full border rounded p-2" placeholder="ex: Rénovation Toiture" required />
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Région (optionnel)</label>
                                <select name="region" class="w-full border rounded p-2">
                                    <option value="">-- Aucune --</option>
                                    @foreach(($regions ?? []) as $r)
                                        <option value="{{ $r }}">{{ $r }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Département (optionnel)</label>
                                <select name="department" class="w-full border rounded p-2">
                                    <option value="">-- Aucun --</option>
                                    @foreach(($departments ?? []) as $d)
                                        <option value="{{ $d }}">{{ $d }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Villes spécifiques (optionnel)</label>
                            <select multiple name="city_ids[]" class="w-full border rounded p-2 h-32">
                                @foreach(($cities ?? []) as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->postal_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Prompt IA (optionnel)</label>
                            <textarea name="ai_prompt" rows="3" class="w-full border rounded p-2" placeholder="Tu es un expert en marketing digital et SEO local. Génère une description optimisée pour une annonce de service local. Inclus des mots-clés locaux, des avantages clients, et un appel à l'action. Le contenu doit être unique et engageant."></textarea>
                            <p class="text-xs text-gray-500 mt-1">Laissez vide pour utiliser le prompt par défaut</p>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Taille du lot</label>
                                <input type="number" name="batch_size" min="1" max="50" value="20" class="w-full border rounded p-2" />
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded w-full">Générer</button>
                            </div>
                        </div>
                        <div id="keywordStatus" class="text-sm text-gray-600"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Manuel -->
    <div id="manualModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold">Créer une annonce manuellement</h3>
                        <button onclick="closeManualModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="manualForm" class="space-y-4" onsubmit="return submitManual(event)">
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Titre</label>
                            <input type="text" name="title" class="w-full border rounded p-2" required />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Mot-clé</label>
                            <input type="text" name="keyword" class="w-full border rounded p-2" required />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Ville</label>
                            <select name="city_id" class="w-full border rounded p-2" required>
                                <option value="">-- Choisir une ville --</option>
                                @foreach(($cities ?? []) as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->postal_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Meta titre</label>
                            <input type="text" name="meta_title" class="w-full border rounded p-2" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Meta description</label>
                            <textarea name="meta_description" class="w-full border rounded p-2" rows="3"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Contenu HTML</label>
                            <textarea name="content_html" class="w-full border rounded p-2" rows="6"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Prompt IA (optionnel)</label>
                            <textarea name="ai_prompt" rows="3" class="w-full border rounded p-2" placeholder="Décrivez comment l'IA doit générer le contenu de cette annonce..."></textarea>
                            <p class="text-xs text-gray-500 mt-1">Laissez vide pour utiliser le prompt par défaut</p>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeManualModal()" class="px-4 py-2 border rounded">Annuler</button>
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Créer</button>
                        </div>
                        <div id="manualStatus" class="text-sm text-gray-600"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr class="text-left text-sm text-gray-600">
                    <th class="p-3">Titre</th>
                    <th class="p-3">Ville</th>
                    <th class="p-3">Statut</th>
                    <th class="p-3">Slug</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ads as $ad)
                <tr class="border-t">
                    <td class="p-3">{{ $ad->title }}</td>
                    <td class="p-3">{{ optional($ad->city)->name }}</td>
                    <td class="p-3">
                        <span class="text-xs px-2 py-1 rounded {{ match($ad->status){ 'published' => 'bg-green-100 text-green-700', 'draft' => 'bg-yellow-100 text-yellow-700', 'archived' => 'bg-gray-100 text-gray-600' } }}">
                            {{ ucfirst($ad->status) }}
                        </span>
                    </td>
                    <td class="p-3"><code>{{ $ad->slug }}</code></td>
                    <td class="p-3 space-x-2">
                        @if($ad->status !== 'published')
                        <form method="POST" action="{{ route('admin.ads.publish', $ad) }}" class="inline">
                            @csrf
                            <button class="text-blue-600">Publier</button>
                        </form>
                        @endif
                        @if($ad->status !== 'archived')
                        <form method="POST" action="{{ route('admin.ads.archive', $ad) }}" class="inline">
                            @csrf
                            <button class="text-gray-600">Archiver</button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('admin.ads.destroy', $ad) }}" class="inline" onsubmit="return confirm('Supprimer ?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600">Supprimer</button>
                        </form>
                        @if($ad->status === 'published')
                        <a class="text-green-700" target="_blank" href="{{ route('ads.show', $ad->slug) }}">Voir</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td class="p-3 text-gray-500" colspan="5">Aucune annonce.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $ads->links() }}</div>
</div>

<script>
function openAIModal() {
    document.getElementById('aiModal').classList.remove('hidden');
}

function closeAIModal() {
    document.getElementById('aiModal').classList.add('hidden');
}

function openKeywordModal() {
    document.getElementById('keywordModal').classList.remove('hidden');
}

function closeKeywordModal() {
    document.getElementById('keywordModal').classList.add('hidden');
}



function openManualModal() {
    document.getElementById('manualModal').classList.remove('hidden');
}

function closeManualModal() {
    document.getElementById('manualModal').classList.add('hidden');
}

async function submitAI(e) {
    e.preventDefault();
    const form = document.getElementById('aiForm');
    const fd = new FormData(form);
    
    // Validation côté client
    if (!fd.get('service_slug')) {
        document.getElementById('aiStatus').textContent = 'Veuillez sélectionner un service';
        return false;
    }
    
    const payload = {
        service_slug: fd.get('service_slug'),
        region: fd.get('region') || null,
        department: fd.get('department') || null,
        batch_size: parseInt(fd.get('batch_size')||'20'),
        city_ids: [...(fd.getAll('city_ids[]')||[])].map(Number).filter(id => id > 0)
    };
    
    // Nettoyer les valeurs null/undefined
    Object.keys(payload).forEach(key => {
        if (payload[key] === null || payload[key] === undefined || payload[key] === '') {
            delete payload[key];
        }
    });
    
    document.getElementById('aiStatus').textContent = 'Génération en cours...';
    document.getElementById('aiStatus').className = 'text-sm text-blue-600';
    
    // Désactiver le bouton pour éviter les double-clics
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Génération...';
    
    try {
        const endpoint = '{{ route('admin.ads.generate.service-cities') }}';
        console.log('POST', endpoint, payload);
        
        // Timeout de 30 secondes
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000);
        
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload),
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        let data;
        const text = await res.text();
        try { data = JSON.parse(text); } catch(_) { data = null; }
        
        if(res.ok && data && data.success){
            document.getElementById('aiStatus').textContent = `✅ Créées: ${data.created}, ignorées: ${data.skipped}`;
            document.getElementById('aiStatus').className = 'text-sm text-green-600';
            setTimeout(()=>{ 
                closeAIModal();
                window.location.reload(); 
            }, 2000);
        } else {
            const msg = (data && (data.message || (data.errors && JSON.stringify(data.errors)))) || `HTTP ${res.status}`;
            document.getElementById('aiStatus').textContent = `❌ ${msg}`;
            document.getElementById('aiStatus').className = 'text-sm text-red-600';
        }
    } catch (error) {
        console.error('Erreur:', error);
        if (error.name === 'AbortError') {
            document.getElementById('aiStatus').textContent = '❌ Timeout: La génération prend trop de temps (>30s)';
        } else {
            document.getElementById('aiStatus').textContent = '❌ Erreur de connexion: ' + error.message;
        }
        document.getElementById('aiStatus').className = 'text-sm text-red-600';
    } finally {
        // Réactiver le bouton
        submitBtn.disabled = false;
        submitBtn.textContent = 'Générer';
    }
    
    return false;
}

async function submitManual(e) {
    e.preventDefault();
    const form = document.getElementById('manualForm');
    const fd = new FormData(form);
    const payload = {
        title: fd.get('title'),
        keyword: fd.get('keyword'),
        city_id: parseInt(fd.get('city_id')),
        meta_title: fd.get('meta_title'),
        meta_description: fd.get('meta_description'),
        content_html: fd.get('content_html')
    };
    
    document.getElementById('manualStatus').textContent = 'Création en cours...';
    
    try {
        const res = await fetch('{{ route('admin.ads.create.manual') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        
        if(data.success){
            document.getElementById('manualStatus').textContent = 'Annonce créée avec succès';
            setTimeout(()=>{ 
                closeManualModal();
                window.location.reload(); 
            }, 1000);
        } else {
            document.getElementById('manualStatus').textContent = data.message || 'Erreur de création';
        }
    } catch (error) {
        document.getElementById('manualStatus').textContent = 'Erreur de connexion';
    }
}

async function submitKeyword(e) {
    e.preventDefault();
    const form = document.getElementById('keywordForm');
    const fd = new FormData(form);
    
    if (!fd.get('keyword')) {
        document.getElementById('keywordStatus').textContent = 'Veuillez saisir un mot-clé';
        return false;
    }
    
    const payload = {
        keyword: fd.get('keyword'),
        region: fd.get('region') || null,
        department: fd.get('department') || null,
        batch_size: parseInt(fd.get('batch_size')||'20'),
        city_ids: [...(fd.getAll('city_ids[]')||[])].map(Number).filter(id => id > 0)
    };
    
    Object.keys(payload).forEach(key => {
        if (payload[key] === null || payload[key] === undefined || payload[key] === '') {
            delete payload[key];
        }
    });
    
    document.getElementById('keywordStatus').textContent = 'Génération en cours...';
    document.getElementById('keywordStatus').className = 'text-sm text-blue-600';
    
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Génération...';
    
    try {
        const res = await fetch('{{ route('admin.ads.generate.keyword-cities') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const data = await res.json();
        
        if(res.ok && data && data.success){
            document.getElementById('keywordStatus').textContent = `✅ Créées: ${data.created}, ignorées: ${data.skipped}`;
            document.getElementById('keywordStatus').className = 'text-sm text-green-600';
            setTimeout(()=>{ 
                closeKeywordModal();
                window.location.reload(); 
            }, 2000);
        } else {
            document.getElementById('keywordStatus').textContent = `❌ ${data.message || 'Erreur de génération'}`;
            document.getElementById('keywordStatus').className = 'text-sm text-red-600';
        }
    } catch (error) {
        document.getElementById('keywordStatus').textContent = '❌ Erreur de connexion: ' + error.message;
        document.getElementById('keywordStatus').className = 'text-sm text-red-600';
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Générer';
    }
    
    return false;
}


</script>


@endsection



