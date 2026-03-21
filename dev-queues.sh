#!/usr/bin/env bash
# =============================================================================
# InertiaFlow — Cola de trabajos para desarrollo
# Uso: ./dev-queues.sh
# =============================================================================

set -e

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
RED='\033[0;31m'
NC='\033[0m'

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo ""
echo -e "${CYAN}╔══════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║       InertiaFlow — Queue Worker         ║${NC}"
echo -e "${CYAN}╚══════════════════════════════════════════╝${NC}"
echo ""

# Verificar que estemos en el directorio correcto
if [ ! -f "$ROOT/artisan" ]; then
    echo -e "${RED}Error: no se encontró artisan en $ROOT${NC}"
    exit 1
fi

# Verificar conexión a la base de datos
echo -e "${YELLOW}▶ Verificando conexión a la base de datos...${NC}"
php "$ROOT/artisan" db:show --no-interaction > /dev/null 2>&1 \
    && echo -e "${GREEN}  ✓ Base de datos OK${NC}" \
    || { echo -e "${RED}  ✗ No se pudo conectar a la base de datos. ¿Está corriendo PostgreSQL?${NC}"; exit 1; }

# Limpiar trabajos fallidos viejos (opcional en dev)
echo -e "${YELLOW}▶ Limpiando jobs fallidos anteriores...${NC}"
php "$ROOT/artisan" queue:flush --no-interaction 2>/dev/null || true
echo -e "${GREEN}  ✓ Listo${NC}"

echo ""
echo -e "${GREEN}▶ Iniciando queue:work con driver: database${NC}"
echo -e "  Cola:     default"
echo -e "  Timeout:  90s por job"
echo -e "  Sleep:    3s entre ciclos"
echo -e "  Tries:    3 intentos por job"
echo -e "  Log:      stderr"
echo ""
echo -e "${YELLOW}  Presiona Ctrl+C para detener${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

php "$ROOT/artisan" queue:work \
    --queue=default \
    --timeout=90 \
    --sleep=3 \
    --tries=3 \
    --max-time=3600 \
    --verbose
