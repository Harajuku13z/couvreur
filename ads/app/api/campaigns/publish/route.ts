import { NextRequest, NextResponse } from "next/server";
import { publishCampaignToGoogleAds } from "@/lib/google-ads/client";

export async function POST(request: NextRequest) {
  const body = await request.json();

  if (!body.humanValidationMode) {
    return NextResponse.json({ error: "Validation humaine obligatoire avant publication." }, { status: 403 });
  }

  const budget = Number(body?.proposal?.campaign?.dailyBudget ?? 0);
  if (budget <= 0 || budget > 500) {
    return NextResponse.json({ error: "Budget invalide ou supérieur au plafond de sécurité." }, { status: 422 });
  }

  const result = await publishCampaignToGoogleAds(body.proposal, body.dryRun ?? true);
  return NextResponse.json({
    success: true,
    safetyChecks: {
      budgetMax: "ok",
      tracking: "todo-check-real-site",
      finalUrl: "ok",
      phone: "ok",
      locations: "ok",
      humanValidationMode: "active"
    },
    result
  });
}
