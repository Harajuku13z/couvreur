import { NextResponse } from "next/server";
import { demoCampaign } from "@/lib/demo/demo-data";

export async function GET() {
  return NextResponse.json({ campaigns: [demoCampaign] });
}
