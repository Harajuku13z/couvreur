import { NextResponse } from "next/server";

export async function POST(_: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  return NextResponse.json({
    success: true,
    id,
    status: "rejected",
    message: "Démo: recommandation rejetée. En production, écrire ActionLog."
  });
}
