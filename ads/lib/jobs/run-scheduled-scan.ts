import { runScheduledScan } from "@/lib/jobs/scheduled-scan";

runScheduledScan({ dryRun: true, autoNegativeKeywords: false })
  .then((result) => {
    console.log(JSON.stringify(result, null, 2));
    process.exit(0);
  })
  .catch((error) => {
    console.error(error);
    process.exit(1);
  });
