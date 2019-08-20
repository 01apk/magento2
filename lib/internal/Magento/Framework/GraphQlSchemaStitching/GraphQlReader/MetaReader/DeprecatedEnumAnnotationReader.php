<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader;

/**
 * Reads documentation from the annotation "@deprecated" of an AST node
 */
class DeprecatedEnumAnnotationReader
{
    /**
     * Read deprecated annotation for a specific node if exists
     *
     * @param \GraphQL\Language\AST\NodeList $directives
     * @return array
     */
    public function read(\GraphQL\Language\AST\NodeList $directives) : string
    {
        foreach ($directives as $directive) {
            if ($directive->name->value == 'deprecated') {
                foreach ($directive->arguments as $directiveArgument) {
                    if ($directiveArgument->name->value == 'deprecationReason') {
                        return $directiveArgument->value->value;
                    }
                }
            }
        }
        return '';
    }
}
