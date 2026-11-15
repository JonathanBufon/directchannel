// mobile/src/constants/list.ts

/**
 * Parâmetros de virtualização
 * Evita o carregamento massivo de itens fora da janela visível.
 */
export const listVirtualizationConfig = {
  initialNumToRender: 8,       // Itens renderizados na primeira pintura
  maxToRenderPerBatch: 6,       // Quantidade máxima renderizada por lote ao rolar
  windowSize: 7,                // Tamanho da janela de visualização montada em memória
  updateCellsBatchingPeriod: 50,// Intervalo em ms entre renderizações em lote
  removeClippedSubviews: true,  // Descarta do pipeline nativo views fora da tela
} as const;