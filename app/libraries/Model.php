<?php

namespace NovaFlow\Core;

/**
 * Model Class
 * Base Active Record Model (Advanced v2.0)
 */
use NovaFlow\Core\Relations\HasOne;
use NovaFlow\Core\Relations\HasMany;
use NovaFlow\Core\Relations\BelongsTo;
use NovaFlow\Core\QueryBuilder\QueryBuilder;
use JsonSerializable;
use Exception;

abstract class Model implements JsonSerializable
{
    protected static $booted = [];
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $allowedFields = [];
    protected array $hidden = []; // Fields to hide in JSON/Array
    protected $attributes = [];
    protected array $relations = [];
    protected $db;
    public $exists = false;

    /**
     * Get the primary key for the model
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    // Soft delete support
    protected $useSoftDelete = false;
    protected $deletedAtColumn = 'deleted_at';

    public function __construct(array $attributes = [])
    {
        $this->db = Container::getInstance()->make(DatabaseInterface::class);
        $this->fill($attributes);
    }

    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (!empty($this->allowedFields) && !in_array($key, $this->allowedFields)) {
                continue;
            }
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function syncAttributes(array $attributes, bool $exists = false)
    {
        $this->attributes = $attributes;
        $this->exists = $exists;
        return $this;
    }

    public function __get($key)
    {
        // Check attributes first
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        // Check loaded relations
        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        // Check for relationship method
        if (method_exists($this, $key)) {
            $relation = $this->$key();
            if ($relation instanceof HasOne || $relation instanceof HasMany || $relation instanceof BelongsTo) {
                // Return result of relationship and cache in relations (not attributes)
                $result = $relation->get();
                $this->relations[$key] = $result;
                return $result;
            }
        }

        return null;
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->attributes[$key]) || isset($this->relations[$key]);
    }

    /**
     * Get the table name
     */
    public function getTable()
    {
        if (!empty($this->table)) {
            return $this->table;
        }
        // Inferred from class name
        $className = (new \ReflectionClass($this))->getShortName();
        $baseName = str_replace('Model', '', $className);
        // Convert PascalCase to snake_case and pluralize
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $baseName));
        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        return $prefix . $snake . 's';
    }

    /**
     * New Query Builder for this model
     */
    public function newQuery(): QueryBuilder
    {
        $db = Container::getInstance()->make(DatabaseInterface::class);
        $builder = (new QueryBuilder($db));
        $builder->setModel($this);
        return $builder;
    }

    /**
     * Begin querying the model
     */
    public static function query(): QueryBuilder
    {
        static::bootIfNotBooted();
        $instance = new static;
        return $instance->newQuery();
    }

    /**
     * Static magic method to start query
     */
    public static function __callStatic($method, $parameters)
    {
        static::bootIfNotBooted();
        $instance = new static;
        return $instance->newQuery()->$method(...$parameters);
    }

    protected static function bootIfNotBooted()
    {
        $class = static::class;
        if (!isset(static::$booted[$class])) {
            static::$booted[$class] = true;
            static::boot();
        }
    }

    protected static function boot()
    {
        // Override in child classes
    }

    public function __call($method, $parameters)
    {
        if (method_exists(QueryBuilder::class, $method)) {
            return $this->newQuery()->$method(...$parameters);
        }
        throw new Exception("Method $method does not exist on model " . static::class);
    }

    /**
     * Relationships
     */
    public function hasOne($related, $foreignKey = null, $localKey = 'id')
    {
        $foreignKey = $foreignKey ?: $this->inferForeignKey();
        return new HasOne($related, $foreignKey, $localKey, $this->attributes);
    }

    public function hasMany($related, $foreignKey = null, $localKey = 'id')
    {
        $foreignKey = $foreignKey ?: $this->inferForeignKey();
        return new HasMany($related, $foreignKey, $localKey, $this->attributes);
    }

    public function belongsTo($related, $foreignKey = null, $ownerKey = 'id')
    {
        $instance = new $related;
        $foreignKey = $foreignKey ?: $instance->inferForeignKey();
        return new BelongsTo($related, $foreignKey, $ownerKey, $this->attributes);
    }

    protected function inferForeignKey(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();
        $baseName = str_replace('Model', '', $className);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $baseName)) . '_id';
    }

    /**
     * Save (Insert or Update)
     */
    public function save()
    {
        $query = $this->newQuery();

        $dataToSave = $this->attributes;
        if (!empty($this->allowedFields)) {
            $allowed = array_flip(array_merge($this->allowedFields, [$this->primaryKey]));
            $dataToSave = array_intersect_key($dataToSave, $allowed);
        }

        if ($this->exists) {
            $pk = $this->primaryKey;
            $id = $this->attributes[$pk] ?? null;
            if (!$id) throw new Exception("Primary Key missing for update");
            
            $updateData = $dataToSave;
            unset($updateData[$pk]);
            if (!empty($updateData)) {
                $query->where($pk, $id)->update($updateData);
            }
        } else {
            unset($dataToSave[$this->primaryKey]);
            $id = $query->insertGetId($dataToSave);
            if ($id) {
                $this->attributes[$this->primaryKey] = $id;
                $this->exists = true;
            }
        }
        return true;
    }

    public function delete()
    {
        if (!$this->exists) return false;

        $pk = $this->primaryKey;
        $id = $this->attributes[$pk] ?? null;

        if ($this->useSoftDelete) {
            $time = date('Y-m-d H:i:s');
            $this->attributes[$this->deletedAtColumn] = $time;
            return (bool) $this->newQuery()->where($pk, $id)->update([
                $this->deletedAtColumn => $time
            ]);
        }

        return $this->newQuery()->where($pk, $id)->delete();
    }

    public function restore()
    {
        if (!$this->exists || !$this->useSoftDelete) return false;

        $pk = $this->primaryKey;
        $id = $this->attributes[$pk] ?? null;

        $this->attributes[$this->deletedAtColumn] = null;
        return (bool) $this->newQuery()->where($pk, $id)->update([
            $this->deletedAtColumn => null
        ]);
    }

    public function toArray(): array
    {
        $data = array_merge($this->attributes, $this->relations);
        
        if (!empty($this->hidden)) {
            foreach ($this->hidden as $key) {
                unset($data[$key]);
            }
        }
        
        return $data;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
