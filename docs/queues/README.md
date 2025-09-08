# MANEJO DE LAS COLAS DE MENSAJES

## Ingresar al contenedor del redis (donde se almacenan las colas e espera)
```sh
docker-compose exec redis bash
```
## Ingresar a la consola del redis
```sh
redis-cli
```
## Listar la lista de jobs 
```sh
KEYS *
LRANGE queues:default 0 -1
```
## Eliminar las colas por defecto
```sh
DEL queues:default
```
## Salir de la consola de redis
```sh
exit
```
## dentro el contenedor del workspace
```sh
nohup php artisan queue:work redis --tries=5 --timeout=300 --daemon > storage/logs/worker.log 2>&1 &
```
## Descripción del comando

    * nohub: evita que el proceso se detenga al cerrar la terminal
    * --tries: cantidad de intentos antes de dar como job fallido
    * --timeout: tiempo maximo de ejecución del job
    * --daemon: mantiene el job en ejecución sin recarga de laravel
    * storage/logs/worker.log: guarda un registro de logs de los workers
    * &: envia el proceso a segundo plano
