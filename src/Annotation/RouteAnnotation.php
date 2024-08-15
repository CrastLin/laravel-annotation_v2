<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation;

use Crastlin\LaravelAnnotation\Annotation\Attributes\Route;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Route\Domain;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Route\Group;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Route\Middleware;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Route\Prefix;
use Crastlin\LaravelAnnotation\Enum\Constraint;
use Crastlin\LaravelAnnotation\Enum\Method;
use Crastlin\LaravelAnnotation\Enum\ResourceEnum;
use ReflectionAttribute;
use stdClass;

/**
 * Routing class annotation parsing class
 * @author crastlin@163.com
 * @date 2024-1-8
 */
class RouteAnnotation extends Annotation
{

    /**
     * check route exists
     * @param string $path the request path string
     * @param string $mapBasePath the base path of map file cache
     * @param int $varCount
     * @return bool
     */
    static function exists(string $path, string $mapBasePath, ?array $mapRecords = null, ?array $resources = null, int $varCount = 0, &$datum = []): bool
    {
        if (is_null($mapRecords)) {
            $mapFile = "{$mapBasePath}/map.php";
            $mapRecords = is_file($mapFile) ? require $mapFile : [];
            if (empty($mapRecords))
                return false;
            $cacheFile = "{$mapBasePath}/cache.php";
            $cache = is_file($cacheFile) ? require $cacheFile : [];
            $resources = $cache['resources'] ?? [];
        }
        if (!empty($resources) && in_array($path, $resources))
            return true;

        if (!empty($mapRecords) && array_key_exists($path, $mapRecords)) {
            foreach ($mapRecords[$path] as $cs => $map):
                if (!str_contains($cs, '-'))
                    break;
                list($minCount, $maxCount) = explode('-', $cs);
                if ($varCount > 0 && ($varCount < $minCount || $varCount > $maxCount))
                    continue;
                if (empty($map['name']))
                    break;
                $datum = $map;
                $actionList = explode('@', $map['name']);
                if (count($actionList) == 2) {
                    $controller = $actionList[0];
                    if (class_exists($controller) && method_exists($controller, $actionList[1])) {
                        // check modify time
                        if (!empty($map['mtime'])) {
                            $reflect = new \ReflectionClass($controller);
                            $mtime = filemtime($reflect->getFileName());
                            if ($map['mtime'] == $mtime)
                                return true;
                        }
                    }
                }
                break;
            endforeach;
            return false;
        }
        // repeat check base path
        $pathList = explode('/', $path);
        if (count($pathList) > 1) {
            array_pop($pathList);
            return self::exists(join('/', $pathList), $mapBasePath, $mapRecords, $resources, ++$varCount, $datum);
        }
        return false;
    }


    // Resolving resource routing
    protected function matchResourceRoute(\ReflectionAttribute $classAttribute, \stdClass $resource): bool
    {
        if (!in_array($classAttribute->getName(), [Route\ResourceMapping::class, Route\ApiResourceMapping::class]))
            return false;
        $ts = explode('\\', $classAttribute->getName());
        $annotation = array_pop($ts);
        $resource->annotation = str_ends_with($annotation, 'Mapping') ? substr($annotation, 0, -7) : $annotation;
        $resource->method = $classAttribute->getName() == Route\ApiResourceMapping::class ? 'api' : 'general';
        $annotation = $classAttribute->newInstance();
        $resource->path = !empty($annotation->path) ? ltrim($annotation->path, '/') : $resource->ct;
        if (!empty($annotation->only) && ResourceEnum::isMatchedAll($annotation->only))
            $resource->only = $annotation->only;
        if (!empty($annotation->except) && ResourceEnum::isMatchedAll($annotation->except))
            $resource->except = $annotation->except;
        if (!empty($annotation->names))
            $resource->names = $annotation->names;
        if (!empty($annotation->parameters))
            $resource->parameters = $annotation->parameters;
        if (!empty($annotation->scoped))
            $resource->scoped = $annotation->scoped;
        $resource->isShallow = str_contains($resource->path, '.') && $annotation->isShallow;
        if (!empty($annotation->missingRedirect))
            $resource->missingRedirect = $annotation->missingRedirect;
        return true;
    }


    // Analyzing Routing Group Configuration
    protected function matchRouteGroup(\ReflectionAttribute $classAttribute, \stdClass $map): bool
    {
        if (!in_array($classAttribute->getName(), [Group::class, Prefix::class, Domain::class, Middleware::class]))
            return false;
        switch ($classAttribute->getName()) {
            case Group::class:
                $annotation = $classAttribute->newInstance();
                $map->domain = $annotation->domain ?: $map->domain;
                $map->prefix = $annotation->prefix ?: $map->prefix;
                $map->middleware = $annotation->middleware ? (is_array($annotation->middleware) ? $annotation->middleware : [$annotation->middleware]) : $map->middleware;
                break;
            case Prefix::class:
                if (!$map->prefix) {
                    $annotation = $classAttribute->newInstance();
                    $map->prefix = $annotation->prefix;
                }
                break;
            case Domain::class:
                if (!$map->domain) {
                    $annotation = $classAttribute->newInstance();
                    $map->domain = $annotation->domain;
                }
                break;
            case Middleware::class:
                if (!$map->middleware) {
                    $annotation = $classAttribute->newInstance();
                    $map->middleware = $annotation->middleware ? (is_array($annotation->middleware) ? $annotation->middleware : [$annotation->middleware]) : $map->middleware;
                }
                break;
        }
        return true;
    }


    /**
     * Match all route annotations
     *
     * @param stdClass $std
     * @param array<ReflectionAttribute> $attributes
     * @return bool
     */
    protected function matchAllRoute(stdClass $std, array $attributes, string $module, ?stdClass $map = null): bool
    {
        $std->domain = '';
        $std->prefix = '';
        $std->middleware = [];
        $std->method = '';
        $std->methods = [];
        $std->path = '';
        $std->name = '';
        $std->pattern = '';
        $std->keys = [];
        $std->values = [];
        foreach ($attributes as $attribute) {
            $class = $attribute->getName();
            $classStrList = explode('\\', $class);
            $routeAnnotation = array_pop($classStrList);
            if ($class == Route::class || (join('\\', $classStrList) == __NAMESPACE__ . '\\Attributes\\Route' && !str_contains($routeAnnotation, 'ResourceMapping'))) {
                if (!$this->matchRouteGroup($attribute, $std)) {
                    if (!empty($std->path))
                        continue;
                    $annotation = $attribute->newInstance();
                    if (!empty($annotation->methods))
                        $std->methods = $annotation->methods;
                    $std->path = !empty($annotation->path) ? $annotation->path : "{$map->ct}/{$std->action}";
                    $std->name = !empty($annotation->name) ? $annotation->name : "{$module}.{$map->ct}.{$std->action}";
                    if (!empty($annotation->method))
                        $std->method = $annotation->method;
                    if (!empty($annotation->where))
                        $std->where = $annotation->where;
                    if (!empty($annotation->pattern))
                        $std->pattern = $annotation->pattern;
                    if (!empty($annotation->keys))
                        $std->keys = $annotation->keys;
                    if (!empty($annotation->values))
                        $std->values = $annotation->values;
                }
            }
        }
        if (!empty($std->path))
            $map->routeList[] = $std;
        return !empty($std->path);
    }

    // Analyze routing annotations
    protected function analysis(mixed ...$parameters): array
    {
        $module = !empty($parameters[0]) ? $parameters[0] : 'Single';
        $rootGroup = $this->config['route']['root_group'] ?? [];
        $moduleGroup = $rootGroup[$module] ?? [];

        $classAttributes = $this->reflectClass->getAttributes();
        $map = new stdClass();
        $map->module = $module;
        [$map->controller, $map->ct] = $this->getController();
        $map->mtime = filemtime($this->reflectClass->getFileName());
        $map->domain = $moduleGroup['domain'] ?? '';
        $map->prefix = $moduleGroup['prefix'] ?? '';
        $map->middleware = $moduleGroup['middleware'] ?? [];
        $map->classic = 'general';
        $map->routeList = [];

        // match single action route
        $single = new stdClass();
        $single->ct = $map->ct;
        $single->action = 'invoke';
        if ($this->matchAllRoute($single, $classAttributes, $module, $map)) {
            $map->classic = 'single';
            return [
                'module' => $module,
                'path' => $this->basePath,
                'data' => $map,
                'classic' => 'single',
            ];
        }

        // match resource route
        $resource = new stdClass();
        $resource->ct = $map->ct;
        $resource->classic = 'resource';
        $resource->mtime = $map->mtime;
        // Get class annotations for the current class
        foreach ($classAttributes as $classAttribute) {
            if ($this->matchResourceRoute($classAttribute, $resource))
                break;
            $this->matchRouteGroup($classAttribute, $map);
        }

        if (!empty($resource->path)) {
            return [
                'module' => $module,
                'path' => $this->basePath,
                'data' => $resource,
                'classic' => 'resource',
            ];
        }

        // Get class annotations for the current class
        foreach ($this->reflectClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes();
            $std = new \stdClass();
            $std->action = $method->getName();
            // match interceptors
            InterceptorAnnotation::matchInterceptors($method, $std, $map);
            // match all routes
            $this->matchAllRoute($std, $attributes, $module, $map);
        }
        return [
            'module' => $module,
            'path' => $this->basePath,
            'data' => $map,
            'classic' => 'general',
        ];
    }


    /**
     * Resolve routing paths
     *
     * @param string $path
     * @return array
     */
    static function parseRoutingPath(string $path): array
    {
        $minCount = $maxCount = 0;
        $vars = '';
        $keys = [];
        if (!empty($path) && preg_match_all('~(\{[a-z]+[\w?]*})~', $path, $matches)) {
            $path = str_replace($matches[0], '', $path);
            $pathStringList = explode('/', $path);
            $optCount = 0;
            foreach ($matches[0] as $k => $value) {
                $keys[] = str_replace(['{', '}', '?'], '', $value);
                if (str_contains($value, '?'))
                    ++$optCount;
            }
            $path = join('/', array_filter($pathStringList));
            $maxCount = count($matches[0]);
            $vars = join(',', $matches[0]);
            $minCount = $maxCount - $optCount;
        }
        return [$path, $minCount, $maxCount, $vars, $keys];
    }

    /**
     * Create routing configuration
     *
     * @param array $analysisResult
     * @param string $savePath
     * @return void
     * @throws AnnotationException
     */
    static function build(array $analysisResult, string $savePath): void
    {
        // Build routes by all of Controllers
        $builderTime = date('Y-m-d');
        $baseRouteContent = "<?php\r\n /**\r\n * Generate all controller routes for the current group \r\n * @Author crastlin@163.com\r\n * @Date {$builderTime}\r\n * @description This routing file is generated by routing annotations with one click. \r\n * @description It is recommended to automatically enable generation in the development environment. \r\n * @description Considering improving performance in production environments, it is recommended to use one click command line generation. \r\n * @using sudo -u {user}.{group} php artisan annotation:route \r\n */\r\n";
        $mapSet = [];
        $modules = [];
        $prefixes = [];
        $resources = [];
        $interceptors = [];
        foreach ($analysisResult as $module => $saveCache) {
            $modules[] = $module;
            $routeContent = "{$baseRouteContent}\r\n";
            $module == 'ResourceMapping' ? self::generateResourceRoutes($module, $saveCache, $routeContent, $mapSet, $prefixes, $resources) : self::generateGeneralRoutes($module, $saveCache, $routeContent, $mapSet, $prefixes, $interceptors);
        }

        // save route cache status
        if (!is_dir($savePath))
            mkdir($savePath, 0755, true);
        $saveFile = "{$savePath}map.php";
        file_put_contents($saveFile, "<?php\r\nreturn " . var_export($mapSet, true) . ';');
        // save modules cache
        $cache = ['modules' => $modules, 'prefixes' => $prefixes, 'resources' => $resources];
        $cacheFile = "{$savePath}cache.php";
        file_put_contents($cacheFile, "<?php\r\nreturn " . var_export($cache, true) . ';');
        $interceptorFile = "{$savePath}interceptor.php";
        file_put_contents($interceptorFile, "<?php\r\nreturn " . var_export($interceptors, true) . ';');
    }

    // Generate ResourceMapping routing configuration file through operation
    protected static function generateResourceRoutes(
        string $module,
        array  $saveCache,
        string $commonRouteContent,
        array  &$mapSet = [],
        array  &$prefixes = [],
        array  &$resources = []
    ): void
    {
        $routeContent = '';
        $path = '';
        foreach ($saveCache as $class => $result) {
            list($group, $path) = [
                $result['data'] ?? new \stdClass(),
                !empty($path) ? $path : ($result['path'] ?? base_path('data/routes/')),
            ];
            if (empty($group->path))
                continue;
            $routeContent .= "// ResourceMapping routing generation for controller {$class}\r\nRoute::";
            $resources[] = $group->path;
            if (!empty($group->domain)) {
                $routeContent .= "domain('{$group->domain}')\r\n->";
            }
            if (!empty($group->prefix)) {
                $prefix = rtrim($group->prefix, '/');
                $prefixes[] = $prefix;
                $routeContent .= "prefix('{$prefix}')\r\n->";
            }
            if (!empty($group->middleware)) {
                $routeContent .= 'middleware(' . (is_array($group->middleware) ? var_export($group->middleware, true) : "'{$group->middleware}'") . ")\r\n->";
            }
            $resourceMethod = !empty($group->annotation) ? lcfirst($group->annotation) : 'resource';
            $routeContent .= "{$resourceMethod}('{$group->path}', {$class}::class)";
            if (!empty($group->only)) {
                $routeContent .= "\r\n->only(" . var_export(ResourceEnum::getValuesByEnums($group->only), true) . ")";
            }
            if (!empty($group->except)) {
                $routeContent .= "\r\n->except(" . var_export(ResourceEnum::getValuesByEnums($group->except), true) . ")";
            }

            if (!empty($group->names))
                $routeContent .= "\r\n->names(" . var_export($group->names, true) . ")";

            if (!empty($group->parameters))
                $routeContent .= "\r\n->parameters(" . var_export($group->parameters, true) . ")";

            if (!empty($group->scoped))
                $routeContent .= "\r\n->scoped(" . var_export($group->scoped, true) . ")";

            if (!empty($group->isShallow) && str_contains($group->path, '.'))
                $routeContent .= "\r\n->shallow()";

            if (!empty($group->missingRedirect)) {
                $routeContent .= "\r\n->missing(function (){\r\n\t\t\treturn Redirect::route('{$group->missingRedirect}');\r\n})";
            }
            $routeContent .= ";\r\n";

            $mapSet[$group->path]['0-0'] = [
                'name' => $class,
                'mtime' => $group->mtime,
                'vars' => [],
            ];
        }
        if (!is_dir("{$path}{$module}"))
            mkdir("{$path}{$module}", 0755, true);
        file_put_contents("{$path}{$module}/resource.php", "{$commonRouteContent}\r\n{$routeContent}");
    }


    // Generate General routing configuration file through operation
    protected static function generateGeneralRoutes(
        string $module,
        array  $saveCache,
        string $commonRouteContent,
        array  &$mapSet = [],
        array  &$prefixes = [],
        array  &$interceptors = []
    ): void
    {
        $path = '';
        $routeContent = '';
        foreach ($saveCache as $class => $result) {
            list($group, $path) = [
                $result['data'] ?? new \stdClass(),
                $result['path'] ?? base_path('data/routes/'),
            ];
            if (empty($group->routeList))
                continue;
            $routeContent .= "// Routing generation of controller {$class}\r\nRoute::";
            $hasDomain = false;
            if (!empty($group->domain)) {
                $hasDomain = true;
                $routeContent .= "domain('{$group->domain}')\r\n->";
            }
            $prefix = '';
            if (!empty($group->prefix)) {
                $prefix = rtrim($group->prefix, '/');
                $prefixes[] = $prefix;
                $routeContent .= "prefix('{$prefix}')\r\n->";
                $prefix .= '/';
            }
            if (!empty($group->middleware)) {
                $routeContent .= 'middleware(' . (is_array($group->middleware) ? var_export($group->middleware, true) : "'{$group->middleware}'") . ")\r\n->";
            }
            if ($group->classic == 'single') {
                $routeContent .= self::generateSubRoute($mapSet, $group, $group, $class, $hasDomain, $prefix, $module, $interceptors) . ";\r\n";
                continue;
            }
            $routeContent .= "controller({$class}::class)->group(function(){\r\n";
            foreach ($group->routeList as $route) {
                $routeContent .= self::generateSubRoute($mapSet, $route, $group, $class, $hasDomain, $prefix, $module, $interceptors) . ";\r\n";
            }
            $routeContent .= "});\r\n";
        }
        if (!is_dir("{$path}{$module}"))
            mkdir("{$path}{$module}", 0755, true);
        file_put_contents("{$path}{$module}/route.php", "{$commonRouteContent}\r\n{$routeContent}");
    }

    protected static function generateSubRoute(array &$mapSet, stdClass $route, stdClass $group, string $class, bool $hasDomain, string $prefix, string $module, array &$interceptors): string
    {
        // put interceptor data into Request
        $req = "Request::";
        // join all routing configuration code
        $subRouteContent = "Route::";
        if (!empty($route->domain) && !$hasDomain)
            $subRouteContent .= "domain('{$route->domain}')\r\n->";
        $childPrefix = '';
        if (!empty($route->prefix)) {
            $childPrefix = rtrim($route->prefix, '/');
            $subRouteContent .= "prefix('{$childPrefix}')\r\n->";
            $childPrefix .= '/';
        }
        if (!empty($route->middleware)) {
            $subRouteContent .= 'middleware(' . (is_array($route->middleware) ? var_export($route->middleware, true) : "'{$route->middleware}'") . ")\r\n->";
        }
        // save controller file status
        [$basePath, $minCount, $maxCount, $vars, $keys] = self::parseRoutingPath($prefix . $childPrefix . $route->path);
        $mapSet[$basePath]["{$minCount}-{$maxCount}"] = [
            'name' => "{$class}@{$route->action}",
            'mtime' => $group->mtime,
            'vars' => $vars,
        ];
        if (!empty($route->locker) || !empty($route->methodValidation) || !empty($route->parameterValidation)) {
            $inter = new stdClass();
            if (!empty($route->locker))
                $inter->locker = $route->locker;
            if (!empty($route->methodValidation))
                $inter->methodValidation = $route->methodValidation;
            if (!empty($route->parameterValidation))
                $inter->parameterValidation = $route->parameterValidation;
            $interceptors["{$class}@{$route->action}"] = $inter;
        }

        if (!empty($route->methods)) {
            $subRouteContent .= 'match(' . var_export(Method::getValuesByEnums($route->methods), true) . ', ';
        } else {
            $subRouteContent .= strtolower($route->method->value) . "(";
        }
        $subRouteContent .= $group->classic == 'single' ? "'{$route->path}', {$class}::class)" : "'{$route->path}', '{$route->action}')";
        $name = str_replace('/', '.', $basePath);
        $name = $prefix ? $name : "{$module}.{$name}";
        $subRouteContent .= "->name('{$name}')";
        $pattern = !empty($route->pattern) && $route->pattern instanceof Constraint ? $route->pattern : Constraint::MATCH;
        if ($pattern) {
            switch ($pattern) {
                case Constraint::MATCH:
                    if (isset($route->where)) {
                        if (empty($route->where))
                            throw new AnnotationException("{$class}->{$route->action} #[RouteWhere] The parameter where cannot be empty");
                        $subRouteContent .= "\r\n->where(" . var_export($route->where, true) . ")";
                    }
                    break;
                case Constraint::IN:
                    if (empty($route->keys))
                        throw new AnnotationException("{$class}->{$route->action} #[RouteWhereIn] The parameter keys cannot be empty");
                    if (empty($route->values))
                        throw new AnnotationException("{$class}->{$route->action} #[RouteWhereIn] The parameter values cannot be empty");
                    $values = is_array($route->values) ? $route->values : explode(',', $route->values);
                    $keyList = is_array($route->keys) ? $route->keys : explode(',', $route->keys);
                    $subRouteContent .= "\r\n->whereIn(" . var_export($keyList, true) . ", " . var_export($values, true) . ")";
                    break;
                default:
                    $name = ucfirst($pattern->value);
                    if (empty($route->keys))
                        throw new AnnotationException("{$class}->{$route->action} #[RouteWhere{$name}] The parameter keys cannot be empty");
                    $keyList = $route->keys == '*' ? $keys : explode(',', $route->keys);
                    $subRouteContent .= "\r\n->where" . $name . '(' . var_export($keyList, true) . ')';
            }
        }
        return $subRouteContent;
    }

}
