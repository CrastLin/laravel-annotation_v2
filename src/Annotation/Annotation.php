<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation;

use Crastlin\LaravelAnnotation\Annotation\Attributes\Autowired;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Controller;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Inject;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Route\ApiResourceMapping;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Route\ResourceMapping;
use Crastlin\LaravelAnnotation\Extra\ResponseCode;
use Crastlin\LaravelAnnotation\Facades\Injection;
use Crastlin\LaravelAnnotation\Utils\TurnBack;
use Illuminate\Console\Command;
use ReflectionClass;

/**
 * Annotation extension base class
 * @Author crastlin@163.com
 * @date 2024-1-8
 *
 * @description Used to implement multi class annotation functionality and achieve agile development
 * @description Annotations include routing, middleware, routing groups, permission menu nodes, dependency injection, data validation, etc
 */
abstract class Annotation implements Annotator
{
    protected ReflectionClass $reflectClass;

    protected array $config;

    protected string $filePath;

    protected string $basePath;

    protected ?Command $command;


    public function __construct(string $class = '', ?array $config = [])
    {
        if (!empty($class)) {
            if (!class_exists($class))
                throw new AnnotationException("Class: {$class} is not exists");
            $this->reflectClass = new ReflectionClass($class);
        }
        $this->setConfig($config);
    }

    function setReflectClass(ReflectionClass $reflectionClass): static
    {
        $this->reflectClass = $reflectionClass;
        return $this;
    }

    function setConfig(array $config): static
    {
        $this->config = $config;
        $this->filePath = base_path(!empty($config['annotation_path']) ? trim($config['annotation_path'], '/') . '/' : 'data/');
        $parseClassStrList = explode('\\', static::class);
        $parseClass = array_pop($parseClassStrList);
        $this->basePath = $this->filePath . self::humpToUnderline(substr($parseClass, 0, -10)) . 's/';
        $this->init();
        return $this;
    }

    function setCommand(?Command $command = null): static
    {
        $this->command = $command;
        return $this;
    }

    protected function init(): void
    {
    }

    static function humpToUnderline(string $string, ?bool $toUpper = false): string
    {
        $string = preg_replace('/(?<=[a-z0-9])([A-Z])/', '_${1}', $string);
        return $toUpper ? strtoupper($string) : strtolower($string);
    }

    protected function getController($leftSuffix = 'Controller'): array
    {
        $ctClass = explode('\\', $this->reflectClass->getName());
        $controller = array_pop($ctClass);
        $ct = $controller == 'Controller' ? $controller : (str_contains($controller, $leftSuffix) ? substr($controller, 0, -strlen($leftSuffix)) : $controller);
        return [$controller, $ct];
    }


    static function getAnnotationPath(string $basePath = 'routes', ?array &$config = []): string
    {
        $config = $config ?: config('annotation');
        $filePath = !empty($config['annotation_path']) ? rtrim($config['annotation_path'], '/') : 'data';
        return base_path($filePath . '/' . ltrim($basePath, '/'));
    }

    static function getAnnotationCache(string $fileName, string $basePath = 'routes', ?array &$config = []): ?array
    {
        $routeBasePath = self::getAnnotationPath($basePath, $config);
        $file = "{$routeBasePath}/{$fileName}.php";
        return is_file($file) ? require $file : null;
    }

    static function handleInvokeAnnotation(string $class, \ReflectionMethod $method, ?array $data, ?array &$arguments = [], bool $onlyRunParameters = false, bool $isOnlyInjectMode = false): TurnBack
    {
        $interceptorAnnotation = new \stdClass();
        $interceptor = new InterceptorAnnotation();
        $methodName = $method->getName();
        $action = "{$class}@{$methodName}";
        $map = new \stdClass();
        $map->module = 'general_locker';
        $map->controller = str_replace('\\', ':', $class);
        $map->action = $methodName;
        if (!$isOnlyInjectMode)
            InterceptorAnnotation::matchInterceptors($method, $interceptorAnnotation, $map);
        if (!$onlyRunParameters) {
            $turnBack = $interceptor->handle($action, $data, null, $interceptorAnnotation);
            if (!in_array($turnBack->code, [ResponseCode::PASSED, ResponseCode::SUCCESS]))
                return $turnBack;
        }

        $parameters = $method->getParameters();
        $hasParameterValidation = !empty($interceptorAnnotation->parameterValidation);
        foreach ($parameters as $k => $parameter) {
            $name = $parameter->getName();
            // Parameter dependency injection
            $attributes = $parameter->getAttributes();
            $argument = $arguments[$k] ?? null;
            if (is_null($argument)) {
                $injectAnnotation = null;
                foreach ($attributes as $attribute) {
                    $an = $attribute->getName();
                    if ($an == Autowired::class) {
                        $injectAnnotation = new \stdClass();
                    } elseif ($an == Inject::class) {
                        $injectAnnotation = $attribute->newInstance();
                    }
                    if (!$injectAnnotation)
                        continue;
                    $arguments[$k] = Injection::takeByParameter($parameter, $injectAnnotation, $action);
                    break;
                }
            }

            if (!$isOnlyInjectMode) {
                // Execute the current method parameter annotation validator
                $validationList = $hasParameterValidation && !empty($interceptorAnnotation->parameterValidation[$name]) ? $interceptorAnnotation->parameterValidation[$name] : [];
                if (!empty($validationList)) {
                    if ($errText = $interceptor->runValidation($validationList, is_array($argument) ? $argument : [$name => $argument]))
                        return TurnBack::intoResult(ResponseCode::PARAMETER_ERROR, $errText);
                }
            }
        }

        return TurnBack::intoResult(ResponseCode::SUCCESS, 'success');
    }

    /**
     * builder from annotation
     * @param mixed $parameters
     * @return array
     */
    abstract protected function analysis(mixed ...$parameters): array;


    /**
     * console message by command
     * @param string $msg
     * @param string $flag
     * @return void
     */
    protected function console(string $msg, string $flag = ''): void
    {
        if ($this->command && in_array($flag, ['info', 'warn', 'error']))
            call_user_func_array([$this->command, $flag], [$msg]);
    }


    /**
     * scan all appoint modules directories
     * @param callable|array<string|object> $parseGenerator
     * @param array|null $config
     * @return array
     * @throws \Throwable
     */
    static function scanAnnotation(callable|array $parseGenerator, ?array $config = null, ?Command $command = null): array
    {
        $config = $config ?: config('annotation');
        $basePath = !empty($config['route']['path']) ? $config['route']['path'] : 'app/Http/Controllers';
        $appointModules = !empty($config['route']['modules']) ? $config['route']['modules'] : ['*'];
        $baseNamespace = !empty($config['route']['namespace']) ? $config['route']['namespace'] : 'App\\Http\\Controllers';
        $analysisResult = [];
        $modules = [];
        $repeatScan = function (string $scanPath, string $namespace, string $rootPath = '') use (&$repeatScan, &$appointModules, &$parseGenerator, &$config, &$analysisResult, $command, &$modules): void {
            $scanControllerList = scandir($scanPath);
            foreach ($scanControllerList as $scanController) {
                if (empty($scanController) || $scanController == '.' || $scanController == '..')
                    continue;
                $file = "{$scanPath}/{$scanController}";

                // When it is currently a directory, recursively scan
                if (is_dir($file)) {
                    $nextNamespace = "{$namespace}\\{$scanController}";
                    if (in_array('*', $appointModules) || in_array($scanController, $appointModules))
                        $repeatScan($file, $nextNamespace, $scanController);
                    continue;
                }

                $class = "{$namespace}\\" . substr($scanController, 0, strpos($scanController, '.php'));
                if (!class_exists($class))
                    throw new AnnotationException("Class: {$class} is not defined");
                $reflect = new ReflectionClass($class);
                // Filter classes with unmarked controller annotations
                if (
                    !$reflect->getAttributes(Controller::class) &&
                    !$reflect->getAttributes(ResourceMapping::class) &&
                    !$reflect->getAttributes(ApiResourceMapping::class)
                )
                    continue;

                $rootPath = $rootPath ?: 'Single';
                if (empty($modules) || !in_array($rootPath, $modules))
                    $modules[] = $rootPath;
                // When is callable of Parser
                if (is_callable($parseGenerator)) {
                    $parseGenerator($reflect, $rootPath);
                    continue;
                }
                // When is ArrayList of Parser
                if (!empty($parseGenerator)) {
                    foreach ($parseGenerator as $annotationParser) {
                        if (is_string($annotationParser)) {
                            if (!class_exists($annotationParser))
                                throw new AnnotationException("Parse of Annotation: {$annotationParser} is not exists");
                            $parser = new $annotationParser();
                            $parseClass = $annotationParser;
                        } else {
                            $parser = $annotationParser;
                            $parseClass = get_class($annotationParser);
                        }
                        if (!$parser instanceof Annotation) {
                            throw new AnnotationException("Class: {$parseClass} must instanceof Annotation");
                        }
                        $result = $parser->setReflectClass($reflect)
                            ->setConfig($config)
                            ->setCommand($command)
                            ->analysis($rootPath);

                        // When the controller is a resource router
                        $classic = $result['classic'] ?? 'general';
                        $module = $classic == 'resource' ? 'ResourceMapping' : $rootPath;
                        if (empty($modules) || !in_array($module, $modules))
                            $modules[] = $module;
                        if (!isset($analysisResult[$parseClass]))
                            $analysisResult[$parseClass] = [
                                'path' => $result['path'] ?? base_path('data/'),
                                'list' => [],
                            ];
                        $analysisResult[$parseClass]['list'][$module][$class] = $result;
                    }
                }
            }
        };
        // Recursive analysis of all annotations
        $repeatScan(base_path($basePath), $baseNamespace);

        // build all result of analysis
        if (empty($analysisResult))
            return $modules;

        foreach ($analysisResult as $parseClass => $analysis) {
            if (class_exists($parseClass) && method_exists($parseClass, 'build'))
                $parseClass::build($analysis['list'], $analysis['path']);
        }
        return $modules;
    }
}
