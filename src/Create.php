<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-28
 */
namespace Uniondrug\Builder;

use Phar;

/**
 * 创建PHAR包
 * @package Uniondrug\Builder
 */
class Create
{
    /**
     * 项目目录
     * @var string
     * @example /data/apps/sketch
     */
    private $basePath;
    private $enableCompress = false;
    private $enableFileMode = false;
    private $override = false;
    private $bootstrap = "vendor/uniondrug/server2/server.php";
    /**
     * 包名称
     * @var string
     * @example sketch.phar
     */
    private $name;
    /**
     * 包版本号
     * @var string
     * @return 1.2.3
     */
    private $version;
    /**
     * @var Phar
     */
    private $phar;
    private $pharName;
    private $pharFile;
    /**
     * 扫描项目目录
     * @var array
     */
    private $folders = [
        'app',
        'config',
        'vendor'
    ];
    private $ignoreFolders = [
        "/^\./",
        "/tests/i",
        "/examples/i",
        "/samples/i",
        "/^docs/",
    ];
    private $regulars = [
        "/\.php$/"
    ];

    /**
     * @param string $basePath 项目根目录
     * @param string $name     PHAR包名称
     * @param string $version  PHAR版本号
     */
    public function __construct(string $basePath, string $name, string $version)
    {
        $this->setBasePath($basePath);
        $this->setName($name);
        $this->setVersion($version);
    }

    public function debug(string $msg, ... $args)
    {
        $this->println("DEBUG", $msg, ... $args);
    }

    public function error(string $msg, ... $args)
    {
        $this->println("ERROR", $msg, ... $args);
    }

    public function info(string $msg, ... $args)
    {
        $this->println("INFO", $msg, ... $args);
    }

    public function warning(string $msg, ... $args)
    {
        $this->println("WARNING", $msg, ... $args);
    }

    public function println(string $level, string $msg, ... $args)
    {
        $args = is_array($args) ? $args : [];
        array_unshift($args, "[{$level}] {$msg}\n");
        file_put_contents("php://stdout", call_user_func_array('sprintf', $args));
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @return string
     */
    public function getBootname()
    {
        return $this->pharName;
    }

    /**
     * @return string
     */
    public function getBootstrap()
    {
        return $this->bootstrap;
    }

    public function getIgnoreFolders()
    {
        return $this->ignoreFolders;
    }

    public function getFolders()
    {
        return $this->folders;
    }

    public function getRegulars()
    {
        return $this->regulars;
    }

    public function isCompress()
    {
        return $this->enableCompress;
    }

    /**
     * 运行构建
     */
    public function run()
    {
        $this->pharName = $this->name.'-'.$this->version.'.phar';
        $this->pharFile = $this->basePath.'/'.$this->pharName;
        $this->info("开始创建{%s/%s}文件包{%s}", $this->name, $this->version, $this->pharName);
        if (file_exists($this->pharFile)) {
            if ($this->override) {
                unlink($this->pharFile);
                $this->warning("删除{%s}包文件", $this->pharName);
            } else {
                $this->error("文件包{%s}已存在", $this->pharName);
                return;
            }
        }
        $phar = new Phar($this->pharFile, 0, $this->pharName);
        $this->info("设置{SHA1}签名");
        $phar->setSignatureAlgorithm(Phar::SHA1);
        $this->debug("打开缓冲区");
        $phar->startBuffering();
        // 1. mode switch
        if ($this->enableFileMode) {
            // 1.1 singal file
            $this->info("创建单文件模式包");
            $m = new Modes\FileMode($this, $phar);
            $m->run();
            $m->setStub();
        } else {
            // 1.2 mulity files/directory mode
            $this->info("创建目录行式包");
            $m = new Modes\FilesMode($this, $phar);
            $m->run();
            $m->setStub();
        }
        // 2. comporess
        if ($this->enableCompress) {
            $this->info("启用{GZ}压缩");
            $phar->compress(Phar::GZ);
        }
        // 3. close buffer
        $this->debug("关闭缓冲区");
        $phar->stopBuffering();
        // 4. ended
        if (file_exists($this->pharFile)) {
            $this->info("完成{%s}包创建, 共{%.02f}MB", $this->pharName, round((filesize($this->pharFile) / 1024) / 1024), 2);
        } else {
            $this->error("创建失败");
        }
    }

    /**
     * 设置项目根目录
     * @param string $basePath
     * @return $this
     */
    public function setBasePath(string $basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    /**
     * @param string $bootstrap
     * @return $this
     */
    public function setBootstrap(string $bootstrap)
    {
        $this->bootstrap = $bootstrap;
        return $this;
    }

    /**
     * 设置压缩状态
     * @param bool $enable
     * @return $this
     */
    public function setCompress(bool $enable = true)
    {
        $this->enableCompress = $enable === true;
        return $this;
    }

    /**
     * 设置包名称
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setIgnoreFolders(array $ignoreFolders)
    {
        $this->ignoreFolders;
        return $this;
    }

    /**
     * 单文件模式
     * @return $this
     */
    public function setFileMode()
    {
        $this->enableFileMode = true;
        return $this;
    }

    /**
     * 多文件模式
     * @return $this
     */
    public function setFilesMode()
    {
        $this->enableFileMode = false;
        return $this;
    }

    /**
     * 设置扫描目录
     * @param array $folders
     * @return $this
     */
    public function setFolders(array $folders)
    {
        $this->folders = $folders;
        return $this;
    }

    /**
     * 重复生成时是否覆盖
     * @param bool $override
     * @return $this
     */
    public function setOverride(bool $override = true)
    {
        $this->override = $override === true;
        return $this;
    }

    /**
     * 设置包版本号
     * @param string $version
     * @return $this
     */
    public function setVersion(string $version)
    {
        $this->version = $version;
        return $this;
    }
}

