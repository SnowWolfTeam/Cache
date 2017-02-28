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
     * @param $data
     * @param $name
     * @param int $cacheLife
     * @return bool
     * @throws FileException
     */
    public function setCache($data, $name, $cacheLife = NULL, $cachePath = '')
    {
        try {
            if (!is_dir($this->config['cachePath'])) {
                $result = mkdir($this->config['cachePath'], 0775, true);
                if (!$result)
                    throw new FileException('设置缓存是创建文件路径失败', FileException::MKDIR_ERROR);
            }
            $cache = [];
            $cache['contents'] = $data;
            $cache['expire'] = $cacheLife !== NULL ? $_SERVER['REQUEST_TIME'] + $cacheLife
                : $this->config['cacheLife'] === 0 ? 0
                    : $_SERVER['REQUEST_TIME'] + $this->config['cacheLife'];
            $filePath = $this->getCacheFilePath($cachePath, $name);
            $this->filePutContents($filePath, $cache);
            return true;
        } catch (FileException $e) {
            $this->exceptionCode = $e->getCode();
            $this->exceptionMsg = $e->getMessage();
            return false;
        }
    }

    /**
     * 设置缓存
     * @param $data
     * @param int $cacheLife
     * @param string $cachePath
     * @return bool
     */
    public function setAllCache($data, $cacheLife = 0, $cachePath = '')
    {
        try {
            if (is_array($data)) {
                if (!file_exists($this->config['cachePath'])) {
                    $result = mkdir($this->config['cachePath'], 0775, true);
                    if (!$result)
                        throw new FileException('设置缓存是创建文件路径失败', FileException::MKDIR_ERROR);
                }

                $cache = [];
                $filePath = '';
                foreach ($data as $key => $values) {
                    $cache['contents'] = $key;
                    $cache['expire'] = $cacheLife !== 0 ? $_SERVER['REQUEST_TIME'] + $cacheLife
                        : $this->config['cacheLife'] === 0 ? 0
                            : $_SERVER['REQUEST_TIME'] + $this->config['cacheLife'];
                    $cache['mtime'] = $_SERVER['REQUEST_TIME'];
                    $filePath = $this->getCacheFilePath($cachePath, $values);
                    $this->filePutContents($filePath, $cache);
                    unset($cache);
                }
            } else
                throw new FileException('当前函数的数据参数必须为数组内型', FileException::PARAMS_NOT_ARRAY);
        } catch (FileException $e) {
            $this->exceptionCode = $e->getCode();
            $this->exceptionMsg = $e->getMessage();
            return false;
        }
    }

    /**
     * 获取缓存
     * @param $name
     * @return mixed
     * @throws FileException
     */
    public function getCache($name, $cachePath = '')
    {
        try {
            $filePath = $this->getCacheFilePath($cachePath, $name);
            if (is_dir($filePath) || !file_exists($filePath))
                throw new FileException('缓存文件不存在', FileException::CACHE_NAME_ERROR);
            $data = $this->fileGetContents($filePath);
            if ($data['expire'] == 0 || $_SERVER['REQUEST_TIME'] < $data['expire'])
                return $data['contents'];
            else {
                unlink($filePath);
                throw new FileException('缓存内容已过期', FileException::CACHE_TIME_OUT);
            }
        } catch (FileException $e) {
            $this->exceptionCode = $e->getCode();
            $this->exceptionMsg = $e->getMessage();
            return false;
        }
    }

    /**
     * 根据$nameArray获取多个缓存
     * @param $nameArray
     * @param $cachePath
     * @return bool
     */
    public function getAllCache($nameArray, $cachePath = '')
    {
        $dataResult = [];
        $filePath = '';
        $data = '';
        foreach ($nameArray as $value) {
            $filePath = $this->getCacheFilePath($cachePath, $name);
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
     * @param $name
     * @return bool
     */
    public function checkCacheExist($name, $cachePath = '')
    {
        $filePath = $this->getCacheFilePath($cachePath, $name);
        if (is_dir($filePath) || !file_exists($filePath))
            return false;
        else if (is_file($filePath))
            return true;
    }

    /**
     * 查看缓存是否过期
     * @param $name
     * @return bool
     */
    public function checkCacheExpire($name, $cachePath = '')
    {
        try {
            $filePath = $this->getCacheFilePath($cachePath, $name);
            if (is_dir($filePath) || !file_exists($filePath))
                return false;
            $data = $this->fileGetContents($filePath);
            if ($data['expire'] == 0 || $_SERVER['REQUEST_TIME'] < $data['expire'])
                return true;
            else {
                unlink($filePath);
                return false;
            }
        } catch (FileException $e) {
            $this->exceptionCode = $e->getCode();
            $this->exceptionMsg = $e->getMessage();
            return false;
        }
    }

    /**
     * 删除cache
     * @param $name
     * @return bool
     * @throws FileException
     */
    public function deleteCache($name, $cachePath = '')
    {
        try {
            $filePath = $this->getCacheFilePath($cachePath, $name);
            if (is_dir($filePath) || !file_exists($filePath))
                throw new FileException('缓存文件不存在', FileException::CACHE_NAME_ERROR);
            return unlink($filePath);
        } catch (FileException $e) {
            $this->exceptionCode = $e->getCode();
            $this->exceptionMsg = $e->getMessage();
            return false;
        }
    }

    /**
     * 获取缓存内容
     * @param $filePath
     * @return mixed
     * @throws FileException
     */
    private function fileGetContents($filePath)
    {
        $fileHandle = fopen($filePath, 'r');
        if ($fileHandle === false)
            throw new FileException('文件打开失败', FileException::FILE_OPEN_FAILED);
        $data = fread($fileHandle, filesize($filePath));
        if ($data === false)
            throw new FileException('文件读取失败', FileException::FILE_READ_FAILED);
        fclose($fileHandle);
        return unserialize($data);
    }

    /**
     * 存入内容到缓存
     * @param $filePath
     * @param $data
     * @throws FileException
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
                throw new FileException('文件写入失败', FileException::FILE_WRITE_FAILED);
            fclose($fileHandle);
        } else
            throw new FileException('文件打开失败', FileException::FILE_OPEN_FAILED);
        chmod($filePath, 0775);
    }

    /**
     * 生成加密缓存名字
     * @param $cachePath
     * @param $name
     * @return string
     */
    private function getCacheFilePath($cachePath, $name)
    {
        $cachePath = empty($cachePath) ? $this->config['cachePath'] : $cachePath;
        return $cachePath . md5($this->config['prefix'] . "_" . $name);
    }
}