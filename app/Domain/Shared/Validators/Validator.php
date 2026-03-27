<?php
namespace App\Domain\Shared\Validators;

use App\Domain\Shared\Exceptions\ValidationException;

abstract class Validator
{
    protected array $errors = [];

    abstract public function validate(): bool;

    protected function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function throws(): void
    {
        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }
    }
}
