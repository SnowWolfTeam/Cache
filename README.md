#缓存

#### 文件缓存：
* 异常(类:FileException.php)：
```
    const MKDIR_ERROR      = 0x1000     //创建文件夹路径失败
    const PARAMS_NOT_ARRAY = 0x1001;    //参数不是数组
    const CACHE_NAME_ERROR = 0x1002;    //缓存名字错误
    const CACHE_TIME_OUT = 0x1003;      //缓存过期
    const FILE_OPEN_FAILED = 0x1004;    //文件打开失败
    const FILE_READ_FAILED = 0x1005;    //文件读取失败
    const FILE_WRITE_FAILED = 0x1006;   //文件写入失败
```
* 配置
```
 [
    'cachePath' => './FileCache', //保存缓存的文件夹路径
    'prefix' => 'cache_',         //缓存前缀
    'cacheLife' => 0              //缓存生存时间，单位秒，0代表永不过期
 ]
```
###### 接口
* 1 . alertConfig($params = []) 修改配置
```
    1.$params = 需要修改的内容，如仅修改cachePath就输入cachePath键值对
```
* 2 . changeConfigCacheLocation($params) 修改缓存的保存路径
```
    1.$params = './Cache' , 仅修改缓存保存路径
```
* 3 . setCache($data, $name, $cacheLife = NULL, $cachePath = '') 保存缓存
```
    1.$data = 要保存的内容，可以是任何类型
    2.$name = 缓存的名字
    3.$cacheLife = NULL 可选，缓存生，如果不为NULL,就使用传入的cacheLife，否则使用配置中的cacheLife
    4.$cachePath = NULL 可选，缓存保存文件夹路径，如果不为'',就使用传入的cachePath，否则使用配置中的cachePath
```
* 4 . setAllCache($data, $cacheLife = 0, $cachePath = '') 保存所有缓存
```
    1.$data = 要保存的内容，数组，结构[(缓存名字)=>(缓存内容),.....]
    2.$cacheLife = NULL 可选，缓存生，如果不为NULL,就使用传入的cacheLife，否则使用配置中的cacheLife
    3.$cachePath = NULL 可选，缓存保存文件夹路径，如果不为'',就使用传入的cachePath，否则使用配置中的cachePath
```
* 5 . getCache($name, $cachePath = '') 获取缓存
```
    1.$name = 缓存名字
    2.$cachePath = NULL 可选，缓存保存文件夹路径，如果不为'',就使用传入的cachePath，否则使用配置中的cachePath
```
* 6 . getAllCache($nameArray, $cachePath='')
```
    1.$name = 缓存名字,数组=[名字1，名字2，名字3...]
    2.$cachePath = NULL 可选，缓存保存文件夹路径，如果不为'',就使用传入的cachePath，否则使用配置中的cachePath
```
* 7 . checkCacheExist($name, $cachePath = '')  检查缓存是否存在
```
    1.$name = 缓存名字
    2.$cachePath = NULL 可选，缓存保存文件夹路径，如果不为'',就使用传入的cachePath，否则使用配置中的cachePath
```
* 8 . checkCacheExpire($name, $cachePath = '') 检查缓存是否过期
```
    1.$name = 缓存名字
    2.$cachePath = NULL 可选，缓存保存文件夹路径，如果不为'',就使用传入的cachePath，否则使用配置中的cachePath
```
* 9 . deleteCache($name, $cachePath = '') 删除缓存
```
    1.$name = 缓存名字
    2.$cachePath = NULL 可选，缓存保存文件夹路径，如果不为'',就使用传入的cachePath，否则使用配置中的cachePath
```
#### Redis缓存
* 异常(类:RedisException.php)：
```
   const REDIS_EXTENSION_NOT_EXIST = 0x1000; //扩展没有安装
```
###### 接口
* 1 . instance($type) 根据选择返回对应的实例
```
    1.$type = 1代表使用redis扩展，返回扩展的实例：2代表使用predis类库，返回类库实例，具体请到github搜查predis
    2.其他的使用接口请查看对应官网的api
    3.若没有扩展则抛出异常
```
#### Memcahed缓存
* 异常(类:MemcacheException.php)：
```
   const MEMCACHE_EXTENSION_NOT_INSTALL = 0x1000; //扩展没有安装
```
###### 接口
* 1 . instance() 返回对应的实例
```
    返回Memcahed扩展的实例,如果没安装扩展则抛出异常
```