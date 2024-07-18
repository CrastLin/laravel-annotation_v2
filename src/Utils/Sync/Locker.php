<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Utils\Sync;


interface Locker
{

    /**
     * get a sync lock
     * @param int $expire
     * @return bool
     */
    function lock(int $expire = 60): bool;

    /**
     * remove a sync lock
     * @return bool
     */
    function unlock(): bool;
}
