#!/bin/sh
set -e

# Generar .env de CodeIgniter con las variables de Railway
cat > /var/www/.env << EOF
CI_ENVIRONMENT = production

app.baseURL = https://${RAILWAY_PUBLIC_DOMAIN}/

database.default.hostname = ${MYSQLHOST}
database.default.database = ${MYSQLDATABASE}
database.default.username = ${MYSQLUSER}
database.default.password = ${MYSQLPASSWORD}
database.default.port     = ${MYSQLPORT}
database.default.DBDriver = MySQLi
EOF

exec /usr/bin/supervisord -c /etc/supervisord.conf
