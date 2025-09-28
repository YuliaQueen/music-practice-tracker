#!/bin/bash

# Music Practice Tracker - Docker Stop Script

echo "🛑 Stopping Music Practice Tracker services..."

# Stop all services
docker-compose down

echo "✅ All services stopped!"
echo ""
echo "To remove all data (including MinIO data):"
echo "  docker-compose down -v"
echo ""
echo "To start again:"
echo "  ./docker-start.sh"