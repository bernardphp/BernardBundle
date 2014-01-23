BernardBundle
=============

Integrates Bernard neatly with a Symfony application.

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

Running the Consumer
--------------------

What good is a message queue if you don't know how to run the consumer? Luckily this bundle auto registeres the commands
with you application. So if you run `php app/console` you should see `bernar:consume` and `bernard:produce`. Theese
works just as the documentation descripes but if you are in doubt just add `--help` when trying to run the command.

Todo
----

Currently this Bundle implements what i needed. This means there is proberly something that is missing.

 * Missing test for `BernardExtension`.
 * Missing compiler pass for registering middleware for consumer and producer
 * Missing some drivers

