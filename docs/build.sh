#!/bin/bash

# Script para compilar la documentación Swagger desde archivos separados
# a un único archivo openapi.yml

echo "📚 Compilando documentación Swagger..."

# Verificar si swagger-cli está instalado
if ! command -v swagger-cli &> /dev/null
then
    echo "❌ swagger-cli no está instalado"
    echo "Instalando swagger-cli..."
    npm install -g @apidevtools/swagger-cli
fi

# Directorio del script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Compilar desde openapi.source.yml a openapi.yml
swagger-cli bundle "$DIR/openapi.source.yml" --outfile "$DIR/openapi.yml" --type yaml

if [ $? -eq 0 ]; then
    echo "✅ Documentación compilada correctamente en openapi.yml"
else
    echo "❌ Error al compilar la documentación"
    exit 1
fi