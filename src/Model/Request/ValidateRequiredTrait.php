<?php

declare(strict_types=1);

namespace MaxMessenger\Bot\Model\Request;

use MaxMessenger\Bot\Exception\Validation\RequiredFieldException;
use MaxMessenger\Bot\Exception\Validation\RequiredOneFieldException;

/**
 * Проверки наличия обязательных полей модели запроса.
 */
trait ValidateRequiredTrait
{
    /**
     * @var list<non-empty-string>
     */
    protected array $required = [];
    /**
     * @var list<non-empty-string>
     */
    protected array $requiredOnce = [];

    /**
     * Проверяет обязательные поля и группы полей, из которых нужно заполнить хотя бы одно.
     */
    public function validateRequired(): void
    {
        foreach ($this->required as $field) {
            /** @psalm-suppress DocblockTypeContradiction */
            if (!isset($this->data[$field]) || $this->data[$field] === '') {
                throw new RequiredFieldException($field);
            }
        }

        while (!empty($this->requiredOnce)) {
            foreach ($this->requiredOnce as $field) {
                /** @psalm-suppress RedundantConditionGivenDocblockType */
                if (isset($this->data[$field]) && $this->data[$field] !== '') {
                    break 2;
                }
            }

            throw new RequiredOneFieldException($this->requiredOnce);
        }
    }
}
