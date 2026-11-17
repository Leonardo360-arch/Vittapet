# QA — Revisão 03: Geolocalização e mapa (17/11/2026)

## Resultado da revisão
**Revisão de código estática, NÃO é aprovação de execução/hospedagem.** O ambiente de execução do GitHub não foi utilizado e não há acesso ao banco configurado nem à conta InfinityFree. Sintaxe PHP, CORS do Nominatim, renderização Leaflet, permissões de diretórios e instalação precisam ser testados em runtime.

## Arquivos envolvidos
- `database/migrations/004_localizacao_loja.sql`: configuração única da loja, com endereço e coordenadas.
- `public/admin/configuracoes.php`: edição protegida por login e CSRF, consulta manual à API Nominatim (JSON), validação de lat/lon e persistência via PDO.
- `public/index.php`: seção 'Onde estamos' e links Google Maps/Waze a partir das coordenadas reais.
- `public/assets/js/mapa.js`: Leaflet com tiles OpenStreetMap, marcador e atribuição.
- `public/assets/css/mapa.css`: mapa responsivo e layout.
- `docs/INSTALACAO.md`: ordem de migrations e configuração.

## Observações técnicas
1. **API 1 — ViaCEP:** já existe no checkout; precisa de teste em navegador e captura JSON.
2. **API 2 — Nominatim:** executa somente mediante clique explícito do administrador, recebe JSON e preenche latitude/longitude; dados são persistidos e o mapa público não geocodifica em cada visita. Proibido usar como autocomplete.
3. Política pública Nominatim: máximo absoluto de uma requisição por segundo por aplicação, identificação por Referer ou User-Agent e cache. O controle de 1,2s no navegador é local e **não garante limitação global**; o cenário previsto (configuração esporádica pelo administrador) deve ser mantido. Não reutilizar a API para geocodificar endereços de clientes em massa. Ver https://operations.osmfoundation.org/policies/nominatim/.
4. Tiles OSM: manter atribuição, observar política de uso e cache do navegador. Ver https://operations.osmfoundation.org/policies/tiles/.
5. Sem endereço/coordenadas, mostrar texto neutro em vez de marcador fictício. O endereço exibido deve ser confirmado pelo responsável pela loja.
6. **InfinityFree:** como esta API é chamada pelo navegador, validar CORS/Referer e bloqueios da hospedagem no domínio publicado; o mapa depende de CDN externa e rede do visitante.

## Testes obrigatórios para aceite
- [ ] Importar migration 004 depois de 001/002/003 em MySQL
- [ ] Login de admin necessário para acessar `/admin/configuracoes.php`
- [ ] Alterar endereço e salvar; dados persistem após recarregar
- [ ] Consultar endereço válido; receber JSON com lat/lon e confirmar coordenadas
- [ ] Endereço não encontrado / API indisponível apresenta mensagem, sem quebrar tela
- [ ] Mapear coordenadas persistidas na homepage sem nova chamada Nominatim
- [ ] Confirmar título, marcador, atribuição e redirecionamento de rotas
- [ ] Sem endereço configurado, nenhum mapa/rota falsa aparece
- [ ] Verificar dispositivo móvel e política de tiles
- [ ] Testar HTTPS, CSS/JS/CDN e `htdocs/.htaccess` no InfinityFree
- [ ] Registrar prints e respostas JSON das duas APIs para avaliação DW2

**Parecer:** código de localização preparado e auditado estaticamente; liberação para teste manual. Não declarar 'API aprovada' nem 'hospedagem concluída' até evidências de execução.
