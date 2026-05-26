import { envFlag } from "@/lib/utils";

const scopes = ["https://www.googleapis.com/auth/adwords"];

export function buildGoogleAdsOAuthUrl(state: string) {
  const params = new URLSearchParams({
    client_id: process.env.GOOGLE_CLIENT_ID || "",
    redirect_uri: `${process.env.NEXTAUTH_URL || "http://localhost:3000"}/api/google-ads/oauth/callback`,
    response_type: "code",
    access_type: "offline",
    prompt: "consent",
    scope: scopes.join(" "),
    state
  });

  return `https://accounts.google.com/o/oauth2/v2/auth?${params.toString()}`;
}

export async function exchangeCodeForTokens(code: string) {
  if (envFlag("APP_DEMO_MODE", true)) {
    return {
      access_token: "demo-access-token",
      refresh_token: "demo-refresh-token",
      expires_in: 3600
    };
  }

  const response = await fetch("https://oauth2.googleapis.com/token", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      code,
      client_id: process.env.GOOGLE_CLIENT_ID || "",
      client_secret: process.env.GOOGLE_CLIENT_SECRET || "",
      redirect_uri: `${process.env.NEXTAUTH_URL || "http://localhost:3000"}/api/google-ads/oauth/callback`,
      grant_type: "authorization_code"
    })
  });

  if (!response.ok) {
    throw new Error(`OAuth token exchange failed: ${await response.text()}`);
  }

  return response.json();
}
