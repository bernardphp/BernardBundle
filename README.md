BernardBundle
=============

Integrates Bernard neatly with a Symfony application.

[![Build Status](https://travis-ci.org/bernardphp/BernardBundle.png?branch=master)](https://travis-ci.org/bernardphp/BernardBundle)

Getting Started
---------------

Everything starts by installing the bundle. This is done through composer by adding the following lines
to your `composer.json` file and running `composer update bernard/bernard-bundle`.

``` json
{
    "require" : {
        "bernard/bernard-bundle" : "~1.0"
    }
}
```

Next up is adding the bundle to your kernel and configuring it in `config.yml`.

``` php
// app/AppKernel.php
// .. previous class definition
public function registerBundles()
{
    // .. all the other bundles you have registered.
    $bundles[] = new Bernard\BernardBundle\BernardBernardBundle();
    // .. the rest of the method
}
```

``` yml
# .. previous content of app/config/config.yml
bernard_bernard:
    driver: file # you can choose predis, phpredis, file, doctrine etc.
    serializer: simple # this is the default and it is optional. Other values are symfony or jms
```

Great! You are now ready to use this diddy. Go and read the rest of the documentation on Bernard at [bernardphp.com](http://bernardphp.com/).

### Running the Consumer

What good is a message queue if you don't know how to run the consumer? Luckily this bundle auto registers the commands
with your application. So if you run `php app/console` you should see `bernard:consume` and `bernard:produce`. These
work just as the documentation describes but if you are in doubt just add `--help` when running the command.

It is important to use `--no-debug` when running the consumer for longer periods of time. This is because Symfony by
default in debug mode collects a lot of information and logging and if this is omitted you will run into memory problems
sooner or later.

### Adding Receivers

In order to know what messages needs to go where you have to register some receivers. This is done with a tag in your
service definitions.

``` yaml
my_receiver:
    class: Acme\Receiver
    tags:
         - { name: bernard.receiver, message: SendNewsletter }
         - { name: bernard.receiver, message: ImportUsers }
```

As the example shows it is possible to register the same receiver for many different message types.

### Configuring Middlewares

By default the three core middlewares are registered for the consumer and only needs to be turned on. This example shows
enabling all of them. But remember theese are only enabled for the consumer.

``` yaml
bernard_bernard:
    middlewares:
        error_log: true
        logger: true # only for versions of symfony that implements PSR-3
        failures: true
```

This is all good, but what if you can code your own? Luckily this is taken care of with a tag for the container through
a compiler pass. When you define your service just tag your middleware factory service with `bernard.middleware` and give
it a `type` attribute with either `consumer` or `producer`.

``` yaml
my_middleware_factory:
    class: Acme\AwesomeMiddlewareFactory
    tags:
         - { name: bernard.middleware, type: consumer }
         - { name: bernard.middleware, type: producer }
```

As the example shows a middleware factory can be registered for both the consumer and producer.

Configuration Options
---------------------

There are different options that can be set that changes the behaviour for various drivers.

### Doctrine

When using the doctrine driver it can be useful to use a seperate connection when using Bernard. In order to
change it use the `connection` option. This also needs to be set if you default connection is called anything else
than `default`.

``` yaml
doctrine:
    dbal:
        connections:
            bernard:
                host:     "%database_host%"
                charset:  UTF8

bernard_bernard:
    driver: doctrine
    options:
        connection: bernard # default is the default value
```

### FlatFile

The file driver needs to know what directory it should use for storing messages and its queue metadata.

``` yaml
bernard_bernard:
    driver: file
    options:
        directory: %kernel.cache_dir%/bernard
```

The above example will dump your messages in the cache folder. In most cases you will want to change this to something
because the cache folder is deleted every time the cache is cleared (obviously).

### PhpRedis

PhpRedis depends on a service called `snc_redis.bernard` with a configured `Redis` instance. If you want to use a
different name use the `phpredis_service` option:

``` yaml
bernard_bernard:
    driver: phpredis
    options:
        phpredis_service: my_redis_service
```

If you're using the [SncRedisBundle](https://github.com/snc/SncRedisBundle) you have to set logging to false for the
bernhard client to ensure that is is a ``Redis`` instance and not wrapped.

### IronMQ

When using the IronMQ driver you have to configure an `IronMQ` connection instance. You can configure it like the following:

``` yaml
services:
    ironmq_connection:
        class: IronMQ
        arguments:
            - { token: %ironmq_token%, project_id: %ironmq_project_id% }
        public: false

bernard_bernard:
    driver: ironmq
    options:
        ironmq_service: ironmq_connection
```
