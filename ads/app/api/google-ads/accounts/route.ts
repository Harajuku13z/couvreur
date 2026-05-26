import { NextResponse } from "next/server";
import { listAccessibleCustomers } from "@/lib/google-ads/client";

export async function GET() {
  const accounts = await listAccessibleCustomers();
  return NextResponse.json({ accounts });
}
