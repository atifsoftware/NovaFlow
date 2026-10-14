<?php

namespace NovaFlow\Core\Database;

use NovaFlow\Core\DB;

/**
 * Base Model / Database Factory
 */
abstract class Factory
{
    protected string $model;
    protected int $count = 1;

    /**
     * Define the model's default state.
     */
    abstract public function definition(): array;

    /**
     * Create a new factory instance
     */
    public static function new(): static
    {
        return new static();
    }

    /**
     * Set the number of models to generate
     */
    public function count(int $count): static
    {
        $this->count = max(1, $count);
        return $this;
    }

    /**
     * Create an array of attributes without persisting
     */
    public function make(array $attributes = []): array
    {
        $results = [];
        for ($i = 0; $i < $this->count; $i++) {
            $results[] = array_merge($this->definition(), $attributes);
        }
        return $this->count === 1 ? $results[0] : $results;
    }

    /**
     * Persist generated data to database
     */
    public function create(array $attributes = [])
    {
        $items = $this->make($attributes);
        $records = $this->count === 1 ? [$items] : $items;

        $created = [];
        foreach ($records as $record) {
            if (isset($this->model) && class_exists($this->model)) {
                $instance = new $this->model($record);
                $instance->save();
                $created[] = $instance;
            } else {
                $table = (new \ReflectionClass($this))->getShortName();
                $table = strtolower(str_replace('Factory', '', $table)) . 's';
                $id = DB::table($table)->insertGetId($record);
                $record['id'] = $id;
                $created[] = (object) $record;
            }
        }

        return $this->count === 1 ? $created[0] : $created;
    }
}
