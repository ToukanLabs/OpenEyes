#!/bin/bash

echo "Adding openeyes user..."
mysql -u root -Bse "CREATE USER '$DATABASE_TEST_USER'@'$DATABASE_TEST_HOST' IDENTIFIED BY '$DATABASE_TEST_PASS';
CREATE DATABASE \`$DATABASE_TEST_NAME\`;
CREATE DATABASE \`$DATABASE_NAME\`;
COMMIT;
GRANT ALL PRIVILEGES ON *.* TO '$DATABASE_TEST_USER'@'$DATABASE_TEST_HOST' IDENTIFIED BY '$DATABASE_TEST_PASS';
FLUSH PRIVILEGES;"
echo "Done."
echo "Importing sample data..."
echo "Importing dev data..."
mysql $DATABASE_NAME -u root -p"$MYSQL_ROOT_PASS" < protected/modules/sample/sql/openeyes_sample_data.sql
echo "Importing test data..."
mysql $DATABASE_TEST_NAME -u root -p"$MYSQL_ROOT_PASS" < protected/modules/sample/sql/openeyes_sample_data.sql
echo "Done."