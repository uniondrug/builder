<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-28
 */
namespace Uniondrug\Builder\Modes;

/**
 * 扫描目录
 * @package Uniondrug\Builder\Modes
 */
class FilesMode extends Abstracts\Mode
{
    public function run()
    {
        $this->create->info("开始扫描目录");
        foreach ($this->create->getFolders() as $folder) {
            $this->scanFolder($folder);
        }
        $this->create->info("共发现{%d}个文件", $this->fileCount);
    }

    /**
     * 设置启动入口
     */
    public function setStub()
    {
        $this->create->info("设置启动入口");
        $stub = <<<STUB
#!/usr/bin/env php
<?php
define("UDPHAR_SWOOLE", true);
Phar::mapPhar('{$this->create->getBootname()}');
require 'phar://{$this->create->getBootname()}/{$this->create->getBootstrap()}';
__HALT_COMPILER();
STUB;
        $this->phar->setStub($stub);
    }

    /**
     * 将指定文件加入Phar包
     * @param string $file
     * @param string $path
     */
    private function scanFile(string $file, string $path)
    {
        $this->phar->addFile($path);
        $this->fileCount++;
        $n = 50;
        if ($this->fileCount % $n === 0) {
            $i = $this->fileCount / $n;
            $this->create->debug("已加入{%d}个文件", $this->fileCount);
        }
    }

    /**
     * @param string $folder
     */
    private function scanFolder(string $folder)
    {
        $path = $this->create->getBasePath().'/'.$folder;
        if (!is_dir($path)) {
            $this->create->warning("忽略{$folder}目录 - 无效目录");
            return;
        }
        $d = dir($path);
        $r = $this->create->getRegulars();
        $ri = $this->create->getIgnoreFolders();
        while (false !== ($e = $d->read())) {
            if ($e == '.' || $e == '..') {
                continue;
            }
            $x = $path.'/'.$e;
            if (is_dir($x)) {
                $eIgnored = false;
                foreach ($ri as $irexp) {
                    if (preg_match($irexp, $e) > 0) {
                        $eIgnored = true;
                        break;
                    }
                }
                if ($eIgnored) {
                    $this->create->warning("忽略{%s}目录 - 不符合规则", $folder.'/'.$e);
                } else {
                    $this->scanFolder($folder.'/'.$e);
                }
                continue;
            }
            foreach ($r as $rexp) {
                if (preg_match($rexp, $e) > 0) {
                    $this->scanFile($x, $folder.'/'.$e);
                    break;
                }
            }
        }
        $d->close();
    }
}
