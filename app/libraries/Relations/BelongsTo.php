<?php

namespace NovaFlow\Core\Relations;

use NovaFlow\Core\Model;

/**
 * BelongsTo Relationship
 */
class BelongsTo
{
    private $relatedModel;
    private $foreignKey;
    private $ownerKey;
    private $parentData;

    public function __construct($relatedModel, $foreignKey, $ownerKey, $parentData)
    {
        $this->relatedModel = $relatedModel;
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
        $this->parentData = $parentData;
    }

    public function get()
    {
        if (!isset($this->parentData[$this->foreignKey])) {
            return null;
        }
        $model = $this->relatedModel;
        return $model::where($this->ownerKey, $this->parentData[$this->foreignKey])->first();
    }

    public function getRelatedModel()
    {
        return $this->relatedModel;
    }
    public function getForeignKey()
    {
        return $this->foreignKey;
    }
    public function getOwnerKey()
    {
        return $this->ownerKey;
    }
}
