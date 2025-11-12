.PHONY: help swagger-build swagger-watch docs-build

help: ## Muestra esta ayuda
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

swagger-build: ## Compila la documentación Swagger una vez
	@echo "📚 Compilando documentación Swagger..."
	@docker-compose restart swagger-builder
	@sleep 2
	@echo "✅ Documentación compilada"

swagger-watch: ## Observa cambios y recompila automáticamente
	@echo "👀 Observando cambios en la documentación..."
	@while true; do \
		inotifywait -e modify,create,delete -r ./docs/paths ./docs/openapi.source.yml 2>/dev/null && \
		make swagger-build; \
	done

docs-build: ## Compila la documentación localmente (requiere Node.js)
	@cd docs && chmod +x build.sh && ./build.sh

up: ## Levanta todos los contenedores
	@docker-compose up -d

down: ## Detiene todos los contenedores
	@docker-compose down

restart: ## Reinicia todos los contenedores
	@docker-compose restart

logs: ## Muestra logs de todos los contenedores
	@docker-compose logs -f

swagger-logs: ## Muestra logs del compilador Swagger
	@docker-compose logs -f swagger-builder