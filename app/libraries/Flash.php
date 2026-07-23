<?php

namespace NovaFlow\Core;

/**
 * Flash Message Handler
 * Provides a clean interface for session-based messaging
 */
class Flash
{
    /**
     * Set a success message
     */
    public static function success($message)
    {
        if (function_exists('flash')) {
            flash('success', $message, 'alert alert-success');
        } else {
            $_SESSION['flash_success'] = $message;
        }
    }

    /**
     * Set an error message
     */
    public static function error($message)
    {
        if (function_exists('flash')) {
            flash('error', $message, 'alert alert-danger');
        } else {
            $_SESSION['flash_error'] = $message;
        }
    }

    /**
     * Helper for generic flashes
     */
    public static function set($name, $message, $class = 'alert alert-info')
    {
        if (function_exists('flash')) {
            flash($name, $message, $class);
        } else {
            $_SESSION['flash_' . $name] = $message;
        }
    }
}
