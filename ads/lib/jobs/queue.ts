import { Queue } from "bullmq";

export function getScanQueue() {
  if (!process.env.REDIS_URL) return null;

  return new Queue("scheduled-scans", {
    connection: {
      url: process.env.REDIS_URL
    }
  });
}

export async function enqueueScan(campaignId?: string) {
  const queue = getScanQueue();
  if (!queue) return { queued: false, reason: "REDIS_URL absent, utilisez Vercel Cron ou le mode manuel." };

  await queue.add("scan", { campaignId }, { removeOnComplete: 100, removeOnFail: 100 });
  return { queued: true };
}
