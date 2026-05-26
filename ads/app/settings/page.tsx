import { AppShell } from "@/components/layout/app-shell";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Select } from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";
import { Button } from "@/components/ui/button";
import { defaultNegativeKeywords } from "@/lib/demo/demo-data";

export default function SettingsPage() {
  return (
    <AppShell>
      <h1 className="text-3xl font-black text-slate-950">Paramètres</h1>
      <p className="mt-2 text-slate-600">Réglages globaux d'automatisation et de sécurité.</p>

      <Card className="mt-8">
        <CardHeader><CardTitle>Mode IA et sécurité</CardTitle></CardHeader>
        <CardContent className="grid gap-5 md:grid-cols-2">
          <div>
            <label className="mb-2 block text-sm font-semibold">Mode IA</label>
            <Select defaultValue="recommendations">
              <option value="reporting">Reporting uniquement</option>
              <option value="recommendations">Recommandations avec validation</option>
              <option value="auto_negative">Auto-exclusion contrôlée</option>
            </Select>
          </div>
          <div>
            <label className="mb-2 block text-sm font-semibold">Fréquence de scan</label>
            <Select defaultValue="3h">
              <option value="2h">Toutes les 2h</option>
              <option value="3h">Toutes les 3h</option>
              <option value="daily">Quotidien</option>
            </Select>
          </div>
          <Input defaultValue="50" placeholder="Budget max journalier autorisé" />
          <Input defaultValue="25" placeholder="Seuil alerte coût" />
          <Input placeholder="Email de rapport" />
          <Input placeholder="WhatsApp/Telegram (interface prête)" />
          <Textarea className="md:col-span-2" rows={8} defaultValue={defaultNegativeKeywords.join("\n")} />
          <Textarea className="md:col-span-2" rows={4} placeholder="Zones interdites" />
          <Textarea className="md:col-span-2" rows={4} placeholder="Horaires autorisés" />
          <div className="rounded-2xl bg-emerald-50 p-4 text-emerald-900 md:col-span-2">
            Validation obligatoire avant hausse budget: toujours activée.
          </div>
          <Button className="md:col-span-2">Enregistrer</Button>
        </CardContent>
      </Card>
    </AppShell>
  );
}
