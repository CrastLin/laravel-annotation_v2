<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Utils\Traits;

use Crastlin\LaravelAnnotation\Annotation\Annotation;
use Crastlin\LaravelAnnotation\Annotation\AnnotationException;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Inject;
use Crastlin\LaravelAnnotation\Extra\ResponseCode;
use Crastlin\LaravelAnnotation\Extra\ResponseCodeEnum;
use Crastlin\LaravelAnnotation\Facades\Injection;
use ReflectionClass;

trait InvokeTrait
{
    use SetterTrait;

    protected ?\ReflectionClass $reflectClass;

    #[Inject('params')]
    protected array $data = [];

    protected ResponseCodeEnum $resCode = ResponseCode::PARAMETER_ERROR;

    protected string $errText = '', $notice = '';

    protected array $result = [];

    function getResCode(): int
    {
        return $this->resCode->value;
    }

    function getError(): string
    {
        return $this->errText ?: '';
    }

    function getNotice(): string
    {
        return $this->notice ?: '';
    }

    function getResult(): array
    {
        return $this->result ?: [];
    }


    static function newInstanceByParent(string $name = '', ...$params): static
    {
        $baseNameSpace = explode('\\', static::class);
        array_pop($baseNameSpace);
        $baseNameSpace = join('\\', $baseNameSpace);
        $name = $name ? (str_contains($name, '\\') ? $name : $baseNameSpace . '\\' . $name) : static::class;
        if (!class_exists($name))
            throw new AnnotationException("class: {$name} is not exists", 601);
        $reflectClass = Injection::exists("reflect.{$name}") ? Injection::take("reflect.{$name}") : new ReflectionClass($name);
        // inject constructor
        if ($constructor = $reflectClass->getConstructor())
            Annotation::handleInvokeAnnotation($name, $constructor, [], $params, true, true);
        $instance = new $name(...$params);
        Injection::injectWithObject($instance, $reflectClass);
        return $instance;
    }

    static function newInstance(...$params): static
    {
        return static::newInstanceByParent('', ...$params);
    }


    public function __invoke(string $method, ...$arguments)
    {
        $class = static::class;
        if (!method_exists($this, $method))
            throw new \Exception("Class {$class}::{$method} is not exists");
        $this->reflectClass = Injection::exists("reflect.{$class}") ? Injection::take("reflect.{$class}") : new \ReflectionClass($class);;
        $ref = $this->reflectClass->getMethod($method);
        if (!$ref || !$ref->isPublic() || $ref->isAbstract())
            throw new \Exception("Class {$class}::{$method} Cannot be accessed");
        $turnBack = Annotation::handleInvokeAnnotation($class, $ref, $this->data, $arguments);
        if ($turnBack->code != ResponseCode::SUCCESS) {
            $this->resCode = $turnBack->code;
            $this->errText = $turnBack->message;
            return false;
        }
        return call_user_func([$this, $method], ...$arguments);
    }
}
