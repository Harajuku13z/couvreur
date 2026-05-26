import { NextRequest, NextResponse } from "next/server";
import { z } from "zod";
import { generateCampaignStructure } from "@/lib/ai/openai";

const schema = z.object({
  businessName: z.string().min(2),
  websiteUrl: z.string().url(),
  phone: z.string().min(5),
  email: z.string().optional(),
  address: z.string().optional(),
  serviceAreas: z.array(z.string()).min(1),
  services: z.array(z.string()).min(1),
  offer: z.string().optional(),
  dailyBudget: z.number().positive().max(500),
  schedule: z.string(),
  objective: z.string()
});

export async function POST(request: NextRequest) {
  const body = await request.json();
  const parsed = schema.safeParse(body);
  if (!parsed.success) return NextResponse.json({ error: parsed.error.flatten() }, { status: 422 });

  const proposal = await generateCampaignStructure(parsed.data);
  return NextResponse.json(proposal);
}
