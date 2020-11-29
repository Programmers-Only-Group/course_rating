#!/bin/bash

# Include base
source $(dirname $0)/_base.sh
source $(dirname $0)/_paths.sh

declare -a arguments
declare -A options
declare -A schema=(
  ["--no-cache"]=0
  ["--env"]=1
  ["--skip-migrations"]=0
  ["--skip-composer"]=0
)

# Load options and args
getOptions schema options arguments $@

if inMap options "--env" ; then
  APP_ENV=${options["--env"]}
else
  APP_ENV="dev"
fi

if inMap options "--no-cache" ; then
  NO_CACHE=1
else
  NO_CACHE=0
fi

if inMap options "--skip-migrations" ; then
  SKIP_MIGRATIONS=1
else
  SKIP_MIGRATIONS=0
fi

if inMap options "--skip-composer" ; then
  SKIP_COMPOSER=1
else
  SKIP_COMPOSER=0
fi

# Create configuration
echo "Copying configuration for $APP_ENV environment"

if [ ! -f $ROOT_PATH/.env ]; then
    cp $ROOT_PATH/.env.$APP_ENV.example $ROOT_PATH/.env
    echo "Configuration created for $APP_ENV environment"
  else
    echo "Configuration exists, skipping"
fi

# Create server log path for nginx
if [ ! -f $SERVER_PATH/logs/access.log ]; then
    touch $SERVER_PATH/logs/access.log
    echo "Created access.log file for nginx"
fi

if [ ! -f $SERVER_PATH/logs/error.log ]; then
    touch $SERVER_PATH/logs/error.log
    echo "Created error.log file for nginx"
fi

cd $ROOT_PATH

# Download containers
docker-compose -f docker-compose.yml -f docker-compose.$APP_ENV.yml pull

# Build containers
docker-compose -f docker-compose.yml -f docker-compose.$APP_ENV.yml build \
 $( isTrue $NO_CACHE && echo '--no-cache' ) \
 ${arguments[*]}

# Set status of the build
BUILD_STATUS=$?

if [ $BUILD_STATUS -gt 0 ]; then
    echo "Build failed for the \"${APP_ENV}\" environment." && exit $BUILD_STATUS
fi

# Initialize containers
docker-compose -f docker-compose.yml -f docker-compose.$APP_ENV.yml up -d

# Install composer if run as development
if [ $APP_ENV == 'dev' ] && isFalse $SKIP_COMPOSER ; then
    docker-compose -f docker-compose.yml -f docker-compose.$APP_ENV.yml exec \
      api composer install
fi

# Run migrations if run as development
if [ $APP_ENV == 'dev' ] && isFalse $SKIP_MIGRATIONS ; then
    docker-compose -f docker-compose.yml -f docker-compose.$APP_ENV.yml exec \
      api bin/console doctrine:migrations:migrate -n
fi

# Halt containers
docker-compose stop

exit $BUILD_STATUS;
