#!/usr/bin/env bash
set -e

# Pastikan direktori storage dan cache writable
mkdir -p /var/www/html/storage/framework/{sessions,views,cache} \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/whatsapp-sidecar/sessions \
         /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Auto start WhatsApp sidecar jika terpasang
if [ -f "/var/www/html/vendor/kstmostofa/laravel-whatsapp/sidecar/index.js" ] && [ -d "/var/www/html/vendor/kstmostofa/laravel-whatsapp/sidecar/node_modules" ]; then
    echo "Starting WhatsApp sidecar daemon..."
    cd /var/www/html/vendor/kstmostofa/laravel-whatsapp/sidecar
    HOST=127.0.0.1 PORT=3000 TOKEN="${WHATSAPP_WEB_TOKEN:-siakad-nuja-secret-token}" PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium \
    node index.js >> /var/www/html/storage/logs/whatsapp-sidecar.log 2>> /var/www/html/storage/logs/whatsapp-sidecar.err.log &
    SIDECAR_PID=$!
    echo "$SIDECAR_PID" > /var/www/html/storage/app/whatsapp-sidecar/sidecar.pid 2>/dev/null || true
    cd /var/www/html
    
    # Beri waktu 2 detik agar sidecar siap menerima koneksi SSE
    sleep 2
    
    # Jalankan SSE Listener agar event pesan masuk langsung diteruskan ke Chatbot
    echo "Starting WhatsApp SSE Listener daemon..."
    nohup php /var/www/html/artisan whatsapp:web:listen main >> /var/www/html/storage/logs/wa-listener.log 2>&1 &

    # Jalankan background Queue Worker
    echo "Starting Queue Worker daemon..."
    nohup php /var/www/html/artisan queue:work --tries=3 --timeout=90 >> /var/www/html/storage/logs/queue-worker.log 2>&1 &
fi

exec "$@"
