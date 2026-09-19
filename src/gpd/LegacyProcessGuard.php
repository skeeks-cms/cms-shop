<?php
namespace skeeks\cms\shop\gpd;

/** Local-process evidence only; deployment must pause producers before migration. */
final class LegacyProcessGuard
{
    public static function assertStopped(string $root, string $proc = '/proc'): void
    {
        $root = realpath($root);
        $entries = glob($proc.'/[0-9]*', GLOB_ONLYDIR);
        if (!$root || !is_readable($proc.'/self/stat') || $entries === false || !$entries) {
            throw new \RuntimeException('Не удалось проверить процессы старого обмена GPD; требуется проверка оператора.');
        }
        foreach ($entries as $entry) {
            if (basename($entry) === (string)getmypid()) continue;
            $cmd = @file_get_contents($entry.'/cmdline');
            if ($cmd === false) {
                if (is_dir($entry)) throw new \RuntimeException('Нет доступа к списку процессов GPD; требуется проверка оператора.');
                continue;
            }
            $args = explode("\0", $cmd);
            $relevant = false;
            foreach ($args as $arg) {
                if (preg_match('~^(?:shop/skeeks-suppliers/(?:update-products|update-store-items)|cmsAgent/execute)(?:/index)?$~', $arg)) $relevant = true;
            }
            if (!$relevant) continue;
            $cwd = @readlink($entry.'/cwd');
            if ($cwd === false) {
                if (is_dir($entry)) throw new \RuntimeException('Не удалось определить проект процесса GPD; требуется проверка оператора.');
                continue;
            }
            $owns = realpath($cwd) === $root;
            foreach ($args as $arg) {
                if (isset($arg[0]) && $arg[0] === '/' && realpath($arg) === $root.'/yii') $owns = true;
            }
            if ($owns) throw new \RuntimeException('Остановите старый процесс GPD проекта (PID '.basename($entry).') перед миграцией.');
        }
    }
}
