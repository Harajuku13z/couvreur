import Link from "next/link";
import { AlertTriangle, CheckCircle2, Lock, Radar, ShieldCheck, Sparkles } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

const features = [
  "Création de campagnes locales",
  "Analyse des termes de recherche",
  "Exclusion de mots-clés inutiles",
  "Rapports automatiques",
  "Alertes budget",
  "Mode validation humaine"
];

export default function HomePage() {
  return (
    <main className="overflow-hidden bg-slate-950 text-white">
      <section className="relative px-4 py-20 md:py-28">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.25),transparent_34%),radial-gradient(circle_at_70%_20%,rgba(59,130,246,0.26),transparent_32%)]" />
        <div className="relative mx-auto max-w-7xl">
          <div className="max-w-3xl">
            <span className="inline-flex items-center rounded-full border border-emerald-400/40 bg-emerald-400/10 px-4 py-2 text-sm font-semibold text-emerald-100">
              <ShieldCheck className="mr-2 h-4 w-4" /> Google Ads piloté, jamais dangereux
            </span>
            <h1 className="mt-7 text-5xl font-black tracking-tight md:text-7xl">
              Automatisez vos campagnes Google Ads pour couvreurs
            </h1>
            <p className="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
              Un copilote IA qui surveille vos campagnes, détecte les dépenses inutiles, exclut les mauvais mots-clés et vous envoie des rapports clairs.
            </p>
            <div className="mt-9 flex flex-col gap-3 sm:flex-row">
              <Button asChild size="lg" variant="success">
                <Link href="/login">Commencer</Link>
              </Button>
              <Button asChild size="lg" variant="secondary">
                <Link href="/dashboard">Voir la démo</Link>
              </Button>
            </div>
          </div>
        </div>
      </section>

      <section className="bg-white px-4 py-20 text-slate-950">
        <div className="mx-auto grid max-w-7xl gap-6 lg:grid-cols-3">
          <Card className="bg-red-50">
            <CardHeader>
              <CardTitle className="flex items-center gap-2"><AlertTriangle className="h-5 w-5 text-red-600" /> Problème</CardTitle>
            </CardHeader>
            <CardContent className="text-slate-700">
              Les campagnes couvreurs gaspillent souvent du budget sur emploi, formation, tuto, salaire, Castorama, Leroy Merlin ou recherches bricolage.
            </CardContent>
          </Card>
          <Card className="bg-brand-50">
            <CardHeader>
              <CardTitle className="flex items-center gap-2"><Radar className="h-5 w-5 text-brand-700" /> Solution</CardTitle>
            </CardHeader>
            <CardContent className="text-slate-700">
              Une surveillance automatique toutes les 2 à 3 heures analyse les termes, coûts, conversions et anomalies avant que le budget parte trop loin.
            </CardContent>
          </Card>
          <Card className="bg-emerald-50">
            <CardHeader>
              <CardTitle className="flex items-center gap-2"><Lock className="h-5 w-5 text-emerald-700" /> Sécurité</CardTitle>
            </CardHeader>
            <CardContent className="text-slate-700">
              Budget plafonné, validation humaine avant actions sensibles, logs complets, dry-run et rollback manuel prévu.
            </CardContent>
          </Card>
        </div>
      </section>

      <section className="bg-slate-50 px-4 py-20 text-slate-950">
        <div className="mx-auto max-w-7xl">
          <h2 className="text-3xl font-black md:text-5xl">Fonctionnalités</h2>
          <div className="mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {features.map((feature) => (
              <div key={feature} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <CheckCircle2 className="h-5 w-5 text-emerald-600" />
                <p className="mt-4 font-bold">{feature}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="bg-white px-4 py-20 text-slate-950">
        <div className="mx-auto max-w-7xl">
          <h2 className="text-3xl font-black md:text-5xl">Pricing fictif</h2>
          <div className="mt-10 grid gap-6 md:grid-cols-3">
            {["Starter: reporting uniquement", "Pro: recommandations IA", "Agency: multi-comptes clients"].map((plan) => (
              <Card key={plan} className="p-6">
                <Sparkles className="h-6 w-6 text-brand-700" />
                <h3 className="mt-5 text-xl font-black">{plan}</h3>
                <p className="mt-3 text-slate-600">Mode sécurité inclus, validation humaine par défaut et logs d'actions.</p>
              </Card>
            ))}
          </div>
        </div>
      </section>
    </main>
  );
}
