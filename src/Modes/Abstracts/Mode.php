<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-28
 */
namespace Uniondrug\Builder\Modes\Abstracts;

use Phar;
use Uniondrug\Builder\Create;

abstract class Mode
{
    /**
     * @var Create
     */
    protected $create;
    /**
     * @var Phar
     */
    protected $phar;

    protected $fileCount = 0;

    public function __construct(Create $create, Phar $phar)
    {
        $this->create = $create;
        $this->phar = $phar;
    }

    abstract function run();
    abstract function setStub();
}
