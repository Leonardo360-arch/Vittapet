# VittaPet — Pausa de desenvolvimento e checklist de homologação visual

## Escopo desta entrega (PR #18)
- Cabeçalho comercial verde com pesquisa e carrinho destacado.
- Menu horizontal de categorias, gerado com categorias ativas do banco.
- Banner principal com destaque laranja.
- Carrosséis de categorias e produtos da consulta existente, com controles e indicadores.
- Ajustes em cards de produtos, botões, hover e preferências de movimento reduzido.
- Preservação do rodapé com Área administrativa à direita.
- Sem mudanças no backend de pedidos, carrinho, pagamentos, estoque ou dados da loja.

## Para testar no Windows
```powershell
git status
git fetch origin
git switch feat/storefront-interactions-dev10
git pull --ff-only origin feat/storefront-interactions-dev10
docker compose -f compose.local.yaml up -d --build
```
Se houver mudanças locais, preservá-las antes da troca de branch.
Abrir http://localhost:8080/ e recarregar com Ctrl+F5.
Para popular dados DEMO localmente, se necessário:
```powershell
docker compose -f compose.local.yaml exec -T app php scripts/seed_local.php
```

## QA manual / critérios de aceitação
- [ ] Desktop 1366px: logo, busca, links e carrinho sem sobreposição.
- [ ] Celular 390px: menu, busca, botões e cards legíveis; sem rolagem lateral da página.
- [ ] Tablet 768px: reorganização do header funcional.
- [ ] Busca superior filtra produtos do catálogo, inclusive buscas vazias e termos ausentes.
- [ ] Menu e carrossel de categorias filtram a grade corretamente.
- [ ] Carrossel de destaques: botões, indicadores, toque e teclado funcionais.
- [ ] Produto esgotado permanece desabilitado.
- [ ] Adicionar ao carrinho, checkout, rastreamento e mapa preservados.
- [ ] Preferência por movimento reduzido respeitada.
- [ ] Acesso Área administrativa permanece somente no rodapé da vitrine.
- [ ] Conferir efeitos, espaçamentos e identidade visual com a imagem de referência.
- [ ] Rodar os workflows de CI, verificar resultados completos e registrar capturas antes de merge.

## Decisões e pendências
- Não inserir endereço residencial em documentação ou repositório.
- Não copiar imagens de produtos de terceiros sem licença; hero atual é ilustração SVG, diferente da fotografia da referência.
- Não prometer serviço de conta do cliente, parcelamento ou entrega nacional sem implementação.
- Não mesclar até a validação visual e funcional no localhost.
