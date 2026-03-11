#!/bin/bash

cd /var/www/your-laravel-app/services/audio-cleaner

source venv/bin/activate

uvicorn app.main:app \
    --host 127.0.0.1 \       # localhost only, Laravel talks to it internally
    --port 8002 \
    --workers 2