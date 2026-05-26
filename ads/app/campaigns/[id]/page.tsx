import { AppShell } from "@/components/layout/app-shell";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, Td, Th } from "@/components/ui/table";
import { demoCampaign, demoRecommendations, demoSearchTerms } from "@/lib/demo/demo-data";
import { formatCurrency, microsToCurrency } from "@/lib/utils";

export default function CampaignDetailPage() {
  return (
    <AppShell>
      <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 className="text-3xl font-black text-slate-950">{demoCampaign.name}</h1>
          <p className="mt-2 text-slate-600">Vue d'ensemble, termes de recherche, mots-clés négatifs et recommandations.</p>
        </div>
        <form action={`/api/campaigns/${demoCampaign.id}/scan`} method="post">
          <Button>Lancer un scan manuel</Button>
        </form>
      </div>

      <div className="mt-6 grid gap-4 md:grid-cols-4">
        <Card><CardContent><p className="text-sm text-slate-600">Budget</p><p className="text-2xl font-black">{formatCurrency(demoCampaign.dailyBudget)}</p></CardContent></Card>
        <Card><CardContent><p className="text-sm text-slate-600">Clics</p><p className="text-2xl font-black">{demoCampaign.clicks}</p></CardContent></Card>
        <Card><CardContent><p className="text-sm text-slate-600">Conversions</p><p className="text-2xl font-black">{demoCampaign.conversions}</p></CardContent></Card>
        <Card><CardContent><p className="text-sm text-slate-600">Score santé</p><p className="text-2xl font-black">{demoCampaign.healthScore}/100</p></CardContent></Card>
      </div>

      <Card className="mt-8 overflow-hidden">
        <CardHeader>
          <CardTitle>Termes de recherche</CardTitle>
        </CardHeader>
        <CardContent className="overflow-x-auto p-0">
          <Table>
            <thead>
              <tr>
                <Th>Terme recherché</Th>
                <Th>Clics</Th>
                <Th>Coût</Th>
                <Th>Conversions</Th>
                <Th>Pertinence IA</Th>
                <Th>Recommandation</Th>
                <Th>Action</Th>
              </tr>
            </thead>
            <tbody>
              {demoSearchTerms.map((term) => (
                <tr key={term.term}>
                  <Td className="font-semibold">{term.term}</Td>
                  <Td>{term.clicks}</Td>
                  <Td>{formatCurrency(microsToCurrency(term.costMicros))}</Td>
                  <Td>{term.conversions}</Td>
                  <Td>{term.aiRelevanceScore}/100</Td>
                  <Td>
                    <Badge variant={term.aiDecision === "exclude" ? "danger" : "success"}>
                      {term.aiDecision === "exclude" ? "exclure" : "garder"}
                    </Badge>
                  </Td>
                  <Td>
                    <Button size="sm" variant={term.aiDecision === "exclude" ? "danger" : "secondary"}>
                      {term.aiDecision === "exclude" ? "Exclure" : "Surveiller"}
                    </Button>
                  </Td>
                </tr>
              ))}
            </tbody>
          </Table>
        </CardContent>
      </Card>

      <Card className="mt-8">
        <CardHeader><CardTitle>Recommandations IA</CardTitle></CardHeader>
        <CardContent className="space-y-4">
          {demoRecommendations.map((rec) => (
            <div key={rec.id} className="rounded-2xl border border-slate-200 p-4">
              <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                  <p className="font-bold text-slate-950">{rec.title}</p>
                  <p className="mt-1 text-sm text-slate-600">{rec.description}</p>
                </div>
                <Badge variant={rec.riskLevel === "high" ? "danger" : "success"}>{rec.riskLevel}</Badge>
              </div>
              <div className="mt-4 flex gap-2">
                <Button size="sm" variant="success">Accepter</Button>
                <Button size="sm" variant="secondary">Rejeter</Button>
              </div>
            </div>
          ))}
        </CardContent>
      </Card>
    </AppShell>
  );
}
