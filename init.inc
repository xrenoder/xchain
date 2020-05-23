<?php
error_reporting (E_ALL ^ E_NOTICE);
ini_set('display_errors', 'Off');
set_time_limit(0);

// TODO проверить эффективность ручной сборки мусора
gc_disable();

if (!defined('CLASS_PATH')) {
    throw new Exception('CLASS_PATH should be defined');
}

if (!defined('CLASS_EXT')) {
    throw new Exception('CLASS_EXT should be defined');
}

if (!is_dir(LOG_PATH)) {
    if (!mkdir($concurrentDirectory = LOG_PATH) && !is_dir($concurrentDirectory)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
}

if (!is_dir(RUN_PATH)) {
    if (!mkdir($concurrentDirectory = RUN_PATH) && !is_dir($concurrentDirectory)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
}

if (!is_dir(DATA_PATH)) {
    if (!mkdir($concurrentDirectory = DATA_PATH) && !is_dir($concurrentDirectory)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
}

if (!is_dir(WALLET_PATH)) {
    if (!mkdir($concurrentDirectory = WALLET_PATH) && !is_dir($concurrentDirectory)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
}

mt_srand((double) microtime() * 1000000);

spl_autoload_register(
    function (string $className)
    {
        if ($file = deepToDirsFileSearch(CLASS_PATH, $className . CLASS_EXT)) {
            require_once($file);
        }
    }
);

/**
 * @param string $dir
 * @param string $fileName
 * @return bool|string|string[]
 */
function deepToDirsFileSearch(string $dir, string $fileName)
{
    $fullPath = $dir . DS . $fileName;
    $fullPath = str_replace(DS . DS, DS, $fullPath);

    if (is_file($fullPath)) {
        return $fullPath;
    }

    if ($handle = opendir($dir)) {
        while (($item = readdir($handle)) !== false) {
            if ($item === '.' || $item === '..') continue;

            $subDir = $dir . DS . $item;
            $subDir = str_replace(DS . DS, DS, $subDir);
            if (!is_dir($subDir)) continue;

            $result = deepToDirsFileSearch($subDir, $fileName);

            if ($result) {			// обеспечиваем выход из рекурсии при первом нахождении нужного файла
                closedir($handle);
                return $result;
            }
        }

        closedir($handle);
    }

    return false;
}
