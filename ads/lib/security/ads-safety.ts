export const protectedCommercialTerms = [
  "couvreur",
  "toiture",
  "fuite",
  "réparation",
  "reparation",
  "zingueur",
  "zinguerie",
  "isolation",
  "rénovation",
  "renovation"
];

export const obviousWasteTerms = [
  "emploi",
  "recrutement",
  "formation",
  "stage",
  "salaire",
  "tuto",
  "youtube",
  "pdf",
  "castorama",
  "leroy merlin",
  "brico dépôt",
  "brico depot",
  "forum",
  "wikipedia"
];

export type RiskyAction =
  | "increase_budget"
  | "create_campaign"
  | "bulk_keyword_change"
  | "delete_campaign"
  | "pause_ad_group"
  | "add_negative_keyword";

export function requiresHumanValidation(action: RiskyAction) {
  return action !== "add_negative_keyword";
}

export function canAutoExcludeTerm(params: {
  term: string;
  decision?: string | null;
  relevanceScore?: number | null;
  costMicros?: number | bigint | null;
  autoNegativeKeywords: boolean;
  safetyCostThresholdMicros?: number;
}) {
  const term = params.term.toLowerCase();
  const containsProtected = protectedCommercialTerms.some((keyword) => term.includes(keyword));
  const containsWaste = obviousWasteTerms.some((keyword) => term.includes(keyword));
  const cost = Number(params.costMicros || 0);
  const threshold = params.safetyCostThresholdMicros ?? 8_000_000;

  if (!params.autoNegativeKeywords) return { allowed: false, reason: "Auto-exclusion désactivée." };
  if (params.decision !== "exclude") return { allowed: false, reason: "La décision IA n'est pas une exclusion." };
  if ((params.relevanceScore ?? 100) > 15) return { allowed: false, reason: "Score de pertinence trop élevé." };
  if (cost > threshold) return { allowed: false, reason: "Coût supérieur au seuil de sécurité." };
  if (containsProtected && !containsWaste) {
    return { allowed: false, reason: "Terme commercial protégé: validation humaine obligatoire." };
  }

  return { allowed: true, reason: "Terme très peu pertinent et sous seuil de sécurité." };
}
