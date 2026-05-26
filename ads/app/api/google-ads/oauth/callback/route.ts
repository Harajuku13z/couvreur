import { NextRequest, NextResponse } from "next/server";
import { exchangeCodeForTokens } from "@/lib/google-ads/oauth";
import { encryptSecret } from "@/lib/security/encryption";

export async function GET(request: NextRequest) {
  const code = request.nextUrl.searchParams.get("code");
  if (!code) return NextResponse.json({ error: "Code OAuth manquant" }, { status: 400 });

  const tokens = await exchangeCodeForTokens(code);
  return NextResponse.json({
    success: true,
    message: "Tokens reçus. TODO: associer à l'utilisateur connecté et sauvegarder en base.",
    encryptedPreview: {
      accessToken: tokens.access_token ? encryptSecret(tokens.access_token).slice(0, 24) + "..." : null,
      refreshToken: tokens.refresh_token ? encryptSecret(tokens.refresh_token).slice(0, 24) + "..." : null
    }
  });
}
