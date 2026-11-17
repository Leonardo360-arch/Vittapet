
/* Scroll-snap carousel controls; no autoplay, no external dependencies. */
(() => {
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  for (const slider of document.querySelectorAll('[data-showcase-slider]')) {
    const id = slider.id;
    const prev = document.querySelector('[data-prev="' + id + '"]');
    const next = document.querySelector('[data-next="' + id + '"]');
    const dots = document.querySelector('[data-dots="' + id + '"]');
    const slides = [...slider.children];
    if (!prev || !next || !dots || !slides.length) continue;
    const offset = i => slides[i].offsetLeft - slides[0].offsetLeft;
    const active = () => {
      let index = 0;
      for (let i = 1; i < slides.length; i++) {
        if (Math.abs(offset(i)-slider.scrollLeft) < Math.abs(offset(index)-slider.scrollLeft)) index=i;
      }
      return index;
    };
    const go = i => slider.scrollTo({left:offset(Math.max(0,Math.min(i,slides.length-1))),behavior:reduced.matches?'auto':'smooth'});
    slides.forEach((_,i) => {
      const b=document.createElement('button');
      b.type='button'; b.setAttribute('aria-label','Ir ao item '+(i+1));
      b.addEventListener('click',()=>go(i));
      dots.append(b);
    });
    const update=()=>{
      const max=Math.max(0,slider.scrollWidth-slider.clientWidth);
      prev.disabled=slider.scrollLeft<=2;
      next.disabled=slider.scrollLeft>=max-2;
      prev.hidden=next.hidden=dots.hidden=max<=2;
      [...dots.children].forEach((b,i)=>b.setAttribute('aria-current',String(i===active())));
    };
    prev.addEventListener('click',()=>go(active()-1));
    next.addEventListener('click',()=>go(active()+1));
    slider.addEventListener('scroll',update,{passive:true});
    window.addEventListener('resize',update);
    update();
  }
  const categoryFilter=document.getElementById('filtro');
  for(const link of document.querySelectorAll('[data-category-pick]')){
    link.addEventListener('click',()=>{
      if(!categoryFilter)return;
      categoryFilter.value=link.dataset.categoryPick;
      categoryFilter.dispatchEvent(new Event('change',{bubbles:true}));
    });
  }
})();

/* Header search shares the already-tested catalog filter; never alters server commerce logic. */
(() => {
  const form=document.querySelector('[data-store-search-form]');
  const headerInput=document.getElementById('store-header-search-input');
  const catalogInput=document.getElementById('busca-produtos');
  if(!form||!headerInput||!catalogInput)return;
  const params=new URLSearchParams(window.location.search);
  const initial=(params.get('q')||'').trim().slice(0,120);
  if(initial){
    headerInput.value=initial;
    catalogInput.value=initial;
    catalogInput.dispatchEvent(new Event('input',{bubbles:true}));
  }
  form.addEventListener('submit',event=>{
    event.preventDefault();
    const query=headerInput.value.trim();
    const filter=document.getElementById('filtro');
    if(filter){filter.value='';filter.dispatchEvent(new Event('change',{bubbles:true}));}
    catalogInput.value=query;
    catalogInput.dispatchEvent(new Event('input',{bubbles:true}));
    document.getElementById('produtos')?.scrollIntoView({behavior:window.matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth',block:'start'});
  });
})();
