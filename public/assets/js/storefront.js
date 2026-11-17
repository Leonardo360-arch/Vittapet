/* Busca e filtro da vitrine. Nenhuma chamada externa; exibe somente itens renderizados pelo PHP. */
(() => {
  const search = document.getElementById('busca-produtos');
  const filter = document.getElementById('filtro');
  const cards = [...document.querySelectorAll('.produto')];
  const counter = document.getElementById('catalog-count');
  const empty = document.getElementById('catalog-empty');
  if (!search || !filter || !counter || !empty) return;

  const normalize = (value) => String(value || '').toLocaleLowerCase('pt-BR').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  const update = () => {
    const term = normalize(search.value.trim());
    let count = 0;
    for (const card of cards) {
      const categoryMatches = !filter.value || card.dataset.category === filter.value;
      const textMatches = !term || normalize(card.dataset.search).includes(term);
      card.hidden = !(categoryMatches && textMatches);
      if (!card.hidden) count++;
    }
    counter.textContent = count === 1 ? '1 produto encontrado' : count + ' produtos encontrados';
    empty.hidden = count !== 0 || cards.length === 0;
  };
  search.addEventListener('input', update);
  filter.addEventListener('change', update);
  update();
})();
