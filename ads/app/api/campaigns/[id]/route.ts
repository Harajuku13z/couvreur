import { NextResponse } from "next/server";
import { demoCampaign, demoSearchTerms } from "@/lib/demo/demo-data";

export async function GET() {
  return NextResponse.json({ campaign: demoCampaign, searchTerms: demoSearchTerms });
}
