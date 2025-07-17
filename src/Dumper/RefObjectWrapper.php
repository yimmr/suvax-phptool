<?php

namespace SuvaxPHPTool\Dumper;

class RefObjectWrapper
{
    /** @var \ReflectionObject */
    public $reflection;
    public $id;
    public $origin;

    public function __construct($obj, $id)
    {
        $this->reflection = new \ReflectionObject($obj);
        $this->id = $id;
        $this->origin = $obj;
    }

    public function getProperties()
    {
        return $this->reflection->getProperties();
    }

    public function getMethods()
    {
        return $this->reflection->getMethods();
    }

    public function __get($name)
    {
        // 兼容旧代码
        if ('name' === $name) {
            return $this->reflection->getName();
        }
        return $this->$name;
    }
}
