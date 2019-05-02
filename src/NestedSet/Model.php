<?php

namespace Tweakers\NestedSet;


abstract class Model
{

    /**
     * AbstractObject constructor.
     * @param array $params
     */
    function __construct(array $params = [])
    {
        $this->populate($params);
    }

    /**
     * @param array $params
     */
    public function populate(array $params = [])
    {
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

}
