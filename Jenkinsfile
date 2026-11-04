pipeline {
    agent any

    environment {
        DEPLOY_DIR = '/var/www/html'
    }

    stages {
        stage('Descargar código') {
            steps {
                checkout scm
            }
        }

        stage('Preparar dependencias') {
            steps {
                sh 'composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader'
                sh 'npm install --no-audit --no-fund'
                sh 'npm run production'
            }
        }

        stage('Desplegar Laravel') {
            steps {
                sh '''
                    set -eu

                    test -d "$DEPLOY_DIR" || {
                        echo "No existe el directorio de despliegue: $DEPLOY_DIR"
                        exit 1
                    }
                    test -f "$DEPLOY_DIR/.env" || {
                        echo "Falta $DEPLOY_DIR/.env; configúralo en el servidor antes del primer despliegue."
                        exit 1
                    }

                    archive=$(mktemp)
                    trap 'rm -f "$archive"' EXIT
                    tar --exclude=.git --exclude=.env --exclude=storage --exclude=node_modules -cf "$archive" .
                    sudo tar -xf "$archive" -C "$DEPLOY_DIR"

                    sudo mkdir -p \
                        "$DEPLOY_DIR/storage/framework/cache/data" \
                        "$DEPLOY_DIR/storage/framework/sessions" \
                        "$DEPLOY_DIR/storage/framework/views" \
                        "$DEPLOY_DIR/storage/logs" \
                        "$DEPLOY_DIR/bootstrap/cache"
                    sudo chown -R www-data:www-data \
                        "$DEPLOY_DIR/storage" \
                        "$DEPLOY_DIR/bootstrap/cache"
                    sudo chmod -R ug+rwX \
                        "$DEPLOY_DIR/storage" \
                        "$DEPLOY_DIR/bootstrap/cache"
                    sudo -u www-data sh -c "cd '$DEPLOY_DIR' && php artisan config:cache"
                '''
            }
        }
    }
}
