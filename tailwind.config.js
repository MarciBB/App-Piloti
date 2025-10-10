/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./app/**/*.{js,ts,jsx,tsx}", "./components/**/*.{js,ts,jsx,tsx}", "./src/app/**/*.{js,ts,jsx,tsx,mdx}", "./src/components/**/*.{js,ts,jsx,tsx,mdx}"],
  theme: {
    extend: {
      colors: {
        primary: {
          navy: "var(--primary-navy)",
          blue: "var(--primary-blue)",
        },
        accent: {
          orange: "var(--accent-orange)",
        },
        light: {
          blue: "var(--light-blue)",
        }
      },
      borderRadius: {
        xl: "0.75rem",
      },
      boxShadow: {
        card: "0 4px 20px rgba(2, 6, 23, .06)",
      }
    },
  },
  plugins: [],
};
