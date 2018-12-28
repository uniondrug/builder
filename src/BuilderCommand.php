<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-28
 */
namespace App\Commands;

use Uniondrug\Builder\Create;
use Uniondrug\Console\Command;

/**
 * 创建PHAR包
 * @package Uniondrug\Builder
 */
class BuilderCommand extends Command
{
    /**
     * 命令名称
     * @var string
     */
    protected $signature = 'builder
        {--name : PHAR包名称};
        {--ver : PHAR包的版本号}';
    /**
     * 命令描述
     * @var string
     */
    protected $description = '构建PHAR包';

    /**
     * @return mixed
     */
    public function handle()
    {
        $c = new Create(getcwd(), 'example', '1.2.3');
        $c->setOverride(true);
        $c->setCompress(true);
        $c->run();
    }
}
