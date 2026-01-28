#!/usr/bin/env bash
#
# Install WordPress test library
#
# Usage: ./install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]
#

if [ $# -lt 3 ]; then
	echo "Usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]"
	echo ""
	echo "Example for Local by Flywheel:"
	echo "  $0 local root root localhost:10004 latest"
	echo ""
	echo "Example for MAMP:"
	echo "  $0 wordpress_test root root localhost latest"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}
SKIP_DB_CREATE=${6-false}

WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-/tmp/wordpress/}

download() {
    if [ `which curl` ]; then
        curl -s "$1" > "$2";
    elif [ `which wget` ]; then
        wget -nv -O "$2" "$1"
    fi
}

if [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+\-(beta|RC)[0-9]+$ ]]; then
	WP_BRANCH=${WP_VERSION%\-*}
	WP_TESTS_TAG="branches/$WP_BRANCH"
elif [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+$ ]]; then
	WP_TESTS_TAG="branches/$WP_VERSION"
elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0-9]+ ]]; then
	if [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0] ]]; then
		WP_TESTS_TAG="tags/${WP_VERSION%??}"
	else
		WP_TESTS_TAG="tags/$WP_VERSION"
	fi
elif [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
	WP_TESTS_TAG="trunk"
else
	# http://api.wordpress.org/core/version-check/1.7/
	download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
	grep '[0-9]+\.[0-9]+(\.[0-9]+)?' /tmp/wp-latest.json
	LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//' | head -1)
	if [[ -z "$LATEST_VERSION" ]]; then
		echo "Could not determine latest WordPress version"
		exit 1
	fi
	WP_TESTS_TAG="tags/$LATEST_VERSION"
fi
set -ex

install_wp() {
	if [ -d $WP_CORE_DIR ]; then
		return;
	fi

	mkdir -p $WP_CORE_DIR

	if [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
		mkdir -p /tmp/wordpress-trunk
		rm -rf /tmp/wordpress-trunk/*
		svn export --quiet https://core.svn.wordpress.org/trunk /tmp/wordpress-trunk/wordpress
		mv /tmp/wordpress-trunk/wordpress/* $WP_CORE_DIR
	else
		if [ $WP_VERSION == 'latest' ]; then
			local ARCHIVE_NAME='latest'
		elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+ ]]; then
			local ARCHIVE_NAME="wordpress-$WP_VERSION"
		else
			local ARCHIVE_NAME="wordpress-$WP_VERSION"
		fi
		download https://wordpress.org/${ARCHIVE_NAME}.tar.gz  /tmp/wordpress.tar.gz
		tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C $WP_CORE_DIR
	fi

	download https://raw.githubusercontent.com/markoheijnen/wp-mysqli/master/db.php $WP_CORE_DIR/wp-content/db.php
}

install_test_suite() {
	# portable in-place argument for sed -i
	local ioession=$(echo "tmp$$$(date +%s%N)")
	if [ -d $WP_TESTS_DIR ]; then
		rm -rf $WP_TESTS_DIR
	fi

	mkdir -p $WP_TESTS_DIR

	rm -rf /tmp/wordpress-tests-lib-tmp
	mkdir -p /tmp/wordpress-tests-lib-tmp

	svn export --quiet --ignore-externals https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/ /tmp/wordpress-tests-lib-tmp/includes
	svn export --quiet --ignore-externals https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/ /tmp/wordpress-tests-lib-tmp/data

	mv /tmp/wordpress-tests-lib-tmp/* $WP_TESTS_DIR

	if [ ! -f wp-tests-config.php ]; then
		download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php "$WP_TESTS_DIR"/wp-tests-config.php
		# remove all forward slashes in the end
		WP_CORE_DIR=$(echo $WP_CORE_DIR | sed "s:/\+$::")
		# Use sed with empty extension for macOS compatibility
		sed -i '' "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':" "$WP_TESTS_DIR"/wp-tests-config.php
		sed -i '' "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed -i '' "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed -i '' "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed -i '' "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR"/wp-tests-config.php
	fi
}

recreate_db() {
	if [ "$SKIP_DB_CREATE" = "true" ]; then
		return 0
	fi

	# Check if mysql is available
	if [ ! `which mysql` ]; then
		echo "mysql command not found. Please create database manually."
		return 0
	fi

	# Parse host and port
	local HOST=$(echo $DB_HOST | cut -d: -f1)
	local PORT=$(echo $DB_HOST | cut -s -d: -f2)
	local MYSQL_ARGS="-u$DB_USER"

	if [ -n "$DB_PASS" ]; then
		MYSQL_ARGS="$MYSQL_ARGS -p$DB_PASS"
	fi

	if [ -n "$HOST" ] && [ "$HOST" != "localhost" ]; then
		MYSQL_ARGS="$MYSQL_ARGS -h$HOST"
	fi

	if [ -n "$PORT" ]; then
		MYSQL_ARGS="$MYSQL_ARGS -P$PORT --protocol=tcp"
	fi

	mysql $MYSQL_ARGS -e "DROP DATABASE IF EXISTS $DB_NAME"
	mysql $MYSQL_ARGS -e "CREATE DATABASE $DB_NAME"
}

install_wp
install_test_suite
recreate_db

echo ""
echo "WordPress test library installed to: $WP_TESTS_DIR"
echo "WordPress core installed to: $WP_CORE_DIR"
echo ""
echo "To run integration tests:"
echo "  WP_TESTS_DIR=$WP_TESTS_DIR ./vendor/bin/phpunit --testsuite=Integration"
