# Makefile for Docker Management

.PHONY: help build up down restart logs shell clean test

# Default target
help:
	@echo "Available commands:"
	@echo "  make build     - Build Docker images"
	@echo "  make up        - Start containers (development mode with hot reload)"
	@echo "  make down      - Stop containers"
	@echo "  make restart   - Restart containers (only needed after config changes)"
	@echo "  make logs      - Show container logs"
	@echo "  make shell     - Open shell in container"
	@echo "  make clean     - Remove containers and volumes"
	@echo "  make test      - Run health check"
	@echo "  make prod      - Start production environment"
	@echo ""
	@echo "🔥 Development Tips:"
	@echo "  - Run 'make up' once to start containers"
	@echo "  - Edit code and refresh browser - changes appear immediately!"
	@echo "  - Only use 'make restart' if you change Dockerfile or php.ini"
	@echo "  - Use 'make logs' to watch real-time logs"

# Build Docker images
build:
	@echo "Building Docker images..."
	docker-compose build

# Start containers in detached mode
up:
	@echo "🚀 Starting containers in development mode..."
	@echo "📝 Hot reload is ENABLED - edit code and refresh browser!"
	docker-compose up -d
	@echo "✅ Application is running at http://localhost:8080"
	@echo "✅ phpMyAdmin is running at http://localhost:8081"
	@echo ""
	@echo "💡 Tip: Run 'make logs' to watch real-time logs"
	@echo "💡 Edit files and refresh - no restart needed!"

# Stop containers
down:
	@echo "Stopping containers..."
	docker-compose down

# Restart containers
restart: down
	@echo "🔄 Rebuilding and restarting containers..."
	@echo "⚠️  This is only needed after Dockerfile or config changes!"
	docker-compose up -d --build
	@echo "✅ Containers restarted with new configuration"

# Show logs
logs:
	@echo "📋 Showing real-time logs (Ctrl+C to exit)..."
	docker-compose logs -f web

# Show logs for all services
logs-all:
	@echo "📋 Showing all service logs (Ctrl+C to exit)..."
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
ps:
	@echo "📊 Container Status:"
	docker-compose ps

status: ps

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
