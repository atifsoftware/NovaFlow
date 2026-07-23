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
    protected $db;
    public $exists = false;

    /**
     * Get all records
     */
    public static function all(): array
    {
        return static::query()->get();
    }

    /**
     * Lazy load relationships
     */
    public function load(string $relation): self
    {
        $method = 'get' . ucfirst($relation);
        if (method_exists($this, $method)) {
            $this->$relation = $this->$method();
        }
        return $this;
    }

    /**
     * Load multiple relationships
     */
    public function loadMany(array $relations): self
    {
        foreach ($relations as $relation) {
            $this->load($relation);
        }
        return $this;
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
            if (empty($this->allowedFields) || in_array($key, $this->allowedFields) || $key === $this->primaryKey) {
                $this->attributes[$key] = $value;
            }
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

        // Check for relationship method
        if (method_exists($this, $key)) {
            $relation = $this->$key();
            if ($relation instanceof HasOne || $relation instanceof HasMany || $relation instanceof BelongsTo) {
                // Return result of relationship
                $result = $relation->get();
                // Cache it in attributes for next time
                $this->attributes[$key] = $result;
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
        return isset($this->attributes[$key]);
    }

    /**
     * Get the table name
     */
    public function getTable()
    {
        if ($this->table) {
            return $this->table;
        }
        // Inferred from class name
        $className = (new \ReflectionClass($this))->getShortName();
        $baseName = str_replace('Model', '', $className);
        // Convert PascalCase to snake_case and pluralize
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $baseName));
        return 'tbl_' . $snake . 's'; // JahinMart standard uses tbl_ prefix
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

        if ($this->exists) {
            $pk = $this->primaryKey;
            $id = $this->attributes[$pk] ?? null;
            if (!$id) throw new Exception("Primary Key missing for update");
            
            $query->where($pk, $id)->update($this->attributes);
        } else {
            $id = $query->insert($this->attributes);
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

        return $this->newQuery()->where($pk, $id)->delete();
    }

    public function toArray(): array
    {
        $data = $this->attributes;
        
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
