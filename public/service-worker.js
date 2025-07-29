// This is a placeholder for the service worker.
// It allows the app to be installable.
// No offline capabilities are implemented yet.

self.addEventListener('install', (event) => {
  console.log('Service worker installing...');
});

self.addEventListener('fetch', (event) => {
  event.respondWith(fetch(event.request));
});
