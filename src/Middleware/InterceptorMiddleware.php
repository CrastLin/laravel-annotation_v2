<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Middleware;

use Crastlin\LaravelAnnotation\Extra\ResponseCode;
use Crastlin\LaravelAnnotation\Facades\Interceptor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Crastlin\LaravelAnnotation\Annotation\AnnotationException;
use \RedisException;
use Illuminate\Http\Response;


class InterceptorMiddleware
{

    /**
     * @param Request $request
     * @param callable $next
     * @return JsonResponse|mixed
     * @throws AnnotationException
     * @throws RedisException
     */
    function handle(Request $request, callable $next): Response|JsonResponse
    {
        $action = $request->route()->getActionName();
        $datum = $request->all();
        $response = null;
        $interceptorConfig = null;
        $turnBack = Interceptor::handle($action, $datum, function () use (&$next, &$response, $request) {
            $response = $next($request);
        }, $interceptorConfig, fn(string $method, string $field) => in_array($method, ['input', 'get', 'header', 'query', 'post', 'date']) && method_exists($request, $method) ? call_user_func_array([$request, $method], [$field]) : null
        );
        if (!empty($interceptorConfig->response)) {
            $fieldSet = $interceptorConfig->response;
            list($codeField, $msgField, $dataField) = [
                $fieldSet['code'] ?? 'code',
                $fieldSet['msg'] ?? 'msg',
                $fieldSet['data'] ?? 'data',
            ];
        } else {
            list($codeField, $msgField, $dataField) = ['code', 'msg', 'data'];
        }

        return match ($turnBack->code) {
            ResponseCode::PASSED => $next($request),
            ResponseCode::IS_LOCKED, ResponseCode::PARAMETER_ERROR => response()->json(isset($turnBack->data['code']) ?
                [$codeField => $turnBack->data['code'], $msgField => $turnBack->data['msg'] ?? ($turnBack->code == ResponseCode::IS_LOCKED ? 'Request busy, please try again later -2' : 'There is an error in the request parameters, please check -2'), $dataField => new \stdClass()] :
                [$codeField => $turnBack->code->value, $msgField => $turnBack->message, $dataField => new \stdClass()])->header('Pragma', 'no-cache')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0'),
            default => $response,
        };
    }
}
