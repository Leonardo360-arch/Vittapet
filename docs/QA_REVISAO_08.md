# QA Revisão 08 — Homologação visual, APIs e dispositivos

**Versão planejada:** 1.0.0-dev.5  
**Data:** 17/11/2026  
**Ambiente:** GitHub Actions, Chromium automatizado, MySQL temporário, PHP 8.2.  
**Critério de transparência:** teste com API simulada **não confirma disponibilidade do serviço externo real**.

## Evidência da execução automática
**GitHub Actions:** [execução 44110702002](https://github.com/Leonardo360-arch/Vittapet/actions/runs/44110702002), commit `fc0169cd1195cb091c8bed601a028a197f297aca`.

- Job `php-lint`: **aprovado** com PHP 8.2 em Ubuntu 24.04.
- Job `mysql-http`: **aprovado**; regressão com **78 asserções HTTP/MySQL**.
- Concorrência InnoDB: **aprovada**; 1 venda, 1 rejeição, estoque zero.
- Playwright Chromium: **15 de 15 testes aprovados**, 5 cenários repetidos em `chromium-mobile`, `chromium-tablet` e `chromium-desktop`, em 18,8 segundos.
- Mocks JSON para ViaCEP e Nominatim: interface e tratamento de erro **aprovados em simulação**, sem certificado de disponibilidade/CORS real.
- Mapa: fallback na indisponibilidade de Leaflet e links de rota **aprovados**; mapa real com tiles **não testado**.
- Link WhatsApp: destinatário, pedido e preço conferidos com número e token **fictícios**; aplicativo WhatsApp real **não testado**.
- A primeira execução falhou na instalação do Chromium porque o runner Ubuntu 26.04 ainda não era suportado pelo Playwright 1.56.1; fixado runner `ubuntu-24.04` e repetido o CI com êxito.

**Decisão QA:** portão de navegador **automatizado aprovado**; etapa 3 **parcialmente homologada**, aguardando os casos externos e físicos abaixo.

## Cobertura implementada
| Área | Método | Resultado exigido |
|---|---|---|
| Vitrine | Browser Chromium | Catálogo carregado e filtro por categoria |
| Navegação | Browser Chromium | Link de carrinho funcional |
| Responsividade | Chromium 390px/768px/1366px | Sem rolagem horizontal inesperada |
| ViaCEP | Interceptação JSON estável | CEP válido preenche endereço; falha permite preenchimento manual |
| Nominatim | Interceptação JSON estável | Só consulta após clique; coordenadas recebidas e exibidas |
| Localização | Browser Chromium + CDN bloqueada | Endereço, alternativas Google Maps/Waze e texto de fallback disponíveis |
| WhatsApp Pix | Browser Chromium, pedido fictício de QA | Link para número configurado, com token e preço, sem envio automático |
| Administração | Navegador real Chromium | Login, formulário de localização e gravação persistente no MySQL |
| Regressão | PHP/MySQL | 78 asserções e teste de concorrência da dev.4 mantidos |

Os mesmos cenários são executados em três projetos de dispositivo emulado pelo Playwright. Emulação de viewport/browser **não equivale a teste físico no WhatsApp ou no Chrome Android real**. Dados de cliente e WhatsApp são exclusivamente fictícios em banco descartável.

## Testes manuais ainda obrigatórios
- [ ] ViaCEP real em conexão de navegador, verificando HTTP/JSON/CORS e fallback por indisponibilidade.
- [ ] Nominatim real com endereço autorizado da loja, conferindo retorno, política de uso e coordenadas.
- [ ] Testar URL wa.me em Android real com WhatsApp instalado, destinatário comercial correto e confirmação consciente do envio.
- [ ] Verificar Leaflet com CDN disponível e mapa com tile carregado e atribuição OSM, além do fallback automatizado.
- [ ] Verificar telas e teclado em pelo menos um celular físico Android e um desktop, sem dados sensíveis.
- [ ] Verificar contraste, foco visível, rótulos e navegação por teclado, com registro de falhas.
- [ ] Conferir navegadores Firefox/Chromium ou Safari conforme disponibilidade e gravar evidências.
- [ ] Testar a tela de pedidos com lista extensa e mensagens de erro específicas.

## Critério de encerramento
Não marcar etapa 3 como integralmente aprovada enquanto existirem testes externos e físicos não realizados. O CI automatizado gera evidências reproduzíveis, mas não substitui validação final humana.

## Infraestrutura
InfinityFree está explicitamente reservado para a última etapa, após revisão de documentação e fechamento de bloqueadores.
