# 302

This is a load balancer that doesn't _act_ as a load balancer. 

It is a _redirect_ balancer. Instead of forwarding traffic to a specific host/port, 
it returns a 302 response with a different location.

Typical usage is that your entrypoint is `https://example.com/foo` and you want to redirect your traffic 
to a pool made of `https://01.example.com` and `https://02.example.com`, by keeping path and query string intact.

It is built with  **PHP 8** on top of [ReactPHP](https://reactphp.org/). 
Because it answers very short responses and runs within a loop, it can easily handle thousands of requests/s 
without blinking an eye.

## Build

Build is optional (you can simply run `php bin/console serve`) but will produce an optimized, single-file executable PHAR.

PHP 8, Composer and [Box](https://github.com/box-project/box/blob/master/doc/installation.md) are globally required on your computer.

To build the application, run:

```bash
./bin/build
```

It will land in `bin/302`.

## Usage

### Live balancing (no persistence - for testing purposes or prototyping)

```bash
php bin/302 serve \
--host=0.0.0.0 \
--port=8080 \
--pick=random \ # or round-robin
example1.org \
example2.org \
example3.org
```

```http request
GET http://0.0.0.0:8080/foo?bar=baz

HTTP/1.1 302 Found 
Location: http://example2.org/foo?bar=baz
```

### Persisted storage (Redis)

```bash
# Expose the REDIS_DSN variable if necessary, default is:
export REDIS_DSN="redis://localhost:6379"
php bin/302 serve --host=0.0.0.0 --port=8080
```

_App will run, but user agent will get 503 errors because the server pool is empty._

#### Add a server to the pool

```bash
php bin/302 server:add example1.org
```

_This command can be run while `302 serve` is running, no need to restart the app!_

#### Remove a server from the pool

```bash
php bin/302 server:remove example1.org
```

_This command can be run while `302 serve` command is running, no need to restart the app!_

#### List servers in the pool

```bash
php bin/302 server:list
```

## Tests

```bash
./vendor/bin/pest
```

## Deployment 

### Example Supervisor config

After [building the app](#build), you can easily create a supervisor recipe to load it on startup:

```ini
[program:302]
command=php /usr/local/bin/302 serve --host=127.0.0.1 --port=80%(process_num)02d
user=www-data
numprocs=4
startsecs=1
autostart=true
autorestart=true
process_name=%(program_name)s_%(process_num)02d
environment = APP_LOG_DIR=/var/log/302
```

With the above config, **302** will run on `127.0.0.1:8000`, `127.0.0.1:8001`, `127.0.0.1:8002` and `127.0.0.1:8003`.

Round-robin will be shared across the instances (since they share the same Redis instance).

### CORS / SSL termination

**302** has no built-in CORS nor SSL termination, but this can be handled by any web server with reverse-proxy capabilities 
(Apache, Nginx, Caddy, ...).

#### Caddyfile example

If you're using [Caddy](https://caddyserver.com/), here's an example `Caddyfile`:

```
example.org {
    @get {
        method GET
    }
    @options {
        method OPTIONS
    }
    header Access-Control-Allow-Origin *
    header Access-Control-Allow-Redirect true
    respond @options 200
    reverse_proxy @get 127.0.0.1:8000 127.0.0.1:8001 127.0.0.1:8002 127.0.0.1:8003
}
```
