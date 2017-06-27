<?php

namespace Bernard\BernardBundle\Normalizer;

use Assert\Assertion;
use Bernard\Message\DefaultMessage;
use Bernard\Normalizer\PlainMessageNormalizer;

class DefaultMessageNormalizer extends PlainMessageNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        @trigger_error('The '.__CLASS__.' class is deprecated and will removed in version 3.0. Use '.PlainMessageNormalizer::class.' instead.', E_USER_DEPRECATED);

        parent::normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        @trigger_error('The '.__CLASS__.' class is deprecated and will removed in version 3.0. Use '.PlainMessageNormalizer::class.' instead.', E_USER_DEPRECATED);

        Assertion::notEmptyKey($data, 'name');
        Assertion::keyExists($data, 'arguments');
        Assertion::isArray($data['arguments']);

        return new DefaultMessage($data['name'], $data['arguments']);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === DefaultMessage::class;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof DefaultMessage;
    }
}
