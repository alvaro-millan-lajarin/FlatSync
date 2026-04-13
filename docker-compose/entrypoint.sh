#!/bin/sh
set -e

# Generar .env de CodeIgniter con las variables de Railway
cat > /var/www/.env << EOF
CI_ENVIRONMENT = development

app.baseURL  = https://${RAILWAY_PUBLIC_DOMAIN}/
app.indexPage =

encryption.key = hex2bin:${APP_ENCRYPTION_KEY}

database.default.hostname = ${MYSQLHOST}
database.default.database = ${MYSQLDATABASE}
database.default.username = ${MYSQLUSER}
database.default.password = ${MYSQLPASSWORD}
database.default.port     = ${MYSQLPORT}
database.default.DBDriver = MySQLi
EOF

# Drop all existing tables (clean slate) then run migrations
php -r "
\$db = new mysqli(getenv('MYSQLHOST'), getenv('MYSQLUSER'), getenv('MYSQLPASSWORD'), getenv('MYSQLDATABASE'), (int)getenv('MYSQLPORT'));
if (\$db->connect_error) { echo 'DB connect error: ' . \$db->connect_error . PHP_EOL; exit(1); }
\$db->query('SET FOREIGN_KEY_CHECKS=0');
\$result = \$db->query('SHOW TABLES');
while (\$row = \$result->fetch_row()) {
    \$db->query('DROP TABLE IF EXISTS \`' . \$row[0] . '\`');
    echo 'Dropped: ' . \$row[0] . PHP_EOL;
}
\$db->query('SET FOREIGN_KEY_CHECKS=1');
\$db->close();
echo 'All tables dropped.' . PHP_EOL;
"

php /var/www/spark migrate --no-interaction

exec /usr/bin/supervisord -c /etc/supervisord.conf
