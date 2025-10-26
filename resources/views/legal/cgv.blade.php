@extends('layouts.app')

@section('title', 'Conditions Générales de Vente - ' . $companyName)

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Conditions Générales de Vente</h1>
            
            <div class="prose prose-lg max-w-none">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">1. Objet</h2>
                <p class="mb-6">
                    Les présentes conditions générales de vente s'appliquent à tous les services de couverture, rénovation et travaux de toiture proposés par {{ $companyName }}.
                </p>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">2. Identification du vendeur</h2>
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <p><strong>{{ $companyName }}</strong></p>
                    @if($companyAddress)
                    <p>{{ $companyAddress }}</p>
                    @endif
                    @if($companySiret)
                    <p>SIRET : {{ $companySiret }}</p>
                    @endif
                    @if($companyPhone)
                    <p>Téléphone : {{ $companyPhone }}</p>
                    @endif
                    @if($companyEmail)
                    <p>Email : {{ $companyEmail }}</p>
                    @endif
                </div>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">3. Services proposés</h2>
                <p class="mb-4">{{ $companyName }} propose les services suivants :</p>
                <ul class="list-disc pl-6 mb-6">
                    @if(count($activeServices) > 0)
                        @foreach($activeServices as $service)
                            @if(is_array($service) && isset($service['name']))
                            <li>{{ $service['name'] }}</li>
                            @endif
                        @endforeach
                    @else
                        <li>Rénovation de toiture</li>
                        <li>Pose de couverture</li>
                        <li>Réparation de toiture</li>
                        <li>Isolation de toiture</li>
                        <li>Demoussage et hydrofuge</li>
                        <li>Zinguerie</li>
                        <li>Charpente</li>
                    @endif
                </ul>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">4. Devis et commande</h2>
                <p class="mb-4">Toute prestation fait l'objet d'un devis détaillé et gratuit comprenant :</p>
                <ul class="list-disc pl-6 mb-6">
                    <li>Description précise des travaux</li>
                    <li>Matériaux utilisés</li>
                    <li>Délais d'exécution</li>
                    <li>Prix détaillé</li>
                    <li>Conditions de paiement</li>
                    <li>Garanties</li>
                </ul>
                <p class="mb-6">
                    Le devis est valable 30 jours. L'acceptation du devis par le client vaut commande ferme et définitive.
                </p>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">5. Prix et modalités de paiement</h2>
                <p class="mb-4">Les prix sont indiqués en euros TTC.</p>
                
                @if($paymentTerms)
                    <div class="mb-6">
                        {!! $paymentTerms !!}
                    </div>
                @else
                    <p class="mb-4">Les modalités de paiement sont les suivantes :</p>
                    <ul class="list-disc pl-6 mb-6">
                        <li><strong>Acompte :</strong> 30% à la commande</li>
                        <li><strong>Solde :</strong> 70% à la livraison des travaux</li>
                        <li><strong>Moyens de paiement :</strong> chèque, virement bancaire, espèces (dans la limite légale)</li>
                    </ul>
                @endif
                
                @if($latePaymentPenalties)
                    <div class="mb-6">
                        {!! $latePaymentPenalties !!}
                    </div>
                @endif
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">6. Délais d'exécution</h2>
                <p class="mb-6">
                    Les délais d'exécution sont indiqués sur le devis. Ils peuvent être prolongés en cas de force majeure ou de circonstances indépendantes de notre volonté (intempéries, grève, etc.).
                </p>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">7. Garanties</h2>
                <p class="mb-4">Nous garantissons :</p>
                <ul class="list-disc pl-6 mb-6">
                    <li><strong>Garantie décennale :</strong> 10 ans pour les travaux de gros œuvre</li>
                    <li><strong>Garantie biennale :</strong> 2 ans pour les équipements</li>
                    <li><strong>Garantie commerciale :</strong> selon les conditions des fabricants</li>
                </ul>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">8. Assurance</h2>
                <p class="mb-6">
                    {{ $companyName }} est couverte par une assurance responsabilité civile professionnelle et une assurance décennale conformément à la réglementation en vigueur.
                </p>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">9. Droit de rétractation</h2>
                <p class="mb-6">
                    Conformément à l'article L. 221-28 du Code de la consommation, le droit de rétractation ne s'applique pas aux contrats de fourniture de services pleinement exécutés avant la fin du délai de rétractation et dont l'exécution a commencé après accord préalable exprès du consommateur et renoncement exprès à son droit de rétractation.
                </p>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">10. Réclamations</h2>
                <p class="mb-6">
                    Toute réclamation doit être adressée par écrit à {{ $companyName }} dans un délai de 8 jours après la réception des travaux. Nous nous engageons à répondre dans les meilleurs délais.
                </p>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">11. Droit applicable et juridiction</h2>
                <p class="mb-6">
                    Les présentes conditions générales de vente sont soumises au droit français. En cas de litige, les tribunaux français seront seuls compétents.
                </p>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">12. Contact</h2>
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <p>Pour toute question concernant ces conditions générales de vente :</p>
                    <p><strong>{{ $companyName }}</strong></p>
                    @if($companyAddress)
                    <p>{{ $companyAddress }}</p>
                    @endif
                    @if($companyPhone)
                    <p>Téléphone : {{ $companyPhone }}</p>
                    @endif
                    @if($companyEmail)
                    <p>Email : {{ $companyEmail }}</p>
                    @endif
                </div>
                
                <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-gray-600">
                        <strong>Dernière mise à jour :</strong> {{ date('d/m/Y') }}
                    </p>
                </div>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 mt-8">13. Crédits</h2>
                <p class="mb-6">
                    Ce site web a été créé par <a href="https://www.osmoseconsulting.fr" target="_blank" class="text-blue-600 hover:text-blue-800 transition-colors font-medium">Osmose*</a> avec amour ❤️
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

