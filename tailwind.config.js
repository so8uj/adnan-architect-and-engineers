/**
 * Tailwind is compiled to public/assets/css/tailwind.css (npm run build:css).
 * Brand colours come from CSS variables (set from Admin -> Theme), so the colours can change without a rebuild.
 */
const rgb = (name) => `rgb(var(--brand-${name}) / <alpha-value>)`;

module.exports = {
  darkMode: 'class',
  content: [
    './resources/views/**/*.blade.php',
    './public/assets/js/**/*.js',
    './app/**/*.php',
    './config/admin.php',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['"Hind Siliguri"', 'system-ui', 'sans-serif'],
        display: ['Poppins', '"Hind Siliguri"', 'sans-serif'],
      },
      colors: {
        brand: { 50: rgb(50), 100: rgb(100), 400: rgb(400), 500: rgb(500), 600: rgb(600), 700: rgb(700), 900: rgb(900) },
        ink: { 800: '#1c1c1f', 900: '#121214', 950: '#0b0b0d' },
      },
    },
  },
};
