/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
     './index.html',
  ],
  theme: {
    extend: {
      animation: {
        fadeIn: 'fadeIn 1s ease-out forwards',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: 0 },
          '100%': { opacity: 1 },
        },

      },
      colors: {
        'brand-blue': '#00AEEF',
      },
    },
  },
  plugins: [
    require('@tailwindcss/line-clamp'),
    
  ],
}
