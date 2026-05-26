import Link from "next/link";
import { AppShell } from "@/components/layout/app-shell";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, Td, Th } from "@/components/ui/table";
import { demoCampaign } from "@/lib/demo/demo-data";
import { formatCurrency } from "@/lib/utils";

export default function CampaignsPage() {
  return (
    <AppShell>
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-black text-slate-950">Campagnes</h1>
          <p className="mt-2 text-slate-600">Liste des campagnes surveillées par l'IA.</p>
        </div>
        <Button asChild><Link href="/campaigns/new">Nouvelle campagne</Link></Button>
      </div>

      <Card className="mt-8 overflow-hidden">
        <CardHeader>
          <CardTitle>Campagnes actives</CardTitle>
        </CardHeader>
        <CardContent className="overflow-x-auto p-0">
          <Table>
            <thead>
              <tr>
                <Th>Nom</Th>
                <Th>Statut</Th>
                <Th>Budget</Th>
                <Th>Coût aujourd'hui</Th>
                <Th>Clics</Th>
                <Th>Conversions</Th>
                <Th>CPA</Th>
                <Th>Score santé</Th>
                <Th>Dernier scan IA</Th>
                <Th>Actions</Th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <Td className="font-semibold">{demoCampaign.name}</Td>
                <Td><Badge variant="success">Active</Badge></Td>
                <Td>{formatCurrency(demoCampaign.dailyBudget)} / jour</Td>
                <Td>{formatCurrency(demoCampaign.spentToday)}</Td>
                <Td>{demoCampaign.clicks}</Td>
                <Td>{demoCampaign.conversions}</Td>
                <Td>{formatCurrency(demoCampaign.costPerConversion)}</Td>
                <Td><Badge variant="info">{demoCampaign.healthScore}/100</Badge></Td>
                <Td>{demoCampaign.lastScanAt}</Td>
                <Td><Button asChild size="sm" variant="secondary"><Link href={`/campaigns/${demoCampaign.id}`}>Voir</Link></Button></Td>
              </tr>
            </tbody>
          </Table>
        </CardContent>
      </Card>
    </AppShell>
  );
}
