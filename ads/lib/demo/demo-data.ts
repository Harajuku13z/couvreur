export const demoSearchTerms = [
  { term: "couvreur chalon sur saone", clicks: 9, impressions: 84, costMicros: 10800000, conversions: 2, aiRelevanceScore: 92, aiDecision: "keep", aiReason: "Intention locale commerciale forte." },
  { term: "réparation fuite toiture", clicks: 6, impressions: 52, costMicros: 9200000, conversions: 1, aiRelevanceScore: 90, aiDecision: "keep", aiReason: "Recherche urgente et commerciale." },
  { term: "formation couvreur", clicks: 4, impressions: 45, costMicros: 4800000, conversions: 0, aiRelevanceScore: 5, aiDecision: "exclude", aiReason: "Recherche liée à la formation." },
  { term: "salaire couvreur", clicks: 3, impressions: 38, costMicros: 3100000, conversions: 0, aiRelevanceScore: 4, aiDecision: "exclude", aiReason: "Intention emploi, pas devis." },
  { term: "castorama plaque toiture", clicks: 7, impressions: 73, costMicros: 7600000, conversions: 0, aiRelevanceScore: 8, aiDecision: "exclude", aiReason: "Recherche achat matériel bricolage." },
  { term: "devis toiture gratuit", clicks: 8, impressions: 68, costMicros: 11400000, conversions: 2, aiRelevanceScore: 88, aiDecision: "keep", aiReason: "Intention devis directe." },
  { term: "zingueur autour de moi", clicks: 5, impressions: 40, costMicros: 6900000, conversions: 1, aiRelevanceScore: 86, aiDecision: "keep", aiReason: "Recherche locale à forte valeur." },
  { term: "tuto réparer fuite toiture", clicks: 5, impressions: 61, costMicros: 4200000, conversions: 0, aiRelevanceScore: 9, aiDecision: "exclude", aiReason: "Intention tutoriel non commerciale." }
];

export const demoCampaign = {
  id: "demo-campaign-1",
  name: "Couvreur Chalon-sur-Saône",
  status: "active",
  dailyBudget: 15,
  spentToday: 42.8,
  clicks: 18,
  impressions: 720,
  conversions: 4,
  costPerConversion: 10.7,
  healthScore: 82,
  lastScanAt: "Il y a 2h",
  nextScanAt: "Dans 58 min",
  offer: "-30% sur vos travaux",
  services: ["Couverture", "Isolation", "Rénovation toiture", "Zinguerie"],
  chart: [
    { label: "Lun", cost: 31, leads: 2 },
    { label: "Mar", cost: 44, leads: 4 },
    { label: "Mer", cost: 36, leads: 3 },
    { label: "Jeu", cost: 49, leads: 5 },
    { label: "Ven", cost: 42.8, leads: 4 }
  ]
};

export const demoRecommendations = [
  {
    id: "rec-1",
    type: "exclude_keyword",
    title: "Exclure les recherches formation/salaire",
    description: "5 clics ont été dépensés sur des intentions emploi ou formation.",
    confidence: 96,
    riskLevel: "low",
    status: "pending",
    impact: "Économie estimée: 7,90 € / jour"
  },
  {
    id: "rec-2",
    type: "tracking_alert",
    title: "Vérifier le suivi des appels",
    description: "Les appels remontent, mais aucun formulaire n'a été attribué depuis 24h.",
    confidence: 78,
    riskLevel: "medium",
    status: "pending",
    impact: "Fiabilise le coût par lead"
  },
  {
    id: "rec-3",
    type: "increase_budget",
    title: "Budget limité sur requêtes rentables",
    description: "La campagne perd des impressions sur les termes couvreur et fuite toiture.",
    confidence: 72,
    riskLevel: "high",
    status: "pending",
    impact: "Validation humaine obligatoire"
  }
];

export const defaultNegativeKeywords = [
  "emploi",
  "recrutement",
  "salaire",
  "formation",
  "stage",
  "alternance",
  "cap couvreur",
  "école",
  "tuto",
  "youtube",
  "pdf",
  "gratuit",
  "définition",
  "bricolage",
  "soi-même",
  "forum",
  "castorama",
  "leroy merlin",
  "brico dépôt",
  "bricoman",
  "matériel",
  "outil",
  "occasion",
  "pas cher bricolage",
  "plaque toiture",
  "logiciel",
  "wikipedia"
];
