import { NextResponse } from "next/server";

export async function POST(_: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  return NextResponse.json({
    success: true,
    id,
    status: "accepted",
    message: "Démo: recommandation acceptée. En production, appliquer l'action puis écrire ActionLog."
  });
}
