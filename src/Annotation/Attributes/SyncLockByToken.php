<?php

namespace Crastlin\LaravelAnnotation\Annotation\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class SyncLockByToken extends SyncLock
{
    public string $suffix = '';
}
