<?php

namespace Bernard\BernardBundle\EventListener;

use Bernard\Doctrine\MessagesSchema;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

class SchemaListener
{
    public function postGenerateSchema(GenerateSchemaEventArgs $args)
    {
        MessageSchema::create($args->getSchema());
    }
}
