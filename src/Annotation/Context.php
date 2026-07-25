<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation;

use Illuminate\Http\Request;

/**
 * @package Crastlin\Annotation
 * @author crastlin@163.com
 * @date 2026-7-25
 */
class Context
{

    /**
     * Custom non-singleton binding to the framework container context object
     * used for stateless data isolation in the Octan pattern
     * The context object is bound to the framework's Request object
     * @param Request $request
     * @param array $attributes
     */
    public function __construct(
        public readonly Request $request,
        protected array         $attributes = []
    )
    {
    }

    /**
     * check data name when is exists
     * @param string $name
     * @return bool
     */
    function exists(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * get context data by name
     * @param string $name
     * @return mixed
     */
    function get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * set context data by name
     * @param string $name
     * @param mixed $value
     * @return void
     */
    function set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    /**
     * unset context data by name
     * @param string $name
     * @return void
     */
    function unset(string $name): void
    {
        unset($this->attributes[$name]);
    }

    /**
     * clear all context data
     * @return void
     */
    function clear(): void
    {
        $this->attributes = [];
    }
}