<?php

namespace NovaFlow\Core\Relations;

use NovaFlow\Core\Model;

class HasMany
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
            return [];
        }
        $model = new ($this->relatedModel)();
        return $model::where($this->foreignKey, $this->parentData[$this->localKey])->get();
    }

    public function first()
    {
        if (!isset($this->parentData[$this->localKey])) {
            return null;
        }
        $model = new ($this->relatedModel)();
        return $model::where($this->foreignKey, $this->parentData[$this->localKey])->first();
    }

    public function getRelatedModel()
    {
        return $this->relatedModel;
    }
    public function getForeignKey()
    {
        return $this->foreignKey;
    }
    public function getLocalKey()
    {
        return $this->localKey;
    }
}
