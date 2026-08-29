#!/usr/bin/env bash
set -e

# Pastikan direktori storage dan cache writable
mkdir -p /var/www/html/storage/framework/{sessions,views,cache} \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/whatsapp-sidecar/sessions \
         /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Auto start WhatsApp sidecar jika terpasang
if [ -d "/var/www/html/vendor/kstmostofa/laravel-whatsapp/sidecar/node_modules" ]; then
    if [ -f "/var/www/html/docker/whatsapp-sidecar/index.js" ]; then
        cp /var/www/html/docker/whatsapp-sidecar/index.js /var/www/html/vendor/kstmostofa/laravel-whatsapp/sidecar/index.js
    fi

    echo "Starting WhatsApp sidecar supervisor daemon..."
    (
        cd /var/www/html/vendor/kstmostofa/laravel-whatsapp/sidecar
        while true; do
            HOST=127.0.0.1 PORT=3000 TOKEN="${WHATSAPP_WEB_TOKEN:-siakad-nuja-secret-token}" PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium \
            node index.js >> /var/www/html/storage/logs/whatsapp-sidecar.log 2>> /var/www/html/storage/logs/whatsapp-sidecar.err.log || true
            sleep 2
        done
    ) &

    # Beri waktu 3 detik agar sidecar siap menerima koneksi
    sleep 3

    # Jalankan SSE Listener agar event pesan masuk langsung diteruskan ke Chatbot
    echo "Starting WhatsApp SSE Listener supervisor daemon..."
    (
        while true; do
            php /var/www/html/artisan whatsapp:web:listen main >> /var/www/html/storage/logs/wa-listener.log 2>&1 || true
            sleep 3
        done
    ) &

    # Jalankan background Queue Worker
    echo "Starting Queue Worker daemon..."
    (
        while true; do
            php /var/www/html/artisan queue:work --tries=3 --timeout=90 >> /var/www/html/storage/logs/queue-worker.log 2>&1 || true
            sleep 2
        done
    ) &
fi

exec "$@"
