import './bootstrap';

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import Chart from 'chart.js/auto';

Alpine.plugin(focus);
window.Alpine = Alpine;
Alpine.start();

window.Chart = Chart;
