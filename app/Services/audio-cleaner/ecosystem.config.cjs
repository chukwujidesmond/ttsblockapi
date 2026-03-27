module.exports = {
  apps: [{
    name: 'audio-cleaner',
    cmd: 'venv/bin/uvicorn',
    args: 'app.main:app --host 127.0.0.1 --port 8002 --workers 2',
    cwd: '/var/www/api/app/Services/audio-cleaner',
    interpreter: 'none',
    env: {
      API_SECRET: 'your-secret-here',
    },
    // restart settings
    restart_delay: 5000,
    max_restarts: 10,
    autorestart: true,
  }]
}