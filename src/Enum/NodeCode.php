<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Enum;

enum NodeCode: string
{
    use EnumMethod;

    case QUERY = 'query';

    case ADD = 'add';

    case EDIT = 'edit';

    case UPLOAD = 'upload';

    case DOWNLOAD = 'download';

    case DELETE = 'delete';

    case EXPORT = 'export';

    case IMPORT = 'import';

    case CHECK = 'check';

    case UNCHECK = 'uncheck';

    case REFUSE = 'refuse';

    case ENABLE = 'enable';

    case DISABLE = 'disable';

    case TOGGLE = 'toggle';

}
