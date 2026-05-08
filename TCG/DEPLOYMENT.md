🚀 TCG Platform Deployment Anleitung

📋 Voraussetzungen
Server-Anforderungen:

- Linux vServer (Ubuntu/Debian empfohlen)
- Mindestens 2GB RAM
- 20GB Speicherplatz
- Root-Zugriff oder sudo-Rechte

🔧 Schritt 1: Server vorbereiten

# 1. System aktualisieren
sudo apt update && sudo apt upgrade -y

# 2. Node.js v26 installieren
curl -fsSL https://deb.nodesource.com/setup_26.x | sudo -E bash -
sudo apt install -y nodejs

# 3. MySQL installieren
sudo apt install -y mysql-server

# 4. Redis installieren
sudo apt install -y redis-server

# 5. Nginx installieren
sudo apt install -y nginx

# 6. PM2 installieren
sudo npm install -g pm2

# 7. Git installieren
sudo apt install -y git

# 8. UFW Firewall installieren
sudo apt install -y ufw

🗄️ Schritt 2: MySQL Datenbank einrichten

# 1. MySQL sichern
sudo mysql_secure_installation

# 2. MySQL starten
sudo systemctl start mysql
sudo systemctl enable mysql

# 3. Als Root einloggen
sudo mysql -u root -p

# 4. In MySQL folgende Befehle ausführen:

CREATE DATABASE tcg_platform;
CREATE USER 'tcg_user'@'localhost' IDENTIFIED BY 'DEIN_SICHERES_PASSWORT';
GRANT ALL PRIVILEGES ON tcg_platform.* TO 'tcg_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

📦 Schritt 3: Projekt auf Server hochladen

# 1. Projekt klonen oder hochladen
cd /var/www
git clone <DEIN_REPO_URL> TCG
cd TCG

# 2. Backend einrichten
cd backend
npm install

# 3. Environment Datei erstellen
nano .env

Inhalt der .env Datei:

DATABASE_URL="mysql://tcg_user:DEIN_SICHERES_PASSWORT@localhost:3306/tcg_platform"
JWT_SECRET="DEIN_SEHR_SICHERES_GEHEIM_TOKEN_HIER"
JWT_EXPIRATION="7d"
REDIS_HOST="localhost"
REDIS_PORT="6379"
PORT="3000"
NODE_ENV="production"

# 4. Prisma Client generieren
npx prisma generate

# 5. Datenbank Migrationen ausführen
npx prisma migrate deploy

# 6. Seed Daten einfügen
npm run seed

# 7. Frontend einrichten
cd ../frontend
npm install

# 8. Frontend builden
npm run build

🚀 Schritt 4: Services mit PM2 starten

# 1. Backend starten
cd /var/www/TCG/backend
pm2 start dist/src/main.js --name tcg-backend

# 2. Frontend starten
cd /var/www/TCG/frontend
pm2 start npm --name tcg-frontend -- start

# 3. PM2 Konfiguration speichern
pm2 save
pm2 startup

🌐 Schritt 5: Nginx konfigurieren (Port 8080 für Apache2 Kompatibilität)

# 1. Nginx Konfigurationsdatei erstellen
sudo nano /etc/nginx/sites-available/tcg-platform

Inhalt der Nginx Konfiguration:

server {
    listen 8080;
    server_name DEINE_SERVER_IP;

    # Frontend
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }

    # Backend API
    location /api {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }

    # WebSocket support
    location /ws {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}

# 2. Site aktivieren
sudo ln -s /etc/nginx/sites-available/tcg-platform /etc/nginx/sites-enabled/

# 3. Standard Site deaktivieren
sudo rm /etc/nginx/sites-enabled/default

# 4. Nginx testen
sudo nginx -t

# 5. Nginx neu starten
sudo systemctl restart nginx

# 6. Firewall konfigurieren
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 8080/tcp
sudo ufw allow 443/tcp
sudo ufw enable

🔐 Schritt 6: SSL Zertifikat (Optional aber empfohlen)

# 1. Certbot installieren
sudo apt install -y certbot python3-certbot-nginx

# 2. SSL Zertifikat erhalten
sudo certbot --nginx -d DEINE_SERVER_IP

# 3. Auto-Renewal ist automatisch konfiguriert

⚙️ Schritt 7: Redis starten

# 1. Redis starten
sudo systemctl start redis-server

# 2. Redis beim Boot starten
sudo systemctl enable redis-server

# 3. Redis Status prüfen
sudo systemctl status redis-server

🧪 Schritt 8: Testen

# 1. Backend Status prüfen
pm2 status

# 2. Logs prüfen
pm2 logs tcg-backend
pm2 logs tcg-frontend

# 3. Nginx Status prüfen
sudo systemctl status nginx

# 4. MySQL Status prüfen
sudo systemctl status mysql

# 5. Redis Status prüfen
sudo systemctl status redis-server

# 6. Firewall Status prüfen
sudo ufw status

📝 Wichtige Einstellungen

Backend .env Datei:

- DATABASE_URL: MySQL Verbindungsstring
- JWT_SECRET: Muss ein sehr sicheres, zufälliges Passwort sein
- JWT_EXPIRATION: Token Lebensdauer (Standard: 7d)
- REDIS_HOST: Redis Server Adresse
- REDIS_PORT: Redis Port (Standard: 6379)
- PORT: Backend Port (Standard: 3000)

Frontend .env.local Datei:

NEXT_PUBLIC_API_URL="http://DEINE_SERVER_IP:8080"
NEXT_PUBLIC_WS_URL="ws://DEINE_SERVER_IP:8080"

🔧 Troubleshooting

Backend startet nicht:

# Logs prüfen
pm2 logs tcg-backend

# Neu starten
pm2 restart tcg-backend

# Fehler im Build prüfen
cd /var/www/TCG/backend
npm run build

Datenbank Verbindungsprobleme:

# MySQL Status prüfen
sudo systemctl status mysql

# MySQL neu starten
sudo systemctl restart mysql

# Verbindung testen
mysql -u tcg_user -p tcg_platform

Nginx Probleme:

# Konfiguration testen
sudo nginx -t

# Nginx neu starten
sudo systemctl restart nginx

# Error Logs prüfen
sudo tail -f /var/log/nginx/error.log

Frontend Build Probleme:

# Node Cache leeren
cd /var/www/TCG/frontend
rm -rf .next node_modules
npm install
npm run build

Firewall Probleme:

# UFW installieren falls fehlt
sudo apt install -y ufw

# Firewall Status prüfen
sudo ufw status

# Firewall aktivieren
sudo ufw enable

🔄 Updates deployen

# 1. Projekt aktualisieren
cd /var/www/TCG
git pull

# 2. Backend aktualisieren
cd backend
npm install
npx prisma migrate deploy
npm run build
pm2 restart tcg-backend

# 3. Frontend aktualisieren
cd ../frontend
npm install
npm run build
pm2 restart tcg-frontend

📊 Monitoring

# PM2 Monitoring
pm2 monit

# Logs live ansehen
pm2 logs

# Ressourcen prüfen
htop

🔒 Sicherheitshinweise

- Ändere alle Standard-Passwörter
- Verwende starke JWT Secrets
- Aktiviere SSL/HTTPS
- Firewall konfigurieren
- Regelmäßige Updates durchführen
- Backups der Datenbank erstellen

📞 Support bei Problemen

Wenn du Probleme hast, prüfe zuerst:

- Die Logs (pm2 logs, /var/log/nginx/error.log)
- Die Service Status (systemctl status)
- Die Firewall Regeln (sudo ufw status)
- Die Ports (sudo netstat -tulpn)

Die Plattform sollte nun unter http://DEINE_SERVER_IP:8080 erreichbar sein! 🎉

⚠️ Wichtiger Hinweis zu Apache2:

Wenn Apache2 bereits auf Port 80 läuft, wird Nginx auf Port 8080 konfiguriert.
Apache2 bleibt auf Port 80 aktiv und kann für andere Projekte genutzt werden.

Falls du Nginx auf Port 80 haben möchtest, musst du Apache2 zuerst stoppen:

sudo systemctl stop apache2
sudo systemctl disable apache2

Dann Nginx Konfiguration auf Port 80 ändern und neu starten.