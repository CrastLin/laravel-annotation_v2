<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation;

use Crastlin\LaravelAnnotation\Annotation\Attributes\Autowired;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Env;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Inject;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Input;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Qualifier;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Service;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Value;
use Crastlin\LaravelAnnotation\Utils\Sync;

/**
 * @package Inject
 * @author crastlin@163.com
 * @date 2024-03-10
 * Injecting objects by binding data
 * @example InjectionAnnotation::bind("data", $data);
 * @using #[Inject("data")]
 * Automatically create and bind the instance when the injected object is a class address
 * @using #[Inject(\App\Library\Utils\Test::class)]
 */
class InjectionAnnotation
{
    static InjectionAnnotation $inject;

    /**
     * @var array $attributes inject container
     */
    protected array $attributes = [];

    protected array $injectAttributeObjectMap = [];


    function bind(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
        $this->refreshAllObject($name);
    }

    function offsetSet(string $name, mixed $value): void
    {
        $this->bind($name, $value);
    }

    function bindAndRefresh(object $object, string $name, mixed $value): void
    {
        $this->bind($name, $value);
        $this->injectWithObject($object);
    }

    function into(array $attributes, bool $recover = false): void
    {
        $this->attributes = $recover ? $attributes : array_merge($this->attributes, $attributes);
    }

    function bindAll(array $attributes, bool $recover = false): void
    {
        $this->into($attributes, $recover);
    }

    function take(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    function offsetGet(string $name): mixed
    {
        return $this->take($name);
    }


    function takeAll(): ?array
    {
        return $this->attributes;
    }

    function clearAll(): void
    {
        $this->attributes = [];
    }

    function takeAllToJson(): ?string
    {
        return json_encode($this->takeAll(), 256);
    }

    function unbind(string $name): void
    {
        if ($this->offsetExists($name))
            unset($this->attributes[$name]);
    }

    function offsetUnset(string $name): void
    {
        $this->unbind($name);
    }

    function exists(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    function offsetExists(string $name): bool
    {
        return $this->exists($name);
    }


    /**
     * 获取注入信息缓存
     * @param \ReflectionClass $reflect
     * @return array
     */
    protected function getInjectInformation(\ReflectionClass $reflect): array
    {
        $conf = config('annotation');
        $rootPath = $conf && !empty($conf['annotation_path']) ? $conf['annotation_path'] : 'data/';
        $rootPath = base_path("{$rootPath}inject/");
        $class = $reflect->getName();
        // get current class file modify time
        $classFile = $reflect->getFileName();
        $subList = explode('\\', $class);
        $name = array_pop($subList);
        $path = $rootPath . join('/', $subList) . '/';
        $hasPath = is_dir($path);
        $file = $path . $name . '.php';
        $hasFile = $hasPath && is_file($file);
        $injectData = $hasFile ? require $file : [];

        $mtime = (string)filemtime($classFile);
        $basePath = base_path();
        // get parent class file modify time
        if (empty($injectData['parents']) || (!empty($injectData['mtime']) && str_contains($injectData['mtime'], '-99'))) {
            $injectData['parents'] = [];
            // repeat get parent class information
            $repeatGetParentClass = function (\ReflectionClass $reflect) use (&$repeatGetParentClass, &$injectData, $basePath) {
                $parentClass = $reflect->getParentClass();
                if ($parentClass) {
                    $class = $parentClass->getFileName();
                    if (empty($class))
                        return;
                    $injectData['parents'][] = [
                        'file' => str_replace($basePath, '', $parentClass->getFileName()),
                        'class' => $parentClass->getName(),
                    ];
                    if ($parentReflect = $parentClass->getParentClass())
                        $repeatGetParentClass($parentReflect);
                }
            };
            $repeatGetParentClass($reflect);
        }
        if (!empty($injectData['parents'])) {
            foreach ($injectData['parents'] as $parent) {
                $parentFile = "{$basePath}{$parent['file']}";
                if (is_file($parentFile) && $st = filemtime($parentFile))
                    $mtime .= sprintf('-%d', $st);
                else {
                    $mtime .= '-99';
                }
            }
        }

        if (!$hasFile || empty($injectData['mtime']) || $injectData['mtime'] != $mtime) {
            $locker = null;
            try {
                if (!$hasPath)
                    mkdir($path, 0755, true);
                $annotations = $this->parseAnnotationByReflect($reflect);
                $injectData = array_merge($injectData, $annotations);
                $injectData['mtime'] = $mtime;
                $locker = Sync::create("sync_inject_cache:{$class}");
                if ($locker->lock()) {
                    file_put_contents($file, "<?php\r\n/**\r\n  * @author crastlin@163.com\r\n  * @date 2024-06-12\r\n  * @remaek Bind data to dependency injection container using Injection::bind(name, value),\r\n  * @remark Implementing automatic dependency injection using annotation #[Inject] \r\n*/\r\nreturn " . var_export($injectData, true) . ";");
                    $locker->unlock();
                }
            } catch (\Throwable $exception) {
                echo "file: " . $exception->getFile() . ' -> ' . $exception->getLine() . PHP_EOL;
                echo 'message: ' . $exception->getMessage();
                $locker && $locker->unlock();
                throw new $exception;
            }
        }
        return $injectData;
    }


    // parse annotation by reflect docComment
    protected function parseAnnotationByReflect(\ReflectionClass $reflectionClass): array
    {
        $targetList = [$reflectionClass->getProperties(), $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC)];
        $maps = [];
        foreach ($targetList as $targetReflect) {
            foreach ($targetReflect as $target) {
                $key = $target instanceof \ReflectionProperty ? 'properties' : 'methods';
                if (!isset($maps[$key]))
                    $maps[$key] = [];
                $attribute = null;
                if ($key == 'methods') {
                    $attrs = $target->getAttributes(Autowired::class);
                    $attribute = $attrs[0] ?? null;
                } else {
                    foreach ($target->getAttributes() as $attr) {
                        $attrName = $attr->getName();
                        if (in_array($attrName, [Value::class, Env::class, Inject::class, Autowired::class])) {
                            $attribute = $attr;
                            break;
                        }
                        if (str_starts_with($attrName, __NAMESPACE__ . '\\Attributes\\Input')) {
                            $attribute = $attr;
                            break;
                        }
                    }
                }
                if (!$attribute)
                    continue;
                $qualifier = null;
                if ($attribute->getName() == Autowired::class) {
                    $qualifierAttr = $target->getAttributes(Qualifier::class);
                    if (!empty($qualifierAttr[0])) {
                        $qualifierInstance = $qualifierAttr[0]->newInstance();
                        $qualifier = $qualifierInstance->implName ?? '';
                    }
                }
                $typeof = null;
                if ($key == 'properties') {
                    if (!isset($maps['public_properties']))
                        $maps['public_properties'] = [];
                    if ($target->isPublic())
                        $maps['public_properties'][] = $target->getName();
                    $propertyType = $target->getType();
                    $typeof = $propertyType instanceof \ReflectionNamedType && !$propertyType->isBuiltin() ? $propertyType->getName() : null;
                }

                $map = new \stdClass();
                $map->target = $target->getName();
                $map->annotation = $attribute->getName();
                $attr = $attribute->newInstance();
                $map->name = $attr->name ?? '';
                $map->parameters = $attr->parameters ?? [];
                if ($attribute->getName() == Input\All::class)
                    $map->keys = $attr->keys ?? [];
                $map->typeof = $typeof ?? 'mixed';
                $map->qualifier = $qualifier ?: '';
                $maps[$key][] = $map;
            }
        }
        return $maps;
    }


    // When binding a dependent object, synchronously update the objects that have already been bound to that object
    protected function refreshAllObject(string $bindName): void
    {
        if (empty($this->injectAttributeObjectMap) || !array_key_exists($bindName, $this->injectAttributeObjectMap))
            return;
        foreach ($this->injectAttributeObjectMap as $class) {
            $this->injectWithClass($class);
        }
    }


    /**
     * Implement interface dependency injection proxy mode
     * @param \ReflectionClass $reflectionClass
     * @return mixed|null
     */
    protected function searchImplementClass(\ReflectionClass $reflectionClass, \stdClass $property): ?object
    {
        $interfaceClass = $reflectionClass->getName();
        $implementClass = '';
        $implementClassName = '';
        $implementClassFile = '';
        $config = [];
        $path = '';
        $ref = null;
        if (!empty($property->qualifier) && str_contains($property->qualifier, '\\')) {
            if (!class_exists($property->qualifier))
                throw new AnnotationException("Qualifier Class {$property->qualifier} is not exists", 500);

            $ref = new \ReflectionClass($property->qualifier);
            if ($ref->getAttributes(Service::class)) {
                $implementClass = $property->qualifier;
                $pathSplitList = explode('\\', $property->qualifier);
                $implementClassName = array_pop($pathSplitList);
                $implementClassFile = $ref->getFileName();
            }
        } else {
            $path = Annotation::getAnnotationPath('proxies/implements', $config);
            $injectConfig = $config['inject'] ?? [];
            $scanImplPath = $injectConfig['impl_path'] ?? 'Impl';
            $is = explode('\\', $interfaceClass);
            array_pop($is);
            $namespace = join('\\', $is);

            $ps = explode('/', $reflectionClass->getFileName());
            array_pop($ps);
            $implPath = join('/', $ps) . '/' . $scanImplPath;
            if (!is_dir($implPath))
                return null;

            $scanList = scandir($implPath);
            foreach ($scanList as $file) {
                if (empty($file) || $file == '.' || $file == '..')
                    continue;
                if (!empty($property->qualifier) && $file != $property->qualifier)
                    continue;
                $classFile = "{$implPath}/{$file}";
                if (!is_file($classFile))
                    continue;
                $fileName = substr($file, 0, strpos($file, '.php'));
                $class = "{$namespace}\\{$scanImplPath}\\" . $fileName;
                if (!class_exists($class))
                    continue;
                $ref = $this->exists($class) ? $this->take($class) : new \ReflectionClass($class);
                if ($ref->implementsInterface($interfaceClass) && $ref->getAttributes(Service::class)) {
                    $implementClass = $class;
                    $implementClassName = $fileName;
                    $implementClassFile = $classFile;
                    break;
                }
            }
        }
        if (!$implementClass)
            return null;

        $path = $path ?: Annotation::getAnnotationPath('proxies/implements', $config);
        $mtime = filemtime($implementClassFile);
        if (!is_dir($path))
            mkdir($path, 0755, true);
        $proxyFile = "{$path}/{$implementClassName}.php";
        $hasFile = is_file($proxyFile);
        if ($hasFile && filemtime($proxyFile) >= $mtime)
            return require $proxyFile;

        if ($hasFile)
            @unlink($proxyFile);

        // Get constructor line parameter injection
        $implementInjectParams = [];
        if ($constructor = $ref->getConstructor())
            Annotation::handleInvokeAnnotation($implementClass, $constructor, [], $implementInjectParams, true, true);

        // make a proxy class extend implement file
        $methods = $reflectionClass->getMethods();
        $methodContent = '';
        $getInstanceVar = '$this->getInstance()';
        foreach ($methods as $method) {
            $parameters = $method->getParameters();
            $parameterContentList = [];
            $putParametersContentList = [];
            foreach ($parameters as $parameter) {
                $parameterContent = '$' . $parameter->getName();
                $putParametersContent = $parameterContent;
                if ($parameter->isDefaultValueAvailable()) {
                    $value = $parameter->getDefaultValue();
                    $valueStr = is_null($value) ? 'null' : ($value == '' ? "''" : $value);
                    var_dump($valueStr);
                    $parameterContent .= ' = ' . var_export($valueStr, true);
                }
                $parameterContentList[] = $parameterContent;
                $putParametersContentList[] = $putParametersContent;
            }
            $parameterContent = join(', ', $parameterContentList);
            $methodName = $method->getName();
            $returnType = $method->hasReturnType() ? ' : ' . $method->getReturnType()->getName() : '';
            $putParametersContent = !empty($putParametersContentList) ? ', ' . join(', ', $putParametersContentList) . '' : '';
            $methodContent .= "function {$methodName}({$parameterContent}){$returnType}\r\n{\r\nreturn {$getInstanceVar}('{$methodName}'{$putParametersContent});\r\n}";
        }
        $implementClassVar = '$implementClass';
        $thisImplementClassVar = '$this->implementClass';
        $implementInstanceVar = '$implementInstance';
        $thisImplementInstance = '$this->implementInstance';
        $method = '$method';
        $arguments = '$arguments';
        $implementInjectParamsVars = var_export($implementInjectParams, true);
        $proxyFileContent = <<<php
<?php
use Crastlin\LaravelAnnotation\Facades\Injection;
 return new class implements \\{$interfaceClass} {
protected string {$implementClassVar};
protected \\{$implementClass} {$implementInstanceVar};
function setImplementClass(string {$implementClassVar}):void
{
{$thisImplementClassVar} = {$implementClassVar};
}
protected function getInstance():\\{$implementClass}
{
if(!empty($thisImplementInstance))
return $thisImplementInstance;
// inject constructor

$thisImplementInstance = new \\{$implementClass}(...$implementInjectParamsVars);

Injection::injectWithObject($thisImplementInstance);
return $thisImplementInstance;
}
{$methodContent}
function __call(string {$method}, array {$arguments})
{
   return $getInstanceVar({$method}, ...{$arguments});
}
};
php;

        file_put_contents($proxyFile, $proxyFileContent);

        return require $proxyFile;
    }


    /**
     * Obtain injection values based on type
     * @param string $class
     * @param \stdClass $property
     * @return mixed
     * @throws \Throwable
     */
    function getInjectTypeofValue(string $class, \stdClass $property, bool $bindInjectMap = true): mixed
    {
        if (!empty($property->annotation) && (in_array($property->annotation, [Value::class, Env::class]) || str_starts_with($property->annotation, __NAMESPACE__ . '\\Attributes\\Input'))) {
            switch ($property->annotation) {
                case Value::class:
                    $value = config("{$property->name}");
                    break;
                case Env::class:
                    $value = env("{$property->name}");
                    break;
                case Input::class:
                    $value = request()?->input($property->name);
                    break;
                case Input\All::class:
                    $value = request()->all($property->keys ?? []);
                    break;
                case Input\Collect::class:
                    $value = request()->collect($property->name ?? null);
                    break;
                case Input\Get::class:
                    $value = request()->get($property->name);
                    break;
                case Input\Post::class:
                    $value = request()->post($property->name);
                    break;
                case Input\Header::class:
                    $value = request()->header($property->name);
                    break;
                case Input\Query::class:
                    $value = request()->query($property->name);
                    break;
                case Input\Json::class:
                    $value = request()->json($property->name);
                    break;
            }
            return $value ?? '';
        }
        $injectClass = '';
        if (empty($property->name) && !empty($property->typeof) && str_contains($property->typeof, '\\'))
            $injectClass = $property->typeof;
        elseif (!empty($property->name) && str_contains($property->name, '\\'))
            $injectClass = $property->name;

        if (!empty($injectClass) && (class_exists($injectClass) || interface_exists($injectClass))) {
            $bindName = $injectClass;
            if (!empty($property->qualifier))
                $bindName .= ":{$property->qualifier}";
            if (!$this->exists($bindName)) {
                // search a class when it implements interface
                $ref = new \ReflectionClass($injectClass);
                // create proxy class for inject
                if ($ref->isInterface()) {
                    $value = $this->searchImplementClass($ref, $property);
                    $this->bind($bindName, $value);
                } else {
                    if (!$ref->isInstantiable())
                        throw new \Exception("Class {$class} <- {$injectClass} is cannot be initialized");

                    $constructor = $ref->getConstructor();
                    $property->parameters = $property->parameters ?? [];
                    Annotation::handleInvokeAnnotation($class, $constructor, [], $property->parameters, true, true);
                    $value = $ref->newInstance(...$property->parameters);
                    $this->injectWithObject($value);
                    $this->bind($bindName, $value);
                }
            } else {
                $value = $this->take($bindName);
            }
        } else {
            $bindName = !empty($property->name) ? $property->name : $property->target;
            if (isset($property->defaultValue))
                $default = isset($property->defaultValue);
            else {
                $default = match ($property->typeof) {
                    "string" => '',
                    "int", "float" => 0,
                    "array" => [],
                    "bool" => false,
                    default => null,
                };
            }
            if ($this->exists($bindName)) {
                $value = $this->take($bindName);
            } else {
                $value = request()->input($bindName, $default);
            }
        }

        if ($bindInjectMap) {
            if (!array_key_exists($bindName, $this->injectAttributeObjectMap))
                $this->injectAttributeObjectMap[$bindName] = [];
            if (!in_array($class, $this->injectAttributeObjectMap[$bindName]))
                $this->injectAttributeObjectMap[$bindName][] = $class;
        }
        return $value;
    }


    function takeByParameter(\ReflectionParameter $parameter, object $annotation, string $action)
    {
        $parameterType = $parameter->getType();
        $std = new \stdClass();
        $std->target = $parameter->getName();
        $std->name = !empty($annotation->name) ? $annotation->name : '';
        $std->parameters = !empty($annotation->parameters) ? $annotation->parameters : [];
        $std->typeof = $parameterType instanceof \ReflectionNamedType && !$parameterType->isBuiltin() ? $parameterType->getName() : null;
        $std->defaultValue = $parameter->getDefaultValue();
        return $this->getInjectTypeofValue($action, $std, false);
    }

    /**
     * Automatic dependency injection of properties and methods for specified class instances
     * @param string $class
     * @param $object
     * @return void
     * @throws \ReflectionException
     * @throws \Throwable
     */
    function autoInject(string $class, &$object = null, ?\ReflectionClass $reflect = null): void
    {
        if (!$reflect) {
            $reflect = new \ReflectionClass($class);
            $this->bind("reflect.{$class}", $reflect);
        }
        if ($reflect->isAnonymous())
            throw new AnnotationException("Anonymous class: {$reflect->getName()} currently do not support dependency injection");
        $object = $object ?: $reflect->newInstance();
        $propertySetMethodType = method_exists($object, 'setProperty') ? 'setProperty' : (method_exists($object, '__set') ? 'set' : '');

        $propertiesCache = $this->getInjectInformation($reflect);

        // inject all properties
        if (!empty($propertiesCache['properties'])) {
            foreach ($propertiesCache['properties'] as $property) {
                $propertyName = $property->target;
                $value = $this->getInjectTypeofValue($class, $property);
                $propertySetter = 'set' . ucfirst($propertyName);
                if ($propertySetMethodType) {
                    switch ($propertySetMethodType) {
                        // using setProperty action
                        case 'setProperty':
                            $object->setProperty($propertyName, $value);
                            break;
                        // using __set magic action
                        case 'set':
                            $object->{$propertyName} = $value;
                            break;
                    }
                } else {
                    switch (true) {
                        // when permission of property is public
                        case !empty($propertiesCache['public_properties']) && in_array($propertiesCache['public_properties'], $propertyName):
                            $object->{$propertyName} = $value;
                            break;
                        // using setter action
                        case method_exists($object, $propertySetter):
                            $object->{$propertySetter}($value);
                            break;
                        default:
                            throw new \Exception("Class {$class} property::{$propertyName} dependency injection must be configured with non private or defined with methods __ set or setProperty");
                    }
                }
            }
        }

        // inject all methods
        if (!empty($propertiesCache['methods'])) {
            foreach ($propertiesCache['methods'] as $method) {
                $ref = $reflect->getMethod($method->target);
                $arguments = [];
                Annotation::handleInvokeAnnotation($class, $ref, [], $arguments, true, true);
                call_user_func_array([$object, $method->target], ...$arguments);
            }
        }
    }

    // inject by object
    function injectWithObject($object, ?\ReflectionClass $reflectionClass = null): void
    {
        $this->autoInject(get_class($object), $object, $reflectionClass);
    }

    // inject by class namesapce
    function injectWithClass(string $class)
    {
        if (!class_exists($class))
            throw new \Exception("Class {$class} is not exists");
        $object = null;
        $this->autoInject($class, $object);
        return $object;
    }

}
