#!/bin/bash
echo "Starting Docker services..."
docker compose up -d

echo "Waiting for Laravel to be ready..."
sleep 5

echo "Starting Frontend Dev Server..."
npm run dev
