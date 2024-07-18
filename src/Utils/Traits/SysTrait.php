<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Utils\Traits;


use Throwable;

trait SysTrait
{

    /**
     * calculate byte covert
     * @param float $bytes
     * @return string
     */
    protected function byteConvert(float $bytes): string
    {
        $s = ['B', 'Kb', 'MB', 'GB', 'TB', 'PB'];
        $currentBytes = abs($bytes);
        $e = (int)floor(log($currentBytes) / log(1024));
        return sprintf(($bytes < 0 ? 'recycle ' : '') . '%.2f%s', ($currentBytes / pow(1024, floor($e))), $s[$e]);
    }


    /**
     * get run tips
     * @param float|null $startTime
     * @param float|null $memory
     */
    protected function getRunTips(?float &$startTime, ?float &$memory)
    {
        if ($startTime == 0) {
            $startTime = microtime(true);
            $memory = memory_get_usage();
        } else {
            $runtime = microtime(true) - $startTime;
            $useMemory = memory_get_usage() - $memory;
            $useMemory = $this->byteConvert($useMemory);
            return [$useMemory, round($runtime, 4)];
        }
    }


    /**
     * log record
     * @param mixed $content
     * @param string $path
     * @param string $prefix
     * @return void
     */
    protected function logRecord(mixed $content, string $path = 'default', string $prefix = ''): void
    {
        $month = date('Y-m');
        $path = storage_path('logRecord') . "/{$path}/{$month}/";
        if (!is_dir($path))
            mkdir($path, 0755, true);

        $today = date('d');
        $lastTime = date('Y-m-d H:i:s');
        $logContent = is_array($content) || is_object($content) ? json_encode($content, JSON_UNESCAPED_UNICODE) : (string)$content;
        unset($content);
        $prefix = $prefix ? " | {$prefix}" : '';
        $logContent = "{$lastTime}{$prefix} | {$logContent}\r\n";
        file_put_contents("{$path}{$today}.log", $logContent, FILE_APPEND);
    }


    /**
     * @param string $savePath 异常保存目录
     * @param Throwable $throwable 异常对象
     * @param string|null $description 异常描述
     */
    protected function saveException(string $savePath, Throwable $throwable, ?string $description = ''): void
    {
        $exceptionContent = "Exception Notice:" . $throwable->getMessage() . ' --> ' . $throwable->getFile() . ':' . $throwable->getLine() . ', ' . $description;
        $this->logRecord($exceptionContent, "throws/{$savePath}");
    }
}
