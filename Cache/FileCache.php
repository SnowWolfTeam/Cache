<?php
namespace PhpCache\Cache;

use PhpCache\Exception\FileException;

class FileCache
{
    private $exceptionCode = -1;
    private $exceptionMsg = '';
    private $config = null;
    private $defaultConfig = [
        'cachePath' => './FileCache',
        'prefix' => 'cache_',
        'cacheLife' => 0
    ];

    public function __construct($config = [])
    {
        $this->config = array_merge($this->defaultConfig, $config);
    }

    /**
     * 动态修改File缓存功能
     * @param array $params
     */
    public function alertConfig($params = [])
    {
        $this->config = array_merge($this->config, $params);
    }

    /**
     * 动态修改缓存文件保存的路径
     * @param $params
     */
    public function changeConfigCacheLocation($params)
    {
        $this->config['cachePath'] = empty($params) ? $this->defaultConfig['cachePath'] : $params;
    }

    /**
     * 设置缓存
     */
    public function setCache($data, $name, $cacheLife = NULL, $cachePath = '')
    {
        $filePath = $this->getCacheFilePath($cachePath, $name);
        $cache = [];
        $cache['contents'] = $data;
        $cache['expire'] = ($cacheLife !== NULL) ? ($_SERVER['REQUEST_TIME'] + $cacheLife)
            : (($this->config['cacheLife'] === 0) ? 0 : ($_SERVER['REQUEST_TIME'] + $this->config['cacheLife']));
        $this->filePutContents($filePath, $cache);
        return true;
    }

    /**
     * 设置缓存
     */
    public function setAllCache($data, $cacheLife = NULL, $cachePath = '')
    {
        if (is_array($data)) {
            $cache = [];
            $filePath = '';
            $size = sizeof($data);
            if (!is_array($cacheLife))
                $cacheLife = $cacheLife == NULL ? [$this->config['cacheLife']] : [$cacheLife];
            if (!is_array($cachePath))
                $cachePath = $cachePath == "" ? [$this->config['cachePath']] : [$cachePath];
            $dataKey = array_keys($data);
            $dataValues = array_values($data);
            for ($i = 0; $i < $size; $i++) {
                $cache['contents'] = $dataValues[$i];
                $cache['expire'] = isset($cacheLife[$i]) ?
                    ($cacheLife[$i] == 0 ? 0 : ($_SERVER['REQUEST_TIME'] + $cacheLife[$i])) :
                    ($this->config['cacheLife'] == 0 ? 0 : ($_SERVER['REQUEST_TIME'] + $this->config['cacheLife']));
                echo isset($cacheLife[$i]) ? 1 : 0;
                $filePath = $this->getCacheFilePath(
                    isset($cachePath[$i]) ? $cachePath[$i] : $this->config['cachePath'],
                    $dataKey[$i]);
                $this->filePutContents($filePath, $cache);
                unset($cache);
            }
            return true;
        } else
            throw new FileException('当前函数的数据参数必须为数组内型');
    }

    /**
     * 获取缓存
     */
    public function getCache($name, $cachePath = '')
    {
        $filePath = $this->getCacheFilePath($cachePath, $name);
        if (is_dir($filePath) || !file_exists($filePath))
            throw new FileException('缓存文件不存在');
        $data = $this->fileGetContents($filePath);
        var_dump($data);
        if ($data['expire'] == 0 || $_SERVER['REQUEST_TIME'] < $data['expire'])
            return $data['contents'];
        else {
            unlink($filePath);
            throw new FileException('缓存内容已过期');
        }
    }

    /**
     * 根据$nameArray获取多个缓存
     */
    public function getAllCache($nameArray, $cachePath = '')
    {
        $dataResult = [];
        $filePath = '';
        $data = '';
        $cachePath = empty($cachePath) ? $this->config['cachePath'] : $cachePath;
        if (!is_array($cachePath))
            $cachePath = [$cachePath];
        $size = sizeof($nameArray);
        for ($i = 0; $i < $size; $i++) {
            $filePath = $this->getCacheFilePath(
                isset($cachePath[$i]) ? $cachePath[$i] : $this->config['cachePath'],
                $nameArray[$i]
            );
            echo $filePath;
            if (is_dir($filePath) || !file_exists($filePath)) {
                $dataResult[] = NULL;
                continue;
            }
            try {
                $data = $this->fileGetContents($filePath);
            } catch (\Exception $e) {
                $dataResult[] = NULL;
                continue;
            }
            if ($data['expire'] == 0 || $_SERVER['REQUEST_TIME'] < $data['expire']) {
                $dataResult[] = NULL;
                continue;
            }
            $dataResult[] = $data['contents'];
        }
        return $dataResult;
    }

    /**
     * 检查缓存是否存在
     */
    public function checkCacheExist($name, $cachePath = '')
    {
        if (!$this->checkCacheExpire($name, $cachePath))
            return false;
        $filePath = $this->getCacheFilePath($cachePath, $name);
        if (is_dir($filePath) || !file_exists($filePath))
            return false;
        else if (is_file($filePath))
            return true;
    }

    /**
     * 查看缓存是否过期
     */
    public function checkCacheExpire($name, $cachePath = '')
    {
        $filePath = $this->getCacheFilePath($cachePath, $name);
        if (is_dir($filePath) || !file_exists($filePath))
            return false;
        $data = $this->fileGetContents($filePath);
        if ($data['expire'] == 0 || $_SERVER['REQUEST_TIME'] < $data['expire'])
            return false;
        else {
            return true;
        }
    }

    /**
     * 删除cache
     */
    public function deleteCache($name, $cachePath = '')
    {
        $filePath = $this->getCacheFilePath($cachePath, $name);
        if (is_dir($filePath) || !file_exists($filePath)) {
            return true;
        } else
            return unlink($filePath);
    }

    /**
     * 获取缓存内容
     */
    private function fileGetContents($filePath)
    {
        $fileHandle = fopen($filePath, 'r');
        if ($fileHandle === false)
            throw new FileException('文件打开失败');
        $data = fread($fileHandle, filesize($filePath));
        if ($data === false)
            throw new FileException('文件读取失败');
        fclose($fileHandle);
        return unserialize($data);
    }

    /**
     * 存入内容到缓存
     */
    private function filePutContents($filePath, $data)
    {
        $serContents = serialize($data);
        $fileHandle = fopen($filePath, 'w');
        if ($fileHandle) {
            flock($fileHandle, LOCK_EX);
            fseek($fileHandle, 0);
            ftruncate($fileHandle, 0);
            $writeResult = fwrite($fileHandle, $serContents);
            if (!$writeResult)
                throw new FileException('文件写入失败');
            fclose($fileHandle);
        } else
            throw new FileException('文件打开失败');
        chmod($filePath, 0775);
    }

    /**
     * 生成加密缓存名字
     */
    private function getCacheFilePath($cachePath, $name)
    {
        $cachePath = empty($cachePath) ? $this->config['cachePath'] : $cachePath;
        if (!is_dir($cachePath)) {
            $result = mkdir($cachePath, 0775, true);
            if (!$result)
                throw new FileException('设置缓存是创建文件路径失败');
        }
        return $cachePath . DIRECTORY_SEPARATOR . md5($this->config['prefix'] . "_" . $name);
    }
}