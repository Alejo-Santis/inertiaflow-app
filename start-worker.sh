#!/bin/bash

# ============================================
# Worker de Queue - NextPyme Certificados
# Versión: Desarrollo Local
# ============================================

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para manejo limpio de señales
cleanup() {
    echo -e "\n${YELLOW}🛑 Deteniendo worker...${NC}"
    exit 0
}

# Capturar Ctrl+C
trap cleanup SIGINT SIGTERM

# Banner
echo -e "${BLUE}============================================${NC}"
echo -e "${GREEN}🚀 Worker de Trabajos en Cola${NC}"
echo -e "${BLUE}============================================${NC}"
echo -e "${YELLOW}Entorno: DESARROLLO${NC}"
echo -e "${YELLOW}Presiona Ctrl+C para detener${NC}"
echo -e "${BLUE}============================================${NC}\n"

# Verificar que artisan existe
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: No se encuentra el archivo 'artisan'${NC}"
    echo -e "${RED}   Asegúrate de ejecutar este script desde la raíz del proyecto${NC}"
    exit 1
fi

# Contador de reinicios
RESTART_COUNT=0

# Loop principal
while true; do
    RESTART_COUNT=$((RESTART_COUNT + 1))

    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')] 🔄 Iniciando worker (reinicio #${RESTART_COUNT})...${NC}"

    # Ejecutar worker con parámetros optimizados para desarrollo
    php artisan queue:work \
        --sleep=3 \
        --tries=3 \
        --max-time=3600 \
        --timeout=120 \
        --verbose

    # Capturar código de salida
    EXIT_CODE=$?

    if [ $EXIT_CODE -ne 0 ]; then
        echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ⚠️  Worker detenido con código de error: ${EXIT_CODE}${NC}"
    else
        echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] ℹ️  Worker detenido normalmente${NC}"
    fi

    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')] ⏳ Reiniciando en 3 segundos...${NC}\n"
    sleep 3
done
