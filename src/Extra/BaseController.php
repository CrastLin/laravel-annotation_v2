<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Extra;

use Crastlin\LaravelAnnotation\Annotation\Annotation;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Inject;
use Crastlin\LaravelAnnotation\Facades\Injection;
use Crastlin\LaravelAnnotation\Utils\Traits\SetterTrait;
use Crastlin\LaravelAnnotation\Utils\Traits\SysTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class BaseController extends \Illuminate\Routing\Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, SysTrait, SetterTrait;

    protected Request $request;

    #[Inject]
    protected array $params;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->_initialize();
    }

    function _initialize()
    {
        // todo
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    protected function isPost(): bool
    {
        return strcasecmp($this->request->getMethod(), 'post') === 0;
    }

    protected function isGet(): bool
    {
        return strcasecmp($this->request->getMethod(), 'get') === 0;
    }


    protected function isOptions(): bool
    {
        return strcasecmp($this->request->getMethod(), 'options') === 0;
    }


    /**
     * calculate runtime
     * @param callable $callback
     * @param string|null $content
     * @return mixed
     * @throws Throwable
     */
    protected function calculateRuntime(callable $callback, ?string $content = ''): mixed
    {
        if ($this->isOptions())
            return ['code' => ResponseCode::SUCCESS->value, 'msg' => 'ok'];

        $startTime = 0;
        $memory = 0;
        $this->getRunTips($startTime, $memory);
        // 调用前方法
        $this->_before();
        $result = $callback();
        $this->_after();
        list($useMemory, $runtime) = $this->getRunTips($startTime, $memory);
        $runtime = round($runtime * 1000, 2);
        $content = ($content ? "{$content}, " : '') . 'result: ' . json_encode($result, 256);
        if (config('app.runtime_log') && $runtime > config('app.runtime_slow_seconds', 1)) {
            Log::info("{$this->request->path()} -- {$content}, use memory: {$useMemory}, use time: {$runtime}ms");
        }
        return $result;

    }

    /**
     * Execute an action on the controller.
     *
     * @param string $method
     * @param array $parameters
     * @return Response
     * @throws Throwable
     */
    public function callAction($method, $parameters)
    {
        $input = $this->request->all();
        $withPrefix = !empty($input) ? 'parameters: ' . json_encode($input, 256) : '';
        return $this->calculateRuntime(function () use ($method, &$parameters) {
            $class = static::class;
            $reflectClass = Injection::take("reflect.{$class}");
            if (empty($reflectClass))
                $reflectClass = new \ReflectionClass($class);
            $ref = $reflectClass->getMethod($method);
            if (!$ref || !$ref->isPublic() || $ref->isAbstract())
                throw new \Exception("Class {$class}::{$method} Cannot be accessed");
            Injection::injectWithObject($this, $reflectClass);

            $turnBack = Annotation::handleInvokeAnnotation($class, $ref, [], $parameters, true);
            if ($turnBack->code != ResponseCode::SUCCESS)
                return $turnBack->toArray(['message' => 'msg']);
            return call_user_func_array([$this, $method], $parameters);
        }, $withPrefix);
    }


    /**
     * 调用Service层能用方法，实现方法、参数注解
     * @param string|object $serviceClass service类名，例如：TaskService::class，或者是接口注入对象
     * @param string $method 调用的Service方法名
     * @param array $serviceParameters 方法参数，按参数定义的顺序
     * @param array $anotherParameters 需要合并到响应的数据
     * @return array
     */
    protected function callService(string|object $serviceClass, string $method, array $serviceParameters = [], array $anotherParameters = []): array
    {
        try {
            if (empty($serviceClass) || empty($method))
                return ['code' => ResponseCode::SERVICE_ERROR->value, 'msg' => 'service or method is not defined'];
            // When the service is an interface injection instance
            if (is_object($serviceClass)) {
                if (!$serviceClass->{$method}(...$serviceParameters)) {
                    $result = $serviceClass->getResult();
                    return ['code' => $serviceClass->getResCode(), 'msg' => $serviceClass->getError(), 'data' => $result];
                }
                $result = $serviceClass->getResult();
                $result = !empty($anotherParameters) ? array_merge($result, $anotherParameters) : $result;
                return [
                    'code' => $serviceClass->getResCode(),
                    'msg' => $serviceClass->getNotice(),
                    'data' => $result,
                ];
            }
            // When the service is a service implementation class address
            $serviceInstance = BaseImplement::singletonByParent($serviceClass);
            if (!$serviceInstance($method, ...$serviceParameters))
                return ['code' => $serviceInstance->getResCode(), 'msg' => $serviceInstance->getError(), 'data' => $serviceInstance->getResult()];
            $result = $serviceInstance->getResult();
            $result = !empty($anotherParameters) ? array_merge($result, $anotherParameters) : $result;
            return [
                'code' => $serviceInstance->getResCode(),
                'msg' => $serviceInstance->getNotice(),
                'data' => $result,
            ];
        } catch (Throwable $exception) {
            $this->saveException('serviceException', $exception);
            return [
                'code' => $exception->getCode() ?: ResponseCode::IS_EXCEPTION->value,
                'msg' => $exception->getCode() > 0 ? $exception->getMessage() : 'An error has occurred, please try again later',
            ];
        }
    }

}
