<?php

namespace Crastlin\LaravelAnnotation;

use Crastlin\LaravelAnnotation\Annotation\Annotation;
use Crastlin\LaravelAnnotation\Annotation\AnnotationException;
use Crastlin\LaravelAnnotation\Commands\ConfigGenerator;
use Crastlin\LaravelAnnotation\Commands\NodeGenerator;
use Crastlin\LaravelAnnotation\Commands\NodeStoreGenerator;
use Crastlin\LaravelAnnotation\Commands\RouteGenerator;
use Crastlin\LaravelAnnotation\Annotation\InjectionAnnotation;
use Crastlin\LaravelAnnotation\Annotation\InterceptorAnnotation;
use Crastlin\LaravelAnnotation\Middleware\InterceptorMiddleware;
use Crastlin\LaravelAnnotation\Annotation\NodeAnnotation;
use Crastlin\LaravelAnnotation\Annotation\RouteAnnotation;
use Crastlin\LaravelAnnotation\Utils\Sync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AnnotationProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        // bind console in provider
        if ($this->app->runningInConsole()) {
            $this->commands([
                RouteGenerator::class,
                NodeGenerator::class,
                ConfigGenerator::class,
                NodeStoreGenerator::class,
            ]);
        }
        // boot route/interceptor/node
        $this->runBuildRouteWithNode();
    }


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->setupConfig();
        $this->app->singleton('crastlin.annotation.injection',
            fn($app) => new InjectionAnnotation()
        );
        $this->app->singleton('crastlin.annotation.interceptor',
            fn($app) => new InterceptorAnnotation()
        );
    }

    /**
     * 设置配置文件
     *
     * @return void
     */
    protected function setupConfig(): void
    {
        $source = realpath(__DIR__ . '/config.php');
        $userConfig = config_path('annotation.php');
        $this->publishes([$source => $userConfig]);
        $this->mergeConfigFrom($source, 'annotation');
    }

    /**
     * auto build annotation of route and node
     */
    protected function runBuildRouteWithNode(): void
    {
        $path = base_path('bootstrap/cache');
        if (file_exists("{$path}/routes.php") || file_exists("{$path}/routes-v7.php"))
            return;
        $config = config('annotation');
        $filePath = !empty($config['annotation_path']) ? rtrim($config['annotation_path'], '/') . '/' : 'data/';
        $routeBasePath = base_path($filePath . '/routes');
        $cacheFile = $routeBasePath . '/cache.php';
        $cache = file_exists($cacheFile) ? require $cacheFile : [];
        $isSingle = !empty($config['route']['is_single_mode']);
        $namespace = $config['route']['namespace'] ?? 'App\Http\Controllers';
        $path = Request::capture()->path();
        if (!empty($config['route']['auto_create_case']) && !empty($path) && preg_match('#^(\w+)(/[\w/]+)?$#', $path, $matches)) {
            if (!empty($matches[1]) && ((empty($cache['modules']) && !$isSingle) || !RouteAnnotation::exists($path, $routeBasePath))) {
                $locker = null;
                try {
                    $locker = Sync::create('distributed_lock:create_route_with_node');
                    if ($locker->lock()) {
                        $parseGenerator = [RouteAnnotation::class];
                        if (!empty($config['node']['auto_create_node']))
                            $parseGenerator[] = NodeAnnotation::class;
                        $cache['modules'] = Annotation::scanAnnotation($parseGenerator, $config);
                        $locker->unlock();
                    }
                } catch (\Throwable $exception) {
                    $locker && $locker->unlock();
                    throw new AnnotationException('message: ' . $exception->getMessage() . " file: " . $exception->getFile() . ' -> ' . $exception->getLine());
                }
            }
        }
        // 注册路由
        $this->registerRoute($config, $isSingle, $cache['modules'] ?? [], $routeBasePath, $namespace);
    }

    /**
     * register route map into RouteAnnotation
     *
     * @param bool $isSingle
     * @param array $modules
     * @param string $routeBasePath
     * @param string $baseNamespace
     * @return void
     */
    protected function registerRoute(array $config, bool $isSingle, array $modules, string $routeBasePath, string $baseNamespace): void
    {
        $routeFileSet = [];
        // all route file of modules
        $modules = !$isSingle && !empty($modules) ? $modules : ['Single'];
        $routeTypes = ['route', 'resource'];
        foreach ($modules as $module) {
            $module = ucfirst($module);
            foreach ($routeTypes as $routeType) {
                $file = "{$routeBasePath}/{$module}/{$routeType}.php";
                if (is_file($file))
                    $routeFileSet[$routeType][] = $file;
            }
        }
        // register all route files
        if (!empty($routeFileSet)) {
            if (!empty($config['interceptor']['lock']['case']) || !empty($config['interceptor']['validate']['case']))
                $route = Route::middleware(InterceptorMiddleware::class)->namespace($baseNamespace);
            else
                $route = Route::namespace($baseNamespace);
            foreach ($routeTypes as $routeType) {
                if (!isset($routeFileSet[$routeType]))
                    continue;
                foreach ($routeFileSet[$routeType] as $routeConfig) {
                    $route->group($routeConfig);
                }
            }
        }
    }
}
