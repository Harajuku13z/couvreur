import { analyzeSearchTerms, generateReportSummary } from "@/lib/ai/openai";
import { fetchCampaignPerformance, addNegativeKeyword } from "@/lib/google-ads/client";
import { canAutoExcludeTerm } from "@/lib/security/ads-safety";

export async function runScheduledScan(options: { dryRun?: boolean; autoNegativeKeywords?: boolean } = {}) {
  const startedAt = new Date();
  const data = await fetchCampaignPerformance();
  const analysis = await analyzeSearchTerms(
    data.searchTerms.map((term) => ({
      term: term.term,
      clicks: term.clicks,
      costMicros: term.costMicros,
      conversions: Number(term.conversions)
    }))
  );

  const actions: Array<Record<string, unknown>> = [];

  for (const term of analysis.terms as Array<{
    term: string;
    relevanceScore?: number;
    decision?: string;
    reason?: string;
    suggestedNegativeKeyword?: string;
  }>) {
    const source = data.searchTerms.find((item) => item.term === term.term);
    const guard = canAutoExcludeTerm({
      term: term.term,
      decision: term.decision,
      relevanceScore: term.relevanceScore,
      costMicros: source?.costMicros,
      autoNegativeKeywords: Boolean(options.autoNegativeKeywords)
    });

    if (guard.allowed && term.suggestedNegativeKeyword) {
      const result = await addNegativeKeyword({
        campaignId: data.campaign.id,
        keyword: term.suggestedNegativeKeyword,
        dryRun: options.dryRun ?? true
      });

      actions.push({
        type: "add_negative_keyword",
        term: term.term,
        keyword: term.suggestedNegativeKeyword,
        status: "applied",
        result,
        reason: guard.reason
      });
    } else if (term.decision === "exclude") {
      actions.push({
        type: "recommend_negative_keyword",
        term: term.term,
        keyword: term.suggestedNegativeKeyword,
        status: "requires_validation",
        reason: guard.reason || term.reason
      });
    }
  }

  const summary = await generateReportSummary({
    campaign: data.campaign,
    searchTerms: data.searchTerms,
    analysis,
    actions
  });

  return {
    type: "scheduled_scan",
    status: "completed",
    startedAt,
    finishedAt: new Date(),
    campaign: data.campaign,
    analysis,
    actions,
    summary
  };
}
