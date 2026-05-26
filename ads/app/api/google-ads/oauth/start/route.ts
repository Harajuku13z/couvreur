import { redirect } from "next/navigation";
import { buildGoogleAdsOAuthUrl } from "@/lib/google-ads/oauth";

export async function GET() {
  redirect(buildGoogleAdsOAuthUrl(crypto.randomUUID()));
}

export async function POST() {
  redirect(buildGoogleAdsOAuthUrl(crypto.randomUUID()));
}
