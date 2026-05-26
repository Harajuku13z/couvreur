import { GoogleAdsApi } from "google-ads-api";
import { envFlag } from "@/lib/utils";
import { demoCampaign, demoSearchTerms } from "@/lib/demo/demo-data";

export function isGoogleAdsConfigured() {
  return Boolean(
    process.env.GOOGLE_ADS_DEVELOPER_TOKEN &&
      process.env.GOOGLE_CLIENT_ID &&
      process.env.GOOGLE_CLIENT_SECRET &&
      !envFlag("APP_DEMO_MODE", true)
  );
}

export function getGoogleAdsClient() {
  if (!isGoogleAdsConfigured()) return null;

  return new GoogleAdsApi({
    client_id: process.env.GOOGLE_CLIENT_ID!,
    client_secret: process.env.GOOGLE_CLIENT_SECRET!,
    developer_token: process.env.GOOGLE_ADS_DEVELOPER_TOKEN!
  });
}

export async function listAccessibleCustomers(refreshToken?: string) {
  if (!isGoogleAdsConfigured() || !refreshToken) {
    return [
      {
        customerId: "1234567890",
        descriptiveName: "Compte démo Couvreur",
        currencyCode: "EUR",
        timeZone: "Europe/Paris"
      }
    ];
  }

  // TODO: brancher customer.listAccessibleCustomers lorsque les identifiants réels sont fournis.
  return [];
}

export async function publishCampaignToGoogleAds(payload: Record<string, unknown>, dryRun = true) {
  if (dryRun || !isGoogleAdsConfigured()) {
    return {
      dryRun: true,
      googleCampaignId: `demo-${Date.now()}`,
      message: "Mode dry-run: aucune campagne réelle n'a été créée."
    };
  }

  // TODO: créer budget, campaign, ad groups, keywords, ads et negative keywords via Google Ads API.
  return {
    dryRun: false,
    googleCampaignId: `real-${Date.now()}`,
    payload
  };
}

export async function fetchCampaignPerformance() {
  if (!isGoogleAdsConfigured()) {
    return {
      campaign: demoCampaign,
      searchTerms: demoSearchTerms
    };
  }

  // TODO: requêtes GAQL pour metrics.cost_micros, clicks, conversions et search_term_view.
  return {
    campaign: demoCampaign,
    searchTerms: []
  };
}

export async function addNegativeKeyword(params: { campaignId: string; keyword: string; dryRun?: boolean }) {
  if (params.dryRun || !isGoogleAdsConfigured()) {
    return {
      dryRun: true,
      resourceName: `demo-negative/${params.keyword}`,
      message: "Mot-clé négatif simulé en dry-run."
    };
  }

  // TODO: appeler campaignCriteria.mutate pour ajouter le negative keyword.
  return {
    dryRun: false,
    resourceName: `customers/live/campaignCriteria/${params.campaignId}~${params.keyword}`
  };
}
