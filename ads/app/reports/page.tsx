import { AppShell } from "@/components/layout/app-shell";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { demoCampaign, demoSearchTerms } from "@/lib/demo/demo-data";
import { formatCurrency } from "@/lib/utils";

export default function ReportsPage() {
  return (
    <AppShell>
      <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 className="text-3xl font-black text-slate-950">Rapports</h1>
          <p className="mt-2 text-slate-600">Rapports quotidiens, hebdomadaires, export PDF et envoi email.</p>
        </div>
        <form action="/api/reports/generate" method="post"><Button>Générer un rapport manuel</Button></form>
      </div>

      <Card className="mt-8">
        <CardHeader><CardTitle>Rapport démo du jour</CardTitle></CardHeader>
        <CardContent className="space-y-5">
          <p className="text-lg leading-8 text-slate-800">
            Votre campagne {demoCampaign.name} a dépensé {formatCurrency(demoCampaign.spentToday)} aujourd'hui.
            Elle a généré {demoCampaign.clicks} clics, 3 appels et 1 formulaire. Le coût par lead estimé est de {formatCurrency(demoCampaign.costPerConversion)}.
            L'IA a détecté 5 recherches inutiles proposées en exclusion.
          </p>
          <div className="grid gap-4 md:grid-cols-4">
            <div className="rounded-2xl bg-slate-50 p-4"><b>Clics</b><br />{demoCampaign.clicks}</div>
            <div className="rounded-2xl bg-slate-50 p-4"><b>Conversions</b><br />{demoCampaign.conversions}</div>
            <div className="rounded-2xl bg-slate-50 p-4"><b>CPA</b><br />{formatCurrency(demoCampaign.costPerConversion)}</div>
            <div className="rounded-2xl bg-slate-50 p-4"><b>Termes exclus</b><br />{demoSearchTerms.filter((term) => term.aiDecision === "exclude").length}</div>
          </div>
          <div className="flex gap-2">
            <Button variant="secondary">Exporter PDF</Button>
            <Button variant="secondary">Envoyer par email</Button>
          </div>
        </CardContent>
      </Card>
    </AppShell>
  );
}
