#!/bin/bash

# Music Practice Tracker - Docker Setup Script

echo "🎵 Starting Music Practice Tracker with MinIO..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker first."
    exit 1
fi

# Copy environment file for Docker
if [ ! -f .env ]; then
    echo "📋 Creating .env file from .env.docker..."
    cp .env.docker .env
fi

# Generate application key if not exists
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Start MinIO services
echo "🚀 Starting MinIO services..."
docker-compose up -d minio

# Wait for MinIO to be ready
echo "⏳ Waiting for MinIO to be ready..."
sleep 10

# Create bucket and set permissions
echo "📦 Setting up MinIO bucket..."
docker-compose up minio-setup

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Install Node dependencies
echo "📦 Installing Node dependencies..."
npm install

# Build assets
echo "🏗️ Building frontend assets..."
npm run build

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

echo "✅ Setup complete!"
echo ""
echo "🌐 MinIO Console: http://localhost:9001"
echo "   Username: minioadmin"
echo "   Password: minioadmin123"
echo ""
echo "🎵 Application: http://localhost:8000"
echo ""
echo "To start the application server:"
echo "  php artisan serve --host=0.0.0.0 --port=8000"