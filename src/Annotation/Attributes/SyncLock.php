<?php

namespace Crastlin\LaravelAnnotation\Annotation\Attributes;

use Crastlin\LaravelAnnotation\Extra\ResponseCode;

#[\Attribute(\Attribute::TARGET_METHOD)]
class SyncLock
{

    /**
     * @param int $expire
     *  set lock expire time, it's default value set
     *
     * @param string $name
     * Set concurrent lock names, full name is: ${prefix}${name}:{$suffix}
     * If suffix is not define, then full name is: ${prefix}${name}
     * When empty, retrieve the request route corresponding to the current route
     *
     * @param bool $once
     * only entry once when it expired
     *
     * @param ResponseCode $code
     * when gets locking status, then set response code
     *
     * @param string $msg
     * when gets locking status, then set response message
     *
     * @param array $response
     * when gets locking status, then set response body with json format
     *
     * @param string $prefix
     *  Set concurrent lock name prefix
     *
     * @param string $suffix
     *    Set concurrent lock name suffix
     *   If using parameter of request, then using: suffix="$parameterName" or suffix="${parameterName}",
     *   the parameter method default is get or post when you need special method, then using: header suffix="header.$parameterName"
     *   input(default) suffix="${parameterName}" or suffix="input.$parameterName"
     *   get suffix="get.$parameterName"
     *   post suffix="post.$parameterName"
     *   put suffix="put.$parameterName"
     *
     * @param array $suffixes
     * many suffix set
     */
    public function __construct(
        public int          $expire = 300,
        public string       $name = '',
        public ResponseCode $code = ResponseCode::IS_LOCKED,
        public string       $msg = 'The request is currently working, please try again later',
        public array        $response = [],
        public bool         $once = false,
        public string       $prefix = 'sync_lock',
        public string       $suffix = '',
        public array        $suffixes = [],
    )
    {
    }
}
