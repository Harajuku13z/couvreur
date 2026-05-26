import Link from "next/link";
import { AlertTriangle, Bot, CalendarClock, Euro, MousePointerClick, PhoneCall, ShieldCheck } from "lucide-react";
import { AppShell } from "@/components/layout/app-shell";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { PerformanceChart } from "@/components/dashboard/performance-chart";
import { demoCampaign, demoRecommendations } from "@/lib/demo/demo-data";
import { formatCurrency } from "@/lib/utils";

const stats = [
  { label: "Comptes Google Ads", value: "1", icon: ShieldCheck },
  { label: "Campagnes actives", value: "1", icon: Bot },
  { label: "Dépensé aujourd'hui", value: formatCurrency(demoCampaign.spentToday), icon: Euro },
  { label: "Leads estimés", value: "4", icon: PhoneCall },
  { label: "Coût par lead", value: formatCurrency(demoCampaign.costPerConversion), icon: MousePointerClick }
];

export default function DashboardPage() {
  return (
    <AppShell>
      <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <p className="text-sm font-semibold uppercase tracking-wide text-emerald-700">Mode démo actif</p>
          <h1 className="mt-1 text-3xl font-black text-slate-950">Dashboard</h1>
          <p className="mt-2 text-slate-600">Supervision Google Ads pour campagnes couvreur locales.</p>
        </div>
        <div className="flex flex-col gap-2 sm:flex-row">
          <Button asChild variant="secondary"><Link href="/settings/google-ads">Connecter Google Ads</Link></Button>
          <Button asChild><Link href="/campaigns/new">Créer une campagne couvreur</Link></Button>
        </div>
      </div>

      <div className="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        {stats.map((stat) => (
          <Card key={stat.label}>
            <CardContent className="p-5">
              <stat.icon className="h-5 w-5 text-brand-700" />
              <p className="mt-4 text-2xl font-black text-slate-950">{stat.value}</p>
              <p className="text-sm text-slate-600">{stat.label}</p>
            </CardContent>
          </Card>
        ))}
      </div>

      <div className="mt-6 grid gap-6 xl:grid-cols-[1.4fr_0.6fr]">
        <Card>
          <CardHeader>
            <CardTitle>Performance récente</CardTitle>
          </CardHeader>
          <CardContent>
            <PerformanceChart data={demoCampaign.chart} />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Alertes et prochain scan</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="rounded-2xl bg-orange-50 p-4 text-orange-900">
              <AlertTriangle className="mb-3 h-5 w-5" />
              Dépenses détectées sur recherches formation, salaire et Castorama.
            </div>
            <div className="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
              <span className="flex items-center gap-2 font-semibold"><CalendarClock className="h-4 w-4" /> Prochain scan</span>
              <Badge variant="info">{demoCampaign.nextScanAt}</Badge>
            </div>
          </CardContent>
        </Card>
      </div>

      <Card className="mt-6">
        <CardHeader>
          <CardTitle>Dernières actions IA</CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          {demoRecommendations.map((rec) => (
            <div key={rec.id} className="flex flex-col gap-2 rounded-2xl border border-slate-200 p-4 md:flex-row md:items-center md:justify-between">
              <div>
                <p className="font-bold text-slate-950">{rec.title}</p>
                <p className="text-sm text-slate-600">{rec.description}</p>
              </div>
              <Badge variant={rec.riskLevel === "high" ? "danger" : rec.riskLevel === "medium" ? "warning" : "success"}>
                {rec.confidence}% confiance
              </Badge>
            </div>
          ))}
        </CardContent>
      </Card>
    </AppShell>
  );
}
