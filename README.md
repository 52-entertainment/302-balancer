# 302 Balancer

This is a load balancer that doesn't act as a load balancer. 

It is a _redirect_ balancer.

## Usage

```bash
php bin/console serve \
--host=0.0.0.0 \
--port=8080 \
--pick=random \
example1.org example2.org example3.org
```

```http request
GET http://0.0.0.0:8080/foo?bar=baz

HTTP/1.1 302 Found 
Location: http://example1.org/foo?bar=baz
```

Of course it will randomly select `example1.org`, `example2.org` `example3.org`.

You can alternatively use a round-robin algorithm with `--pick=round-robin`.

## Tests

```bash
./vendor/bin/pest
```
