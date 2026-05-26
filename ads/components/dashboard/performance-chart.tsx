"use client";

import { Area, AreaChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from "recharts";

export function PerformanceChart({ data }: { data: Array<{ label: string; cost: number; leads: number }> }) {
  return (
    <div className="h-72 w-full">
      <ResponsiveContainer>
        <AreaChart data={data}>
          <defs>
            <linearGradient id="cost" x1="0" x2="0" y1="0" y2="1">
              <stop offset="5%" stopColor="#0f70c7" stopOpacity={0.3} />
              <stop offset="95%" stopColor="#0f70c7" stopOpacity={0} />
            </linearGradient>
          </defs>
          <CartesianGrid strokeDasharray="3 3" stroke="#e2e8f0" />
          <XAxis dataKey="label" stroke="#64748b" />
          <YAxis stroke="#64748b" />
          <Tooltip />
          <Area type="monotone" dataKey="cost" stroke="#0f70c7" fill="url(#cost)" name="Dépense" />
          <Area type="monotone" dataKey="leads" stroke="#10b981" fill="#d1fae5" name="Leads" />
        </AreaChart>
      </ResponsiveContainer>
    </div>
  );
}
