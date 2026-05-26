"use client";

import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Select } from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";

const services = [
  "Couverture",
  "Réparation toiture",
  "Fuite toiture",
  "Zinguerie",
  "Nettoyage toiture",
  "Démoussage toiture",
  "Isolation toiture",
  "Rénovation toiture",
  "Façade"
];

export function CampaignWizard() {
  const [step, setStep] = useState(1);
  const [loading, setLoading] = useState(false);
  const [proposal, setProposal] = useState<Record<string, unknown> | null>(null);
  const [proposalText, setProposalText] = useState("");
  const [form, setForm] = useState({
    businessName: "Entreprise Démo Couverture",
    websiteUrl: "https://example.fr",
    phone: "06 00 00 00 00",
    email: "contact@example.fr",
    address: "Chalon-sur-Saône",
    serviceAreas: "Chalon-sur-Saône, Saône-et-Loire",
    services: ["Couverture", "Fuite toiture", "Zinguerie"],
    offer: "-30% sur vos travaux",
    dailyBudget: 15,
    schedule: "Lundi-Samedi 08:00-19:00",
    objective: "Appels"
  });

  async function generate() {
    setLoading(true);
    const response = await fetch("/api/campaigns/generate", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        ...form,
        serviceAreas: form.serviceAreas.split(",").map((item) => item.trim()).filter(Boolean)
      })
    });
    const data = await response.json();
    setProposal(data);
    setProposalText(JSON.stringify(data, null, 2));
    setLoading(false);
    setStep(3);
  }

  async function publish() {
    setLoading(true);
    let payload = proposal;
    try {
      payload = JSON.parse(proposalText);
      setProposal(payload);
    } catch {
      setLoading(false);
      alert("Le JSON de validation n'est pas valide. Corrigez-le avant publication.");
      return;
    }

    const response = await fetch("/api/campaigns/publish", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ proposal: payload, humanValidationMode: true, dryRun: true })
    });
    const data = await response.json();
    setProposal(data);
    setLoading(false);
    setStep(4);
  }

  return (
    <div className="space-y-6">
      <div className="grid gap-3 md:grid-cols-4">
        {["Entreprise", "Génération IA", "Validation humaine", "Publication"].map((label, index) => (
          <div key={label} className={`rounded-2xl border p-4 ${step === index + 1 ? "border-brand-600 bg-brand-50" : "border-slate-200 bg-white"}`}>
            <p className="text-sm font-bold text-slate-950">Étape {index + 1}</p>
            <p className="text-sm text-slate-600">{label}</p>
          </div>
        ))}
      </div>

      {step === 1 && (
        <Card>
          <CardHeader><CardTitle>Informations entreprise</CardTitle></CardHeader>
          <CardContent className="grid gap-4 md:grid-cols-2">
            <Input value={form.businessName} onChange={(e) => setForm({ ...form, businessName: e.target.value })} placeholder="Nom entreprise" />
            <Input value={form.websiteUrl} onChange={(e) => setForm({ ...form, websiteUrl: e.target.value })} placeholder="Site web" />
            <Input value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} placeholder="Téléphone" />
            <Input value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} placeholder="Email" />
            <Input value={form.address} onChange={(e) => setForm({ ...form, address: e.target.value })} placeholder="Adresse" />
            <Input value={form.serviceAreas} onChange={(e) => setForm({ ...form, serviceAreas: e.target.value })} placeholder="Zones d'intervention" />
            <Input value={form.offer} onChange={(e) => setForm({ ...form, offer: e.target.value })} placeholder="Offre commerciale" />
            <Input type="number" value={form.dailyBudget} onChange={(e) => setForm({ ...form, dailyBudget: Number(e.target.value) })} placeholder="Budget journalier maximum" />
            <Input value={form.schedule} onChange={(e) => setForm({ ...form, schedule: e.target.value })} placeholder="Horaires" />
            <Select value={form.objective} onChange={(e) => setForm({ ...form, objective: e.target.value })}>
              <option>Appels</option>
              <option>Formulaires</option>
              <option>Devis</option>
              <option>Trafic site</option>
            </Select>
            <div className="md:col-span-2">
              <p className="mb-2 text-sm font-semibold text-slate-900">Services proposés</p>
              <div className="grid gap-2 md:grid-cols-3">
                {services.map((service) => (
                  <label key={service} className="flex items-center gap-2 rounded-xl border border-slate-200 p-3 text-sm">
                    <input
                      type="checkbox"
                      checked={form.services.includes(service)}
                      onChange={(e) => {
                        setForm({
                          ...form,
                          services: e.target.checked ? [...form.services, service] : form.services.filter((item) => item !== service)
                        });
                      }}
                    />
                    {service}
                  </label>
                ))}
              </div>
            </div>
            <div className="md:col-span-2">
              <Button onClick={() => setStep(2)}>Continuer</Button>
            </div>
          </CardContent>
        </Card>
      )}

      {step === 2 && (
        <Card>
          <CardHeader><CardTitle>Génération IA</CardTitle></CardHeader>
          <CardContent>
            <p className="text-slate-600">L'IA va proposer groupes d'annonces, mots-clés, négatifs, annonces, extensions, sitelinks et callouts.</p>
            <Button className="mt-6" onClick={generate} disabled={loading}>
              {loading ? "Génération..." : "Générer la structure Google Ads"}
            </Button>
          </CardContent>
        </Card>
      )}

      {step === 3 && (
        <Card>
          <CardHeader><CardTitle>Validation humaine obligatoire</CardTitle></CardHeader>
          <CardContent className="space-y-4">
            <Textarea rows={22} value={proposalText} onChange={(e) => setProposalText(e.target.value)} />
            <div className="rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-900">
              Vérifications avant publication: budget max, tracking, URL finale, téléphone, zones, horaires et validation humaine active.
            </div>
            <Button onClick={publish} disabled={loading}>{loading ? "Publication..." : "Publier dans Google Ads en dry-run"}</Button>
          </CardContent>
        </Card>
      )}

      {step === 4 && (
        <Card>
          <CardHeader><CardTitle>Résultat publication</CardTitle></CardHeader>
          <CardContent>
            <pre className="overflow-auto rounded-2xl bg-slate-950 p-5 text-sm text-slate-100">{JSON.stringify(proposal, null, 2)}</pre>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
