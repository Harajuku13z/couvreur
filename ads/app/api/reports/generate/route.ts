import { NextResponse } from "next/server";
import { generateReportSummary } from "@/lib/ai/openai";
import { demoCampaign, demoSearchTerms } from "@/lib/demo/demo-data";

export async function POST() {
  const summary = await generateReportSummary({ campaign: demoCampaign, searchTerms: demoSearchTerms });
  return NextResponse.json({
    success: true,
    report: {
      type: "manual",
      summary,
      metrics: {
        spent: demoCampaign.spentToday,
        clicks: demoCampaign.clicks,
        conversions: demoCampaign.conversions,
        costPerLead: demoCampaign.costPerConversion
      }
    }
  });
}
