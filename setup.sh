#!/bin/bash
# Setup script for CookAI

echo "CookAI Installation Setup"
echo "========================="

# Check PHP version
echo "Checking PHP version..."
php -v

# Check if Composer is installed
echo "Checking Composer..."
if ! command -v composer &> /dev/null; then
    echo "Composer not found. Please install Composer first."
    exit 1
fi

# Copy env file
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example"
    cp .env.example .env
    echo "Please edit .env file with your configuration"
fi

# Install dependencies
echo "Installing PHP dependencies..."
composer install

# Create upload directories
echo "Creating upload directories..."
mkdir -p public/uploads/recipes
mkdir -p public/uploads/avatars
mkdir -p public/uploads/communities
mkdir -p temp
mkdir -p logs

echo ""
echo "✓ Setup complete!"
echo ""
echo "Next steps:"
echo "1. Edit .env file with database credentials"
echo "2. Import database schema: mysql -u root -p < database/schema.sql"
echo "3. Start server: composer start"
echo "4. Visit: http://localhost:8000"
