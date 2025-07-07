# INSTALACIÓN DE SUPERVISOR Y HORIZON

## Configuración

* Dar de baja los contenedores
```sh
docker-compose down
```
* En el archivo .env de laradock modificar las siguientes filas a true

```sh
WORKSPACE_INSTALL_SUPERVISOR=true
WORKSPACE_INSTALL_PYTHON=true
```

* Copiar el archivo Dockerfile del directorio /laradock/horizon

```sh
cp -f docs/horizon/Horizon-Dockerfile laradock/workspace/Dockerfile
```

* Desde el directorio laradock, reconstruir el contenedor del workspace

```sh
docker-compose build workspace
```

* Levantar los contenedores

```sh
docker-compose up -d nginx php-fpm workspace laravel-echo-server redis
```

* Ingresar al contenedor del workspace

```sh
docker-compose exec workspace bash
```

* Instalamos horizon

```sh
composer require laravel/horizon

php artisan horizon:install
```

* Modificamos el archivo HorizonServiceProvider

```sh
nano app/providers/HorizonServiceProvider.php
```
* en la función boot modificamos el tipo de acceso, algo parecido a 

```sh
Horizon::auth(function ($request) {
    return true;
});
```

* publicamos horizonServiceProvider

```sh
php artisan vendor:publish --provider="Laravel\Horizon\HorizonServiceProvider"
```

* Verificamos que se haya publicado en el archivo app.php

```sh
cat /config/app.php

App\Providers\HorizonServiceProvider::class,
```

* verificacmos que supervisor esta activo

```sh
ps aux | grep supervisord
```

* Copiamos los archivos de configuración en el directorio de supervisor

```sh
cp docs/horizon/laravel-worker.conf /etc/supervisord.d/
cp docs/horizon/laravel-horizon.conf /etc/supervisord.d/
```

* Actualizamos las configuraciones

```sh
supervisorctl reread
supervisorctl update
supervisorctl start laravel-horizon
```

* salir del contenedor y reiniciar todos los contenedores

```sh
docker-compose restart
```

* supervisor y horizon deberian encontrarse funcionando en la pagina correspondiente

* si se desea modificar la ruta de acceso a horizon modificar el archivo /config/horizon.php

