import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import '../scss/app.scss';
import './bootstrap';

const appName = import.meta.env.VITE_APP_NAME || 'Marketplace';

createInertiaApp({
  title: title => (title ? `${title} - ${appName}` : appName),
  resolve: name => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
  setup({ el, App, props }) {
    const root = createRoot(el);
    root.render(<App {...props} />);
  },
}).then(() => document.getElementById('app').removeAttribute('data-page'));
