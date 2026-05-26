import { AppShell } from "@/components/layout/app-shell";
import { CampaignWizard } from "@/app/campaigns/new/campaign-wizard";

export default function NewCampaignPage() {
  return (
    <AppShell>
      <div className="mb-8">
        <p className="text-sm font-semibold uppercase tracking-wide text-emerald-700">Wizard sécurisé</p>
        <h1 className="mt-1 text-3xl font-black text-slate-950">Créer une campagne couvreur</h1>
        <p className="mt-2 text-slate-600">Aucune campagne réelle n'est publiée sans validation humaine et vérifications de sécurité.</p>
      </div>
      <CampaignWizard />
    </AppShell>
  );
}
