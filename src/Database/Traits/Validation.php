<?php
/*!
 * Avalon
 * Copyright (C) 2011-2026 Jack Polgar
 *
 * This file is part of Avalon.
 *
 * Avalon is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation; version 3 only.
 *
 * Avalon is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Avalon. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Avalon\Database\Traits;

/**
 * @since 0.10
 */
trait Validation
{
    protected ?array $errors = null;

    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    public function isValid(): bool
    {
        return $this->errors === null ? $this->validate() : empty($this->errors);
    }

    public function hasError(string $property): bool
    {
        return isset($this->errors[$property]);
    }

    public function validate(): bool
    {
        $errors = [];

        foreach (static::$properties as $property => $options) {
            if ($options['type'] === 'email' && !filter_var($this->data[$property], FILTER_VALIDATE_EMAIL)) {
                $errors[$property] = 'errors.not_valid_email';
            } elseif ($options['type'] === 'password') {
                if ($this->data[$property] !== $this->data["{$property}_confirmation"]) {
                    $errors['password'] = 'errors.passwords_do_not_match';
                    continue;
                }

                $hasUppercase = preg_match('/[A-Z]/', $this->data[$property]);
                $hasLowercase = preg_match('/[a-z]/', $this->data[$property]);
                $hasDigit     = preg_match('/[0-9]/', $this->data[$property]);
                $hasSpecial   = preg_match('/[^a-zA-Z0-9]/', $this->data[$property]);

                if (!$hasUppercase || !$hasLowercase || !$hasDigit || !$hasSpecial) {
                    $errors[$property] = 'errors.password_not_strong_enough';
                }

                unset($this->data["{$property}_confirmation"]);
            }

            if (isset($options['length']) && $options['length'] && strlen($this->data[$property]) < $options['length']) {
                $errors[$property] = ['errors.must_be_at_least_x_characters_long', $options['length']];
            }

            if (isset($options['required']) && $options['required'] && !$this->data[$property]) {
                $errors[$property] = 'errors.required';
            }

            if (isset($options['unique']) && $options['unique']) {
                $existing = static::findBy($property, $this->data[$property]);

                if ($existing) {
                    $errors[$property] = 'errors.already_in_use';
                }
            }
        }

        $this->errors = $errors;

        return count($errors) === 0;
    }
}
