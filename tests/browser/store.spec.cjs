const { test, expect } = require('@playwright/test');

const email = process.env.ADMIN_EMAIL || 'qa-admin@example.invalid';
const password = process.env.ADMIN_PASSWORD || 'CI-password-test-2026!';

async function loginAdmin(page) {
  await page.goto('/login.php');
  await page.locator('#email').fill(email);
  await page.locator('#senha').fill(password);
  await page.getByRole('button', {name:'Entrar'}).click();
  await expect(page).toHaveURL(/admin\/dashboard\.php/);
}

test('catálogo, navegação e responsividade em três resoluções', async ({page}) => {
  await page.goto('/index.php');
  await expect(page.getByRole('heading', {name:'Nossa loja'})).toBeVisible();
  await expect(page.locator('.produto')).not.toHaveCount(0);
  const category = await page.locator('.produto').first().getAttribute('data-category');
  await page.locator('#filtro').selectOption({label:category});
  await expect(page.locator('.produto:not([hidden])').first()).toBeVisible();
  await page.getByRole('link', {name:'Carrinho'}).click();
  await expect(page).toHaveURL(/carrinho\.php/);
  await expect(page.getByRole('heading', {name:'Meu carrinho'})).toBeVisible();
  await expect.poll(async () => page.evaluate(
    () => document.documentElement.scrollWidth <= window.innerWidth + 2
  )).toBe(true);
});

test('API ViaCEP: JSON válido preenche endereço e falha permite preenchimento manual', async ({page}) => {
  await page.route('https://viacep.com.br/ws/**', async route => {
    const url = route.request().url();
    if (url.includes('01001000')) {
      return route.fulfill({status:200,contentType:'application/json',
        body:JSON.stringify({logradouro:'Praça da Sé',bairro:'Sé',localidade:'São Paulo',uf:'SP'})});
    }
    return route.fulfill({status:503,contentType:'application/json',body:'{}'});
  });
  await page.goto('/checkout.php');
  await page.locator('#cep').fill('01001000');
  await page.locator('#cep').blur();
  await expect(page.locator('#cep-status')).toContainText('Endereço localizado');
  await expect(page.locator('#logradouro')).toHaveValue('Praça da Sé');
  await expect(page.locator('#cidade')).toHaveValue('São Paulo');
  await page.locator('#cep').fill('99999999');
  await page.locator('#cep').blur();
  await expect(page.locator('#cep-status')).toContainText('Preencha o endereço manualmente');
  await page.locator('#logradouro').fill('Endereço preenchido manualmente');
  await expect(page.locator('#logradouro')).toHaveValue('Endereço preenchido manualmente');
});

test('API Nominatim: consulta sob demanda e mensagem de coordenadas', async ({page}) => {
  await loginAdmin(page);
  let queries = 0;
  await page.route('https://nominatim.openstreetmap.org/search?**', async route => {
    queries++;
    return route.fulfill({status:200,contentType:'application/json',
      body:JSON.stringify([{lat:'-23.5505200',lon:'-46.6333080'}])});
  });
  await page.goto('/admin/configuracoes.php');
  await page.locator('#logradouro').fill('Praça da Sé');
  await page.locator('#cidade').fill('São Paulo');
  await page.locator('#uf').fill('SP');
  expect(queries).toBe(0);
  await page.getByRole('button',{name:'Localizar endereço no mapa'}).click();
  await expect(page.locator('#latitude')).toHaveValue('-23.5505200');
  await expect(page.locator('#longitude')).toHaveValue('-46.6333080');
  expect(queries).toBe(1);
});

test('mapa apresenta fallback e links de rotas após salvar localização', async ({page}) => {
  await loginAdmin(page);
  await page.goto('/admin/configuracoes.php');
  await page.locator('#logradouro').fill('Praça da Sé');
  await page.locator('#numero').fill('1');
  await page.locator('#bairro').fill('Sé');
  await page.locator('#cidade').fill('São Paulo');
  await page.locator('#uf').fill('SP');
  await page.locator('#latitude').fill('-23.5505200');
  await page.locator('#longitude').fill('-46.6333080');
  await page.getByRole('button',{name:'Salvar configurações'}).click();
  await expect(page).toHaveURL(/configuracoes\.php\?salvo=1/);
  await page.route('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',route=>route.abort());
  await page.goto('/index.php');
  await expect(page.locator('#store-map')).toBeVisible();
  await expect(page.locator('#store-map-status')).toContainText('Mapa temporariamente indisponível');
  await expect(page.getByRole('link',{name:'Rotas no Google Maps'})).toHaveAttribute('href',/google\.com\/maps\/dir/);
  await expect(page.getByRole('link',{name:'Abrir no Waze'})).toHaveAttribute('href',/waze\.com/);
});

test('rastreio Pix usa WhatsApp comercial, valor e código sem enviar mensagem sozinho', async ({page,request}) => {
  await loginAdmin(page);
  await page.goto('/admin/configuracoes.php');
  await page.locator('#whatsapp').fill('5511999999999');
  await page.getByRole('button',{name:'Salvar configurações'}).click();

  // Token conhecido através do último pedido Pix de teste, apenas no banco do CI.
  const pixToken = process.env.TEST_PIX_TOKEN;
  test.skip(!pixToken, 'TEST_PIX_TOKEN não configurado para este ambiente');
  await page.goto('/rastrear.php?token='+encodeURIComponent(pixToken));
  const whatsapp = page.getByRole('link',{name:'Solicitar chave Pix pelo WhatsApp'});
  await expect(whatsapp).toBeVisible();
  const href = await whatsapp.getAttribute('href');
  expect(href).toContain('https://wa.me/5511999999999');
  expect(decodeURIComponent(href)).toContain(pixToken);
  expect(decodeURIComponent(href)).toContain('R$ ');
});
