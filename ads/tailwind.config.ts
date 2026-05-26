import type { Config } from "tailwindcss";

const config: Config = {
  darkMode: ["class"],
  content: [
    "./app/**/*.{ts,tsx}",
    "./components/**/*.{ts,tsx}",
    "./lib/**/*.{ts,tsx}"
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50: "#eef8ff",
          100: "#d9efff",
          500: "#0f70c7",
          600: "#075ca6",
          700: "#06477f",
          900: "#08243f"
        },
        success: "#10b981",
        warning: "#f97316",
        danger: "#ef4444"
      },
      boxShadow: {
        soft: "0 18px 60px rgba(8, 36, 63, 0.12)"
      },
      fontFamily: {
        sans: ["var(--font-geist-sans)", "Inter", "ui-sans-serif", "system-ui"]
      }
    }
  },
  plugins: []
};

export default config;
