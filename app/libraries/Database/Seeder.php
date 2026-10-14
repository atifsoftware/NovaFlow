<?php

namespace NovaFlow\Core\Database;

/**
 * Base Database Seeder
 */
abstract class Seeder
{
    /**
     * Run the database seeds.
     */
    abstract public function run(): void;

    /**
     * Run another seeder class
     */
    public function call(string $seederClass): void
    {
        if (!class_exists($seederClass)) {
            require_once defined('BASE_PATH') ? BASE_PATH . '/app/database/seeds/' . $seederClass . '.php' : $seederClass . '.php';
        }

        $seeder = new $seederClass();
        $seeder->run();
    }
}
