# Design system VittaPet Store v1.0

## Identidade
Loja contemporânea, limpa, confiável e amigável. Priorizar facilidade de compra e clareza na gestão.

## Tokens de interface
| Token | Valor | Uso |
|---|---|---|
| color-primary | #0F766E | Marca, links e ações primárias |
| color-secondary | #2DD4BF | Elementos auxiliares, com texto contrastante |
| color-accent | #F97316 | Chamadas comerciais e destaque |
| color-background | #FFF7ED | Fundo de seções suaves |
| color-text | #1F2937 | Texto |
| color-surface | #FFFFFF | Cards, modais e inputs |
| color-border | #E5E7EB | Separadores |

Poppins 600/700 nos títulos; Inter 400/500 em texto, preço, tabelas e inputs. Tamanhos-base propostos: corpo 16px; textos auxiliares 14px; heading H1 fluido 32–48px; heading H2 24–32px. Espaços de 4/8/12/16/24/32px. Radius 12px inputs/botões, 16px cards. Ícones SVG/Lucide de traço fino e tamanho 18–22px. Não usar caracteres de texto ou emojis como ícones.

## Componentes
Header com pesquisa/carrinho (quantidade atualizada), menu de categorias, cards de produto com foto, nome, preço e botão enxuto; carrinho com controle de quantidade compacto, remover e resumo de valores; checkout em etapas claras; status de entrega em linha do tempo; dashboard com cards de KPIs, filtros e gráficos; tabela admin com ações discretas; mapa compacto no rodapé.

## Responsividade e acessibilidade
Mobile-first, colunas fluidas, menus utilizáveis com toque e teclado, foco visível, contrastes WCAG 2.1 AA verificados, labels em inputs, feedback de erros legível, estados de loading e conteúdo alternativo para imagens. Nunca usar apenas cor para comunicar um estado.

## Organização dos estilos
```text
public/assets/css/
  tokens.css
  base.css
  layout.css
  components.css
  pages/home.css
  pages/catalogo.css
  pages/carrinho.css
  pages/checkout.css
  admin/dashboard.css
  admin/produtos.css
  admin/financeiro.css
```
Componentes devem consumir tokens CSS custom properties. Bootstrap é base de grid/utilitários, não substitui identidade original. Evitar CSS inline arbitrário.


## Implementação incremental dev.8
O design system original é uma especificação-alvo; nem todos os arquivos planejados existem. Nesta etapa foram implementados `public/assets/css/admin.css` e `includes/admin_header.php`, utilizados inicialmente no dashboard. A navegação das demais páginas continua pendente de migração para o componente compartilhado. As barras são HTML/CSS, com valores vindos da consulta por `pago_em`, sem biblioteca gráfica ou números artificiais. Os estados vazios mostram instruções reais, e o menu usa `aria-current`. A responsividade em viewport emulado não substitui inspeção visual em aparelho físico.
