import { AppShell } from "@/components/layout/app-shell";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { demoRecommendations } from "@/lib/demo/demo-data";

export default function RecommendationsPage() {
  return (
    <AppShell>
      <h1 className="text-3xl font-black text-slate-950">Recommandations IA</h1>
      <p className="mt-2 text-slate-600">Chaque recommandation est validée, rejetée ou conservée en attente.</p>

      <div className="mt-8 space-y-4">
        {demoRecommendations.map((rec) => (
          <Card key={rec.id}>
            <CardHeader>
              <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                  <CardTitle>{rec.title}</CardTitle>
                  <p className="mt-2 text-slate-600">{rec.description}</p>
                </div>
                <Badge variant={rec.riskLevel === "high" ? "danger" : rec.riskLevel === "medium" ? "warning" : "success"}>
                  Risque {rec.riskLevel}
                </Badge>
              </div>
            </CardHeader>
            <CardContent>
              <div className="grid gap-3 text-sm md:grid-cols-3">
                <div className="rounded-2xl bg-slate-50 p-4"><b>Confiance</b><br />{rec.confidence}%</div>
                <div className="rounded-2xl bg-slate-50 p-4"><b>Impact estimé</b><br />{rec.impact}</div>
                <div className="rounded-2xl bg-slate-50 p-4"><b>Statut</b><br />{rec.status}</div>
              </div>
              <div className="mt-5 flex gap-2">
                <form action={`/api/recommendations/${rec.id}/accept`} method="post"><Button variant="success">Accepter</Button></form>
                <form action={`/api/recommendations/${rec.id}/reject`} method="post"><Button variant="secondary">Rejeter</Button></form>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    </AppShell>
  );
}
