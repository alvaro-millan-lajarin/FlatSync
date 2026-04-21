#!/bin/sh
set -e

# Generar .env de CodeIgniter con las variables de Railway
cat > /var/www/.env << EOF
CI_ENVIRONMENT = production

app.baseURL  = ${APP_BASE_URL}
app.indexPage =

encryption.key = hex2bin:${APP_ENCRYPTION_KEY}

database.default.hostname = ${MYSQLHOST}
database.default.database = ${MYSQLDATABASE}
database.default.username = ${MYSQLUSER}
database.default.password = ${MYSQLPASSWORD}
database.default.port     = ${MYSQLPORT}
database.default.DBDriver = MySQLi
EOF

# Wait for MySQL to be ready
php -r "
\$tries = 0;
while (\$tries < 30) {
    \$db = @new mysqli(getenv('MYSQLHOST'), getenv('MYSQLUSER'), getenv('MYSQLPASSWORD'), getenv('MYSQLDATABASE'), (int)getenv('MYSQLPORT'));
    if (!\$db->connect_error) { \$db->close(); echo 'DB ready.' . PHP_EOL; exit(0); }
    echo 'Waiting for DB... (' . \$db->connect_error . ')' . PHP_EOL;
    sleep(2);
    \$tries++;
}
echo 'DB not available after 60s.' . PHP_EOL;
exit(1);
"

# Run any pending migrations (safe: only runs new ones, data is preserved)
php /var/www/spark migrate --no-interaction

exec /usr/bin/supervisord -c /etc/supervisord.conf
