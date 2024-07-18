<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation;

use Crastlin\LaravelAnnotation\Annotation\Attributes\SyncLock;
use Crastlin\LaravelAnnotation\Annotation\Attributes\SyncLockByToken;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Validation;
use Crastlin\LaravelAnnotation\Enum\Rule;
use Crastlin\LaravelAnnotation\Annotation\Extra\ResponseCode;
use Crastlin\LaravelAnnotation\Annotation\Extra\Validate;
use Crastlin\LaravelAnnotation\Utils\RedisClient;
use Crastlin\LaravelAnnotation\Utils\TurnBack;

class InterceptorAnnotation extends Annotation
{

    static function matchInterceptors(\ReflectionMethod $method, \stdClass $interceptor, \stdClass $map): void
    {
        $attributes = $method->getAttributes();
        $lock = new \stdClass();
        $methodName = $method->getName();
        $methodValidationList = [];
        foreach ($attributes as $attribute) {
            $name = $attribute->getName();

            $annotation = $attribute->newInstance();
            switch ($name) {
                // match sync lock annotation
                case in_array($name, [SyncLock::class, SyncLockByToken::class]):
                    $lock->name = !empty($annotation->name) ? $annotation->name : self::humpToUnderline("{$map->module}_{$map->controller}_{$methodName}");
                    $lock->expire = $annotation->expire ?? 300;
                    $lock->code = $annotation->code ?? ResponseCode::IS_LOCKED;
                    $lock->msg = $annotation->msg ?? 'The method is locking...';
                    $lock->response = $annotation->response ?? [];
                    $lock->once = !empty($annotation->once);
                    $lock->prefix = $annotation->prefix ?? '';
                    $lock->suffix = $annotation->suffix ?? '';
                    $lock->suffixes = $annotation->suffixes ?? [];
                    $interceptor->locker = $lock;
                    break;
                // match validation
                case $attribute->getName() == Validation::class || $annotation instanceof Validation\ValidateBase:
                    $validate = new \stdClass();
                    self::matchAllValidation($validate, $annotation, $attribute, $method->getName());
                    $methodValidationList[] = $validate;
                    break;
            }
        }
        if (!empty($methodValidationList)) {
            $interceptor->methodValidation = $methodValidationList;
            unset($methodValidationList);
        }
        // Match all parameter annotations
        $parameters = $method->getParameters();
        $parameterValidation = [];
        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();
            foreach ($parameter->getAttributes() as $attribute) {
                $annotation = $attribute->newInstance();
                if ($attribute->getName() == Validation::class || $annotation instanceof Validation\ValidateBase) {
                    $validate = new \stdClass();
                    self::matchAllValidation($validate, $annotation, $attribute, $method->getName());
                    $parameterValidation[$parameterName][] = $validate;
                }
            }
            if (!empty($parameterValidation))
                $interceptor->parameterValidation = $parameterValidation;
        }
    }

    /**
     * Match all validator annotation classes
     * @param \stdClass $validate
     * Collect validator data data objects
     * @param object $annotation
     * Verifier Annotation Instance Class
     * @param \ReflectionAttribute $attribute
     * Annotation reflection class for methods or parameters
     * @param string $name
     * Annotate the host name
     * @return void
     */
    static function matchAllValidation(\stdClass $validate, object $annotation, \ReflectionAttribute $attribute, string $name): void
    {
        $validate->validator = $attribute->getName();
        $validate->field = !empty($annotation->field) ? $annotation->field : $name;
        $validate->rule = $annotation->rule instanceof Rule ? $annotation->rule->value : $annotation->rule;
        $validate->attribute = !empty($annotation->attribute) ? $annotation->attribute : $annotation->field;
        $validate->message = $annotation->message ?? '';
        if (isset($annotation->ruleValue))
            $validate->ruleValue = $annotation->ruleValue;
        if ($attribute->getName() == Validation::class) {
            $validate->class = $annotation->class ?? '';
            $validate->rules = $annotation->rules ?? [];
            $validate->messages = $annotation->messages ?? [];
            $validate->attributes = $annotation->attributes ?? [];
        }
    }

    protected function analysis(...$parameters): array
    {
        if ($this->reflectClass->isAbstract())
            return [];
        $interceptor = new \stdClass();
        $module = !empty($parameters[0]) ? $parameters[0] : 'Single';
        $interceptor->module = $module;
        [$interceptor->controller, $interceptor->ct] = $this->getController();
        $interceptor->list = [];
        $methodList = $this->reflectClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methodList as $method) {
            $std = new \stdClass();
            $std->action = $method->getName();
            self::matchInterceptors($method, $std, $interceptor);
            $interceptor->list[] = $std;
        }
        return [
            'module' => $module,
            'path' => $this->basePath,
            'data' => $interceptor,
        ];
    }

    static function build(array $analysisResult, string $savePath): void
    {
        // todo
    }


    function handle(string $action, ?array $datum = [], callable $callback = null, ?\stdClass &$interceptor = null, ?callable $header = null): TurnBack
    {
        $config = config('annotation');
        $interceptorConfig = $config['interceptor'] ?? [];
        list($lockConfig, $validateConfig, $redisConfig) = [
            $interceptorConfig['lock'] ?? [],
            $interceptorConfig['validate'] ?? [],
            $config['redis']['master'] ?? ($config['redis']['slave'] ?? []),
        ];

        if (!$interceptor) {
            $interceptorConfig = Annotation::getAnnotationCache('interceptor', 'routes', $config);
            if (empty($interceptorConfig) || !array_key_exists($action, $interceptorConfig))
                return TurnBack::intoResult(ResponseCode::PASSED, 'passed');

            $interceptor = $interceptorConfig[$action];
        }

        $releaseLocker = null;
        // Synchronized concurrent locks for interceptors
        if (!empty($lockConfig['case']) && !empty($interceptor->locker)) {
            $annotation = $interceptor->locker;
            $annotation->prefix = $annotation->prefix ? "{$annotation->prefix}:" : '';
            $lockerKey = "{$annotation->prefix}{$annotation->name}";
            if (!empty($annotation->suffix) || !empty($annotation->suffixes)) {
                $annotation->suffixes = $annotation->suffixes ?? [];
                if (!empty($annotation->suffix))
                    $annotation->suffixeses[] = $annotation->suffixes;
                $suffixKey = '';
                foreach ($annotation->suffixes as $suffix):
                    $suffixList = explode('.', $suffix);
                    $count = count($suffixList);
                    list($method, $parameter) = [
                        $count >= 2 ? $suffixList[0] : 'input',
                        $count >= 2 ? $suffixList[1] : $suffixList[0],
                    ];
                    if (str_starts_with($parameter, '$')) {
                        $field = str_replace(['{', '}'], '', substr($parameter, 1));
                        $value = $method == 'header' ? (!is_null($header) ? $header($field) : '') : $all[$field] ?? '';
                        $suffixKey .= is_string($value) ? $value : serialize($value);
                    } else {
                        $suffixKey .= ltrim($suffix, ':');
                    }
                endforeach;
                $lockerKey .= ':' . md5($suffixKey);
            }
            $redis = RedisClient::singleton(!empty($redisConfig['db']) ? (int)$redisConfig['db'] : 0, $redisConfig)->getInstance();

            if (!$redis->set($lockerKey, 1, ['nx', 'ex' => $annotation->expire ?? (!empty($lockConfig['expire']) && is_numeric($lockConfig['expire']) ? $lockConfig['expire'] : 300)])) {
                $response = !empty($annotation->response) ? $annotation->response : (!empty($lockConfig['response']) ? $lockConfig['response'] : ['code' => $annotation->code ?? 500, 'msg' => $annotation->msg ?? 'Request busy, please try again later']);
                return TurnBack::intoResult(ResponseCode::IS_LOCKED, 'is returned', ['code' => isset($response['code']) && ResponseCode::isMatched($response['code']) ? $response['code']->value : ResponseCode::IS_LOCKED->value, 'msg' => !empty($response['msg']) ? $response['msg'] : 'Request busy, please try again later']);
            }
            if (empty($annotation->once))
                $releaseLocker = fn() => $redis->del($lockerKey);
        }
        // Execute the current method annotation validator
        if (!empty($validateConfig['case']) &&
            !empty($interceptor->methodValidation) &&
            $errText = $this->runValidation($interceptor->methodValidation, $datum)
        ) {
            $releaseLocker && $releaseLocker();
            return TurnBack::intoResult(ResponseCode::PARAMETER_ERROR, $errText);
        }


        $releaseLocker && $releaseLocker();
        !is_null($callback) && $callback();
        return TurnBack::intoResult(ResponseCode::SUCCESS, 'success');
    }


    // create validator
    function runValidation(array $validatorList, array $data): string
    {
        $rules = $messages = $attributes = [];
        foreach ($validatorList as $validator) {
            $cs = explode('\\', $validator->validator);
            $ruleClass = array_pop($cs);
            if ($ruleClass == 'Validation') {
                if (!empty($validator->class)) {
                    $class = '\\' . $validator->class;
                    if (!class_exists($validator->class))
                        throw new AnnotationException("Validation Class: {$class} is not exists");
                    $validate = new $class();
                    if (!$validate instanceof Validate)
                        throw new AnnotationException("Validation Class: {$class} must instanceof \Crastlin\LaravelAnnotationV2\Utils\Validate");
                    $validate = $validate->setData($data)->validate();
                    if ($validate->fails())
                        return $validate->errors()->first();
                    continue;
                }
                // general validator annotation
                if (!empty($validator->rules)) {
                    foreach ($validator->rules as $field => $rule) {
                        $ruleList = !empty($rule) ? explode('|', $rule) : [];
                        if (!empty($rules) && array_key_exists($field, $rules)) {
                            array_push($rules[$field], ...$ruleList);
                        } else {
                            $rules[$field] = $ruleList;
                        }
                    }
                } else {
                    $rulesList = $rules[$validator->field] ?? [];
                    $ruleList = !empty($validator->rule) ? explode('|', $validator->rule) : [];
                    $rulesList = !empty($ruleList) ? array_merge($rulesList, $ruleList) : $rulesList;
                    $rules[$validator->field] = $rulesList;
                }
                if (!empty($validator->messages)) {
                    $messages = array_merge($messages, $validator->messages);
                } elseif (!empty($validator->message)) {
                    $messages["{$validator->field}.{$validator->rule}"] = $validator->message;
                }
                if (!empty($validator->attributes)) {
                    $attributes = array_merge($attributes, $validator->attributes);
                } elseif (!empty($validator->attribute)) {
                    $attributes[$validator->field] = $validator->attribute;
                }
            } else {
                $rule = $validator->rule ?? self::humpToUnderline($ruleClass);
                if (!isset($rules[$validator->field]))
                    $rules[$validator->field] = [];
                if (isset($validator->ruleValue))
                    $rule .= ":{$validator->ruleValue}";
                $rules[$validator->field][] = $rule;
                $ruleName = explode(':', $rule)[0];
                $messages["{$validator->field}.{$ruleName}"] = $validator->message;
                $attributes[$validator->field] = !empty($validator->attribute) ? $validator->attribute : $validator->field;
            }
        }
        if (!empty($rules)) {
            $messages = array_filter($messages, function ($message) {
                return !empty($message);
            });
            $validate = new Validate($rules, $messages, $attributes);
            $validate = $validate->setData($data)->validate();
            if ($validate->fails())
                return $validate->errors()->first();
        }
        return '';
    }

}
