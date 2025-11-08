<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Liste des clients
     */
    public function index(Request $request)
    {
        try {
            $query = Client::orderBy('nom');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('telephone', 'like', "%{$search}%")
                      ->orWhere('adresse', 'like', "%{$search}%")
                      ->orWhere('code_postal', 'like', "%{$search}%")
                      ->orWhere('ville', 'like', "%{$search}%");
                });
            }

            $clients = $query->paginate(20);

            return view('admin.clients.index', compact('clients'));
        } catch (\Exception $e) {
            \Log::error('Erreur ClientController::index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('admin.clients.index', ['clients' => collect([])->paginate(20)])
                ->with('error', 'Erreur lors du chargement des clients. Vérifiez que les migrations ont été exécutées : ' . $e->getMessage());
        }
    }

    /**
     * Créer un client
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $client = Client::create($request->all());

        return response()->json([
            'success' => true,
            'client' => $client,
        ]);
    }

    /**
     * Rechercher des clients (pour autocomplete)
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $clients = Client::where('nom', 'like', "%{$query}%")
            ->orWhere('prenom', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json($clients);
    }
}

