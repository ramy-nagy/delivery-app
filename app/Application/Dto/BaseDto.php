<?php
namespace App\Application\Dto;

use Illuminate\Contracts\Support\Arrayable;

abstract class BaseDto implements Arrayable
{
    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        
        $data = [];
        foreach ($properties as $property) {
            $data[$property->getName()] = $this->{$property->getName()};
        }
        return $data;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
