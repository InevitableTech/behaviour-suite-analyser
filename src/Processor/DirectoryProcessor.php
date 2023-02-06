<?php declare(strict_types = 1);

namespace Forceedge01\BDDStaticAnalyser\Processor;

class DirectoryProcessor
{
    public static function getAllFeatureFiles(string $directory, string $feature_file_extension)
    {
        $files = scandir($directory);
        $features = [];
        foreach ($files as $file) {
            $dirPath = $directory . DIRECTORY_SEPARATOR . $file;
            if (is_dir($dirPath) && ($file != '.' && $file != '..')) {
                // echo $dirPath . PHP_EOL;
                $features = array_merge($features, self::getAllFeatureFiles($dirPath, $feature_file_extension));
            } else if (pathinfo($file, PATHINFO_EXTENSION) === $feature_file_extension) {
                // echo $file . PHP_EOL;
                $features[] = $directory . DIRECTORY_SEPARATOR . $file;
            }
        }

        return $features;
    }
}
