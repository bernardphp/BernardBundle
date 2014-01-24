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
    driver: file # you can choose redis, predis, pheanstalk, file, doctrine etc.
    serializer: simple # this is the default and it is optional. Other values are symfony or jms
```

Great! You are now ready to use this diddy. Go and read the rest of the documentation on Bernard at bernardphp.com.

### Running the Consumer

What good is a message queue if you don't know how to run the consumer? Luckily this bundle auto registeres the commands
with you application. So if you run `php app/console` you should see `bernar:consume` and `bernard:produce`. Theese
works just as the documentation descripes but if you are in doubt just add `--help` when trying to run the command.

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

This is all good, but what if you can coded you own? Luckily this is taken care of with a tag for the container through
a compiler pass. When you define you service just tag you middleware factory service with `bernard.middleware` and give
it a `type` attribute or either `consumer` or `producer`.

``` yaml
my_middleware_factory:
    class: Acme\AwesomeMiddlewareFactory
    tags:
         - { name: bernard.middleware, type: consumer }
         - { name: bernard.middleware, type: producer }
```

As the example shows a middleware factory can be registered for both the consumer and producer.
