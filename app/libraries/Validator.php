<?php

namespace NovaFlow\Core;

/**
 * Input Validation Class
 * Provides comprehensive validation for form inputs and API data.
 */
class Validator
{
    private $data = [];
    private $rules = [];
    private $errors = [];
    private $messages = [];
    private $fieldLabels = [];

    // Default error messages in Bengali
    private $defaultMessages = [
        'required' => ':field ঘরটি অবশ্যই পূরণ করতে হবে।',
        'email' => ':field অবশ্যই একটি সঠিক ইমেইল এড্রেস হতে হবে।',
        'min' => ':field কমপক্ষে :param অক্ষরের হতে হবে।',
        'max' => ':field :param অক্ষরের বেশি হতে পারবে না।',
        'numeric' => ':field অবশ্যই সংখ্যা হতে হবে।',
        'integer' => ':field অবশ্যই পূর্ণসংখ্যা হতে হবে।',
        'string' => ':field অবশ্যই একটি স্ট্রিং হতে হবে।',
        'date' => ':field সঠিক তারিখ নয়।',
        'url' => ':field ইউআরএল ফর্ম্যাটটি সঠিক নয়।',
        'in' => 'নির্বাচিত :field-টি সঠিক নয়।',
        'unique' => ':field-টি ইতিমধ্যে ব্যবহৃত হয়েছে।',
        'exists' => 'নির্বাচিত :field-টি সঠিক নয়।',
        'regex' => ':field ফর্ম্যাটটি সঠিক নয়।',
        'confirmed' => ':field কনফার্মেশন মিলছে না।',
        'between' => ':field অবশ্যই :param এবং :param2 এর মধ্যে হতে হবে।',
        'required_if' => ':field ঘরটি পূরণ করা আবশ্যক।',
        'different' => ':field অবশ্যই :param থেকে আলাদা হতে হবে।',
    ];

    public function __construct($data = [], $rules = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $this->defaultMessages;
    }

    /**
     * Validate data against rules
     */
    public function validate()
    {
        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            $rules = is_string($rules) ? explode('|', $rules) : $rules;
            $value = $this->getValue($field);

            foreach ($rules as $rule) {
                $this->validateRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Validate a single rule
     */
    private function validateRule($field, $value, $rule)
    {
        list($ruleName, $params) = $this->parseRule($rule);

        // Skip other validations if field is empty and not required
        if ($ruleName !== 'required' && ($value === null || $value === '')) {
            return;
        }

        if (!$this->passes($ruleName, $value, $params, $field)) {
            $this->addError($field, $ruleName, $params);
        }
    }

    /**
     * Parse rule string
     */
    private function parseRule($rule)
    {
        if (strpos($rule, ':') !== false) {
            list($ruleName, $paramString) = explode(':', $rule, 2);
            $params = explode(',', $paramString);
        } else {
            $ruleName = $rule;
            $params = [];
        }

        return [$ruleName, $params];
    }

    /**
     * Check if value passes validation rule
     */
    private function passes($rule, $value, $params = [], $field = null)
    {
        switch ($rule) {
            case 'required':
                return !empty($value) || $value === '0' || $value === 0;

            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;

            case 'min':
                if (is_numeric($value)) {
                    return $value >= $params[0];
                }
                return mb_strlen((string)$value, 'UTF-8') >= $params[0];

            case 'max':
                if (is_numeric($value)) {
                    return $value <= $params[0];
                }
                return mb_strlen((string)$value, 'UTF-8') <= $params[0];

            case 'numeric':
                return is_numeric($value);

            case 'integer':
                return filter_var($value, FILTER_VALIDATE_INT) !== false;

            case 'string':
                return is_string($value);

            case 'date':
                return strtotime($value) !== false;

            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;

            case 'in':
                return in_array($value, $params);

            case 'unique':
                return $this->isUnique($value, $params);

            case 'exists':
                return $this->exists($value, $params);

            case 'regex':
                return preg_match($params[0], $value);

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                $confirmValue = $this->getValue($confirmField);
                return $value === $confirmValue;

            case 'between':
                $length = is_numeric($value) ? $value : mb_strlen((string)$value, 'UTF-8');
                return $length >= $params[0] && $length <= $params[1];
            
            case 'required_if':
                $otherField = $params[0];
                $otherValue = $params[1];
                if ((string)$this->getValue($otherField) === (string)$otherValue) {
                    return !empty($value) || $value === '0' || $value === 0;
                }
                return true;

            case 'different':
                $otherField = $params[0];
                return (string)$value !== (string)$this->getValue($otherField);

            default:
                return true;
        }
    }

    /**
     * Check if value is unique in database
     */
    private function isUnique($value, $params)
    {
        if (empty($params)) return true;

        $table = $params[0];
        $column = $params[1] ?? 'id';
        $ignoreId = $params[2] ?? null;
        $idColumn = $params[3] ?? 'id';

        $query = DB::table($table)->where($column, $value);
        if ($ignoreId) {
            $query->where($idColumn, '!=', $ignoreId);
        }

        return $query->count() == 0;
    }

    /**
     * Check if value exists in database
     */
    private function exists($value, $params)
    {
        if (empty($params)) return true;

        $table = $params[0];
        $column = $params[1] ?? 'id';

        return DB::table($table)->where($column, $value)->count() > 0;
    }

    /**
     * Get value from data array
     */
    private function getValue($field)
    {
        return $this->data[$field] ?? null;
    }

    /**
     * Add validation error
     */
    private function addError($field, $rule, $params = [])
    {
        $message = $this->getMessage($field, $rule, $params);
        $this->errors[$field][] = $message;
    }

    /**
     * Get error message
     */
    private function getMessage($field, $rule, $params = [])
    {
        $key = $field . '.' . $rule;
        $message = $this->messages[$key] ?? $this->messages[$rule] ?? $this->defaultMessages[$rule] ?? "The $field field is invalid.";

        // Replace :field with custom label if exists
        $label = $this->fieldLabels[$field] ?? $field;
        $message = str_replace(':field', $label, $message);

        if (!empty($params)) {
            $message = str_replace(':param', $params[0] ?? '', $message);
            $message = str_replace(':param2', $params[1] ?? '', $message);
        }

        return $message;
    }

    /**
     * Set custom field labels (e.g. ['full_name' => 'পূর্ণ নাম'])
     */
    public function setFieldLabels($labels)
    {
        $this->fieldLabels = $labels;
        return $this;
    }

    /**
     * Set custom messages
     */
    public function setMessages($messages)
    {
        $this->messages = array_merge($this->defaultMessages, $messages);
        return $this;
    }

    /**
     * Get validation errors
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Get first error for field
     */
    public function firstError($field)
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Check if field has errors
     */
    public function hasErrors($field = null)
    {
        if ($field) {
            return isset($this->errors[$field]);
        }
        return !empty($this->errors);
    }

    /**
     * Static validation method
     */
    public static function make($data, $rules, $messages = [], $labels = [])
    {
        $validator = new self($data, $rules);
        if (!empty($messages)) {
            $validator->setMessages($messages);
        }
        if (!empty($labels)) {
            $validator->setFieldLabels($labels);
        }
        $validator->validate();
        return $validator;
    }
}
