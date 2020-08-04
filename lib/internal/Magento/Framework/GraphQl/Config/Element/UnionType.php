<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config\Element;

/**
 * Class representing 'union' GraphQL config element.
 */
class UnionType implements UnionInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $types;

    /**
     * @var string
     */
    private $resolver;

    /**
     * @var string
     */
    private $description;

    /**
     * @param string $name
     * @param string $resolver
     * @param string[] $types
     * @param string $description
     */
    public function __construct(
        string $name,
        string $resolver,
        array $types,
        string $description
    ) {
        $this->name = $name;
        $this->types = $types;
        $this->resolver = $resolver;
        $this->description = $description;
    }

    /**
     * Get the type name.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get a list of fields that make up the possible return or input values of a type.
     *
     * @return string[]
     */
    public function getTypes() : array
    {
        return $this->types;
    }

    /**
     * Return the name of the resolver class that determines the concrete type to display in the result.
     *
     * @return string
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * Get a human-readable description of the type.
     *
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }
}
