<?php

namespace Crastlin\LaravelAnnotation\Extra;

use Crastlin\LaravelAnnotation\Enum\EnumMethod;

enum ResponseCode: int implements ResponseCodeEnum
{
    use EnumMethod;

    // Success response code
    case SUCCESS = 200;
    // Passed response code
    case PASSED = 201;
    // Warning response code
    case WARNING = 202;
    // Not logged in status
    case NO_LOGIN_ERROR = 400;
    // Login failed (username mismatch or incorrect password)
    case LOGIN_ERROR = 401;
    // Incorrect validation parameters
    case PARAMETER_ERROR = 500;
    // There is an anomaly present
    case IS_EXCEPTION = 501;
    // Service does not exist
    case SERVICE_ERROR = 502;
    // Limit concurrent state
    case IS_LOCKED = 503;
}
