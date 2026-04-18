/**
 * Calcula el Dígito de Verificación (DV) de un NIT colombiano.
 * Algoritmo oficial DIAN — Resolución 8558.
 *
 * Factores primos de posición (de derecha a izquierda):
 *   3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71
 *
 * Regla final:
 *   sum % 11 === 0  → DV = 0
 *   sum % 11 === 1  → DV = 1
 *   de lo contrario → DV = 11 − (sum % 11)
 */

const WEIGHTS = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];

export function calcDV(nit: string): string {
  const digits = nit.replace(/\D/g, '');
  if (digits.length === 0) return '';

  let sum = 0;
  for (let i = 0; i < digits.length && i < WEIGHTS.length; i++) {
    sum += parseInt(digits[digits.length - 1 - i]) * WEIGHTS[i];
  }

  const mod = sum % 11;
  if (mod === 0) return '0';
  if (mod === 1) return '1';
  return String(11 - mod);
}
