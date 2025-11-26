# Makefile for Docker Management

.PHONY: help build up down restart logs shell clean test

# Default target
help:
	@echo "Available commands:"
	@echo "  make build     - Build Docker images"
	@echo "  make up        - Start containers"
	@echo "  make down      - Stop containers"
	@echo "  make restart   - Restart containers"
	@echo "  make logs      - Show container logs"
	@echo "  make shell     - Open shell in container"
	@echo "  make clean     - Remove containers and volumes"
	@echo "  make test      - Run health check"
	@echo "  make prod      - Start production environment"

# Build Docker images
build:
	@echo "Building Docker images..."
	docker-compose build

# Start containers in detached mode
up:
	@echo "Starting containers..."
	docker-compose up -d
	@echo "Application is running at http://localhost:8080"

# Stop containers
down:
	@echo "Stopping containers..."
	docker-compose down

# Restart containers
restart: down up

# Show logs
logs:
	docker-compose logs -f

# Open shell in web container
shell:
	docker-compose exec web bash

# Clean everything (containers, volumes, cache)
clean:
	@echo "Cleaning up..."
	docker-compose down -v
	rm -rf data/*.db
	rm -rf cache/*.cache
	@echo "Cleanup complete!"

# Health check
test:
	@echo "Running health check..."
	@curl -f http://localhost:8080/index.php || echo "Health check failed!"

# Production environment
prod:
	@echo "Starting production environment..."
	docker-compose -f docker-compose.prod.yml up -d
	@echo "Production environment is running!"

# View container status
status:
	docker-compose ps

# View real-time resource usage
stats:
	docker stats nutrition-app

# Backup database
backup:
	@echo "Creating backup..."
	@mkdir -p backups
	@cp data/nutrition.db backups/nutrition-$(shell date +%Y%m%d-%H%M%S).db
	@echo "Backup created in backups/ directory"

# Restore database from latest backup
restore:
	@echo "Restoring from latest backup..."
	@cp $(shell ls -t backups/*.db | head -1) data/nutrition.db
	@echo "Database restored!"
