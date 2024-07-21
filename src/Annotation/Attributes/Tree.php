<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes;

use Crastlin\LaravelAnnotation\Enum\NodeMode;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Tree
{
    /**
     * ------------------------------------------------
     * Annotations for menu and permission tree nodes |
     * @Author crastlin@163.com                       |
     * @date 2024-3-6                                 |
     * ------------------------------------------------
     *
     * @param string $name
     * Used for front-end menu display and node configuration display
     *
     * @param int $sort
     * Arrange weights for menu optimization level sorting
     *
     * @param string $preNamedSubMethods
     * When used as a class annotation, define a method with a valid menu name prefix, with multiple methods separated by commas
     *
     * @param bool $isMenuNode
     * Is it used for front-end menu display
     *
     * @param bool $isAuthNode
     * Do nodes need to verify access permissions
     *
     * @param string $virtualNode
     * Root menu virtual node
     *
     * @param NodeMode $checkMode
     * Annotation self check mode
     * For detailed instructions, please refer to the enumeration class
     */
    public function __construct(
        public string   $name = '',
        public int      $sort = 0,
        public bool     $isMenuNode = true,
        public bool     $isAuthNode = false,
        public string   $preNamedSubMethods = '',
        public string   $virtualNode = 'defaultPage',
        public NodeMode $checkMode = NodeMode::LOOSE_MODE,
    )
    {
    }
}
