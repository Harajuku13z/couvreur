import { NextResponse } from "next/server";
import { runScheduledScan } from "@/lib/jobs/scheduled-scan";

export async function POST() {
  const result = await runScheduledScan({ dryRun: true, autoNegativeKeywords: false });
  return NextResponse.json(result);
}
