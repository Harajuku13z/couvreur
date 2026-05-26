import Link from "next/link";
import { ShieldCheck } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";

export default function LoginPage() {
  return (
    <main className="grid min-h-screen bg-slate-50 lg:grid-cols-2">
      <section className="hidden bg-brand-900 p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <Link href="/" className="flex items-center gap-3 text-lg font-black">
          <ShieldCheck className="h-7 w-7" /> Couvreur Ads Pilot
        </Link>
        <div>
          <p className="text-4xl font-black leading-tight">Une IA qui surveille, mais ne dépense jamais sans garde-fou.</p>
          <p className="mt-5 text-brand-100">Validation humaine, logs et dry-run sont actifs par défaut.</p>
        </div>
      </section>

      <section className="flex items-center justify-center p-6">
        <div className="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-soft">
          <h1 className="text-3xl font-black text-slate-950">Connexion</h1>
          <p className="mt-2 text-slate-600">Connectez-vous pour accéder au dashboard.</p>

          <form className="mt-8 space-y-4">
            <div>
              <label className="mb-2 block text-sm font-semibold text-slate-900" htmlFor="email">Email</label>
              <Input id="email" name="email" type="email" placeholder="vous@entreprise.fr" />
            </div>
            <div>
              <label className="mb-2 block text-sm font-semibold text-slate-900" htmlFor="password">Mot de passe</label>
              <Input id="password" name="password" type="password" placeholder="Votre mot de passe" />
            </div>
            <Button type="button" className="w-full">Se connecter</Button>
          </form>

          <div className="my-6 flex items-center gap-3 text-xs text-slate-500">
            <span className="h-px flex-1 bg-slate-200" /> ou <span className="h-px flex-1 bg-slate-200" />
          </div>

          <Button variant="secondary" className="w-full" type="button">
            Connexion avec Google
          </Button>
          <p className="mt-5 text-center text-xs text-slate-500">Magic link prévu via Auth.js/Resend.</p>
        </div>
      </section>
    </main>
  );
}
