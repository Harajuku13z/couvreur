import Link from "next/link";
import { BarChart3, FileText, Gauge, Lightbulb, Megaphone, Settings, ShieldCheck } from "lucide-react";
import { cn } from "@/lib/utils";

const links = [
  { href: "/dashboard", label: "Dashboard", icon: Gauge },
  { href: "/campaigns", label: "Campagnes", icon: Megaphone },
  { href: "/recommendations", label: "Recommandations", icon: Lightbulb },
  { href: "/reports", label: "Rapports", icon: FileText },
  { href: "/settings/google-ads", label: "Google Ads", icon: BarChart3 },
  { href: "/settings", label: "Paramètres", icon: Settings }
];

export function Sidebar({ className }: { className?: string }) {
  return (
    <aside className={cn("min-h-screen w-full border-r border-slate-200 bg-white px-4 py-5 lg:w-72", className)}>
      <Link href="/" className="flex items-center gap-3 px-2">
        <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-900 text-white">
          <ShieldCheck className="h-5 w-5" />
        </span>
        <span>
          <span className="block text-base font-black text-slate-950">Couvreur Ads</span>
          <span className="block text-xs font-semibold text-emerald-700">Pilot IA sécurisé</span>
        </span>
      </Link>

      <nav className="mt-8 space-y-1">
        {links.map((link) => (
          <Link
            key={link.href}
            href={link.href}
            className="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-950"
          >
            <link.icon className="h-4 w-4" />
            {link.label}
          </Link>
        ))}
      </nav>

      <div className="mt-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
        <p className="text-sm font-bold text-emerald-950">Validation humaine active</p>
        <p className="mt-1 text-xs leading-relaxed text-emerald-800">
          Les budgets, créations et changements massifs restent bloqués sans validation.
        </p>
      </div>
    </aside>
  );
}
