@extends('layouts.admin')

@section('title', 'Accès SEO Automation')

@section('content')
<div class="container mx-auto px-4 py-6 md:py-8">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6 md:p-8">
            <div class="text-center mb-6">
                <i class="fas fa-lock text-4xl text-blue-600 mb-4"></i>
                <h1 class="text-2xl font-bold text-gray-900">Accès protégé</h1>
                <p class="text-gray-600 mt-2">Veuillez saisir le mot de passe pour accéder à la page SEO Automation</p>
            </div>

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.seo-automation.verify-password') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Mot de passe
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required
                           autofocus
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium">
                    <i class="fas fa-unlock mr-2"></i>Accéder
                </button>
            </form>

            <div class="mt-4 text-center text-sm text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                L'accès est valable pendant 1 heure
            </div>
        </div>
    </div>
</div>
@endsection

