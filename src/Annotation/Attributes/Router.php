<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes;


use Crastlin\LaravelAnnotation\Enum\Method;

/**
 * Routing annotation base class
 * @Author crastlin@163.cm
 * @Date 2023-12-27
 *
 * @description Implement rapid development of routing settings
 * @description When developing the environment, routing will be automatically generated
 * @description In the production environment, it is necessary to generate routing configurations through instructions. For detailed usage methods, please refer to README.md
 */
abstract class Router
{

    /**
     * @var Method $method Request method
     * Restrict request methods by configuring this parameter
     * Allow request parameters in enumeration class Method
     */
    public Method $method;

    /**
     * @param string $path Defined as a routing access path address
     * Namely, in the browser address bar, the domain name suffix address
     * When undefined, defaults to: controller directory/class name (excluding controller suffix name)/method name
     * @example #[RouteAnnotation("demo/test")]
     */
    public string $path;


    /**
     * @param string $name The routing name
     * When not configured, defaults to: controller level+controller class name (excluding controller suffix)+method name
     * @example #[RouteAnnotation(name: "demo.test")]
     */
    public string $name;


    /**
     * @var array<string, string> $where Regular constraint conditions
     * Routing with parameter regularization constraints
     * Defined as an associative array type, with the key name being the routing parameter name and the value being a regular expression
     * @example #[RouteAnnotation(url: "demo/test", where: ["id" => "\d+", "username" => "\w+"])]
     */
    public array $where;

}
