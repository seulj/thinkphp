<?php
/**
 * Created by PhpStorm.
 * User: xiapf
 * Date: 2018/10/25 0025
 * Time: 13:36
 */

namespace Cienv;

class Cienv
{
    static $cache = [];
    static $cacheFile = [];

    /**
     * 获取某一个环境变量
     * @param $env_name
     * @return string
     */
    public static function getEnv($env_name)
    {
        $cache_data = isset(self::$cache[$env_name]) ? self::$cache[$env_name] : null;
        if (isset($cache_data)) {
            return $cache_data;
        }
        if (!$env_name) {
            return "";
        }
        if (isset($_SERVER['ENVS_BASE_PATH'])) {
            $file_path = trim($_SERVER['ENVS_BASE_PATH'] . "/" . $env_name);
            if (file_exists($file_path)) {
                //将值放进静态数组中
                self::$cache[$env_name] = trim(file_get_contents($file_path));
                return self::$cache[$env_name];
            }
        } else if (isset($_SERVER[$env_name])) {
            self::$cache[$env_name] = $_SERVER[$env_name];
            return self::$cache[$env_name];
        }
        return getenv($env_name) ? trim(getenv($env_name)) : "";
    }

    public static function getAllEnvs()
    {
        if (!self::$cacheFile) {
            $file_envs = [];
            if (isset($_SERVER['ENVS_BASE_PATH'])) {
                $scan_dir = trim($_SERVER['ENVS_BASE_PATH']);
                if (is_dir($scan_dir)) {
                    $list = scandir($scan_dir);
                    foreach ($list as $k => $d) {
                        if ($d == '.' || $d == "..") {
                            continue;
                        }
                        if (is_dir($scan_dir . "/" . $d)) {
                            continue;
                        }
                        $file_envs[$d] = trim(file_get_contents($scan_dir . "/" . $d));
                    }
                }
            }
            self::$cacheFile = $file_envs;
        }
        //file的优先级更高
        return array_merge($_SERVER, self::$cacheFile);
    }

}


