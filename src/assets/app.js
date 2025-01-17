import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

//

function initializeCharts() {
  const canvases = document.querySelectorAll('canvas');
  canvases.forEach(canvas => {
    new Chart(canvas, {
      type: 'line',
      data: {
        labels: ['11/01/2025', '12/01/2025', '13/01/2025', '14/01/2025', '15/01/2025', '16/01/2025'],
        datasets: [{
          label: 'Montant',
          data: [12, 19, 3, 5, 2, 3],
          borderWidth: 1,
          tension: 0.3
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  });
}

document.addEventListener('turbo:load', initializeCharts);
