# QA Revisão 10 — Redesenho público e catálogo DEMO

**Versão:** 1.0.0-dev.9  
**Data:** 17/11/2026  
**Ambiente:** localhost de desenvolvimento; não publicar como release estável.

## Alterações realizadas
1. Vitrine pública redesenhada em `public/index.php` e `public/assets/css/storefront.css`: marca com símbolo próprio, navegação organizada, botões de ação visíveis, hero, benefícios, cards de produtos, busca, filtros e rodapé.
2. Link administrativo removido do header, substituído por **Área administrativa** no canto inferior direito do rodapé.
3. Filtragem em `public/assets/js/storefront.js` funciona por categoria e por texto, incluindo estado vazio e contador acessível.
4. Sem imagem cadastrada, produto usa ilustração local em SVG, não uma imagem de catálogo falsa; produtos DEMO recebem etiqueta visível.
5. `scripts/seed_local.php` adiciona **até 20 produtos fictícios**, com preços/estoque variados, em quatro categorias, sem sobrescrever itens existentes. Reexecutar não duplica os itens, inclusive aqueles desativados.
6. Dados DEMO só podem ser criados explicitamente por CLI em `APP_ENV=local`; o script não faz upload para a loja real.
7. Endereço **não alterado**: o responsável ainda enviará o endereço fictício solicitado. Nunca sobrescrever endereço já configurado sem confirmação.

## Aceitação automatizada
- [ ] PHP lint de `public/index.php` e `scripts/seed_local.php`.
- [ ] Testes de integração existentes no MySQL (78 asserções) sem regressões.
- [ ] Concorrência de estoque permanece correta.
- [ ] Playwright valida cabeçalho, ausência de link Admin no topo, link Área administrativa no rodapé, busca, filtro e botão em três viewports.
- [ ] Smoke test Docker importa as migrations e inicializa catálogo DEMO.
- [ ] A segunda execução do seed não duplica itens nem altera os existentes.
- [ ] Revisão no navegador real Windows: fotografias ilustrativas locais, largura dos botões, alinhamento do rodapé e responsividade.

## Operação do catálogo DEMO
Executar na pasta do repositório:
```powershell
docker compose -f compose.local.yaml exec -T app php scripts/seed_local.php
```
O script **não apaga nenhum produto**. Para retirar um item, usar `/admin/produtos.php` e a opção de desativação/Exclusão do painel. A desativação preserva dados históricos.

## Limitações
- Imagens atuais são ilustrações SVG genéricas, não fotos reais dos produtos; imagens reais exigem funcionalidade segura de cadastro/upload para lançamento.
- A vitrine continua limitada aos 60 produtos mais recentes; paginação é uma melhoria futura se houver catálogos maiores.
- O novo redesign concentra-se na `index.php`; telas de carrinho, checkout e todo o admin precisam de avaliação visual separada.
- ViaCEP, Nominatim, mapa com tiles, WhatsApp em aparelho real, segurança manual e InfinityFree ainda não estão homologados.
