/* Exibe exclusivamente as coordenadas da loja salvas no banco: sem GPS do visitante. */
(() => {
  const element = document.getElementById('store-map');
  if (!element) return;
  const status = document.getElementById('store-map-status');
  const lat = Number(element.dataset.lat);
  const lon = Number(element.dataset.lon);
  const nome = element.dataset.name || 'VittaPet Store';

  if (!Number.isFinite(lat) || !Number.isFinite(lon) || Math.abs(lat)>90 || Math.abs(lon)>180) {
    if (status) status.textContent = 'Localização ainda não configurada.';
    return;
  }
  if (typeof L === 'undefined') {
    if (status) status.textContent = 'Mapa temporariamente indisponível. Use o endereço e os links de rota.';
    return;
  }
  try {
    const map = L.map(element, {scrollWheelZoom:false}).setView([lat, lon], 15);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom:19,
      attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    L.marker([lat,lon]).addTo(map).bindPopup(nome);
    if (status) status.textContent='Marcador da loja exibido no mapa interativo.';
  } catch (err) {
    if (status) status.textContent='Não foi possível carregar o mapa. Consulte o endereço e os links de rota.';
  }
})();
