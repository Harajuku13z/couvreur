import OpenAI from "openai";
import { defaultNegativeKeywords, demoSearchTerms } from "@/lib/demo/demo-data";
import { campaignGenerationPrompt, searchTermAnalysisPrompt } from "@/lib/ai/prompts";
import { envFlag } from "@/lib/utils";

const client = process.env.OPENAI_API_KEY
  ? new OpenAI({ apiKey: process.env.OPENAI_API_KEY })
  : null;

function extractJson<T>(content: string): T | null {
  try {
    return JSON.parse(content) as T;
  } catch {
    const start = content.indexOf("{");
    const end = content.lastIndexOf("}");
    if (start === -1 || end === -1) return null;
    try {
      return JSON.parse(content.slice(start, end + 1)) as T;
    } catch {
      return null;
    }
  }
}

export type CampaignBrief = {
  businessName: string;
  websiteUrl: string;
  phone: string;
  email?: string;
  address?: string;
  serviceAreas: string[];
  services: string[];
  offer?: string;
  dailyBudget: number;
  schedule: string;
  objective: string;
};

export async function generateCampaignStructure(brief: CampaignBrief) {
  if (!client || envFlag("APP_DEMO_MODE", true)) {
    return demoCampaignStructure(brief);
  }

  const response = await client.chat.completions.create({
    model: "gpt-4o",
    temperature: 0.35,
    response_format: { type: "json_object" },
    messages: [
      { role: "system", content: campaignGenerationPrompt },
      { role: "user", content: JSON.stringify(brief) }
    ]
  });

  const parsed = extractJson<Record<string, unknown>>(response.choices[0]?.message?.content || "");
  return parsed ?? demoCampaignStructure(brief);
}

export async function analyzeSearchTerms(terms: Array<{ term: string; clicks: number; costMicros: number; conversions: number }>) {
  if (!client || envFlag("APP_DEMO_MODE", true)) {
    return {
      terms: terms.map((term) => {
        const demo = demoSearchTerms.find((item) => item.term === term.term);
        return {
          term: term.term,
          relevanceScore: demo?.aiRelevanceScore ?? 50,
          decision: demo?.aiDecision ?? "watch",
          reason: demo?.aiReason ?? "Analyse démo en attente de données réelles.",
          riskLevel: demo?.aiDecision === "exclude" ? "low" : "medium",
          suggestedNegativeKeyword: demo?.aiDecision === "exclude" ? term.term.split(" ")[0] : null
        };
      })
    };
  }

  const response = await client.chat.completions.create({
    model: "gpt-4o",
    temperature: 0.2,
    response_format: { type: "json_object" },
    messages: [
      { role: "system", content: searchTermAnalysisPrompt },
      { role: "user", content: JSON.stringify({ terms }) }
    ]
  });

  return extractJson<{ terms: unknown[] }>(response.choices[0]?.message?.content || "") ?? { terms: [] };
}

export async function generateReportSummary(metrics: Record<string, unknown>) {
  if (!client || envFlag("APP_DEMO_MODE", true)) {
    return "Votre campagne Couvreur Chalon-sur-Saône a dépensé 42,80 € aujourd'hui. Elle a généré 18 clics, 3 appels et 1 formulaire. Le coût par lead estimé est de 10,70 €. L'IA a détecté 5 recherches inutiles proposées en exclusion.";
  }

  const response = await client.chat.completions.create({
    model: "gpt-4o",
    temperature: 0.35,
    messages: [
      { role: "system", content: "Résume en français clair un rapport Google Ads pour artisan couvreur. Sois concis et actionnable." },
      { role: "user", content: JSON.stringify(metrics) }
    ]
  });

  return response.choices[0]?.message?.content || "Rapport généré, mais résumé IA indisponible.";
}

function demoCampaignStructure(brief: CampaignBrief) {
  return {
    campaign: {
      name: `Couvreur ${brief.serviceAreas[0] || "Local"} - Search`,
      objective: brief.objective,
      dailyBudget: brief.dailyBudget,
      validationRequired: true
    },
    adGroups: [
      "Couvreur local",
      "Réparation toiture",
      "Fuite toiture urgence",
      "Nettoyage toiture",
      "Zinguerie",
      "Isolation toiture",
      "Rénovation toiture"
    ].map((name) => ({
      name,
      keywords: [
        `[${name.toLowerCase()}]`,
        `"${name.toLowerCase()} près de moi"`,
        `"devis ${name.toLowerCase()}"`
      ],
      ads: [
        {
          headlines: [`${name} - Devis gratuit`, "Artisan couvreur local", brief.offer || "Intervention rapide"],
          descriptions: ["Contactez un couvreur qualifié pour un diagnostic clair.", "Appels et demandes de devis suivis par l'IA."]
        }
      ]
    })),
    negativeKeywords: defaultNegativeKeywords,
    sitelinks: ["Devis toiture", "Urgence fuite", "Rénovation toiture", "Contact"],
    callouts: ["Devis gratuit", "Entreprise locale", "Intervention rapide", "Suivi clair"],
    callExtension: brief.phone
  };
}
