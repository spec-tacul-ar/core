import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import resize from '@alpinejs/resize';

Alpine.plugin(focus);
Alpine.plugin(resize);

window.Alpine = Alpine;

Alpine.start();
