<?php

namespace NovaFlow\Core\Relations;

use NovaFlow\Core\Model;

/**
 * HasOne Relationship
 */
class HasOne
{
    private $relatedModel;
    private $foreignKey;
    private $localKey;
    private $parentData;

    public function __construct($relatedModel, $foreignKey, $localKey, $parentData)
    {
        $this->relatedModel = $relatedModel;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
        $this->parentData = $parentData;
    }

    public function get()
    {
        if (!isset($this->parentData[$this->localKey])) {
            return null;
        }
        $model = new ($this->relatedModel)();
        return $model::where($this->foreignKey, $this->parentData[$this->localKey])->first();
    }
}
