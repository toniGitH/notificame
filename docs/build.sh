#!/bin/bash

# Script para compilar la documentación Swagger desde archivos separados
# a un único archivo openapi.yml

echo "📚 Compilando documentación Swagger..."

# Verificar si redocly está instalado
if ! command -v redocly &> /dev/null
then
    echo "❌ @redocly/cli no está instalado"
    echo "Instalando @redocly/cli..."
    npm install -g @redocly/cli
fi

# Directorio del script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Compilar desde openapi.source.yml a openapi.yml
redocly bundle "$DIR/openapi.source.yml" --output "$DIR/openapi.yml"

if [ $? -eq 0 ]; then
    echo "✅ Documentación compilada correctamente en openapi.yml"
else
    echo "❌ Error al compilar la documentación"
    exit 1
fi