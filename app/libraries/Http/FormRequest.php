<?php

namespace NovaFlow\Core\Http;

use NovaFlow\Core\Request;
use NovaFlow\Core\Validator;
use NovaFlow\Core\Exceptions\ValidationException;
use Exception;

/**
 * NovaFlow FormRequest
 * Encapsulates input validation, sanitization, and authorization for controller actions.
 */
abstract class FormRequest
{
    protected ?Request $request = null;
    protected ?Validator $validator = null;
    protected ?array $validatedData = null;

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?: new Request();
    }

    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request
     */
    abstract public function rules(): array;

    /**
     * Custom validation error messages
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Custom attribute display names
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Validate the incoming request
     *
     * @throws Exception If unauthorized
     * @throws ValidationException If validation fails
     */
    public function validate(): array
    {
        if (!$this->authorize()) {
            throw new Exception('This action is unauthorized.', 403);
        }

        $data = $this->request->all();
        $this->validator = Validator::make(
            $data,
            $this->rules(),
            $this->messages(),
            $this->attributes()
        );

        if ($this->validator->fails()) {
            throw new ValidationException($this->validator->errors());
        }

        $this->validatedData = $this->validator->validated();
        return $this->validatedData;
    }

    /**
     * Get the validated data
     */
    public function validated(): array
    {
        if ($this->validatedData === null) {
            return $this->validate();
        }

        return $this->validatedData;
    }

    /**
     * Determine if the request validation failed
     */
    public function fails(): bool
    {
        if ($this->validator === null) {
            try {
                $this->validate();
            } catch (ValidationException $e) {
                return true;
            } catch (Exception $e) {
                return true;
            }
        }

        return $this->validator ? $this->validator->fails() : false;
    }

    /**
     * Get all validation errors
     */
    public function errors(): array
    {
        return $this->validator ? $this->validator->errors() : [];
    }

    /**
     * Get the underlying request instance
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Magic getter to retrieve input from the request
     */
    public function __get(string $key): mixed
    {
        return $this->request->input($key);
    }

    /**
     * Forward calls to underlying request
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->request->{$method}(...$parameters);
    }
}
