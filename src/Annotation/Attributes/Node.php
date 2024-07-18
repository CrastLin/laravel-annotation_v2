<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes;

use Crastlin\LaravelAnnotation\Enum\NodeCode;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Node
{

    /**
     * ------------------------------------------------
     * Annotations for menu and permission tree nodes |
     * @Author crastlin@163.com                       |
     * @date 2024-1-6                                 |
     * ------------------------------------------------
     *
     * Application for managing multiple role permission allocation and role corresponding front-end menus in backend systems
     * All menus and interface nodes are configured through annotations during the development phase and generated with just one click from the command line, facilitating agile development without the need for administrators to add permission nodes themselves
     *
     * @param string $name
     * Used for front-end menu display and node configuration display
     *
     * @param string $parent
     * Parent node configuration，Used for menu leaf hierarchy and permission classification
     * The method name defined by the parent node. If it is the current controller, only the method name needs to be configured. If it is not the current controller, the controller name and method name need to be added
     *
     * @param NodeCode $code
     * Used for implementing button level permissions in the application front-end
     *
     * @param bool $isMenuNode
     * Is it used for front-end menu display
     *
     * @param bool $isAuthNode
     * Do nodes need to verify access permissions
     *
     * @param int $sort
     * Node sorting, used for front-end menu display optimization level
     *
     * @param string $preNamedSubMethods
     * When used as a class annotation, define a method with a valid menu name prefix, with multiple methods separated by commas
     *
     * @param string $icon
     * Icon address or style name, used for front-end menu display
     *
     * @param string $remark
     * Node notes, used to display the functional description of the node during role authorization
     *
     * @param bool $ignore
     * Ignore saving nodes，ignore if there are child nodes
     *
     * @param bool $delete
     * Delete node, ignore if there are child nodes
     *
     * @param string $component
     * Component name, used for routing and binding to front-end components
     */
    public function __construct(
        public string   $name,
        public string   $parent = '',
        public NodeCode $code = NodeCode::QUERY,
        public bool     $isMenuNode = false,
        public bool     $isAuthNode = true,
        public int      $sort = 0,
        public string   $preNamedSubMethods = '',
        public string   $icon = '',
        public string   $remark = '',
        public bool     $ignore = false,
        public bool     $delete = false,
        public string   $component = '',
    )
    {
    }
}
