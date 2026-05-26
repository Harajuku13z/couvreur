import { AppShell } from "@/components/layout/app-shell";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export default function GoogleAdsSettingsPage() {
  return (
    <AppShell>
      <h1 className="text-3xl font-black text-slate-950">Connexion Google Ads</h1>
      <p className="mt-2 text-slate-600">Connectez OAuth 2.0, listez les customer IDs et choisissez le compte à gérer.</p>

      <Card className="mt-8">
        <CardHeader><CardTitle>État connexion</CardTitle></CardHeader>
        <CardContent className="space-y-5">
          <Badge variant="warning">Mode démo ou OAuth non configuré</Badge>
          <p className="text-slate-700">Les tokens seront chiffrés avant stockage. Le mode dry-run reste disponible pour tester.</p>
          <Button asChild>
            <a href="/api/google-ads/oauth/start">Connecter mon compte Google Ads</a>
          </Button>
          <div className="rounded-2xl bg-slate-50 p-4">
            <p className="font-bold">Compte disponible en démo</p>
            <p className="text-sm text-slate-600">1234567890 - Compte démo Couvreur - EUR - Europe/Paris</p>
          </div>
        </CardContent>
      </Card>
    </AppShell>
  );
}
