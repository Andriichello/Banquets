<?php

namespace App\Constrainters;
use App\Rules\SymfonyRule;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;


abstract class Constrainter
{
    /**
     * Get an array of validation constraints.
     *
     * @param bool $isMandatory
     * @param Constraint[] $additionalConstrains
     * @return array
     */
    public static function getConstraints(bool $isMandatory = false, array $additionalConstrains = []): array {
        $constraints = $isMandatory ? [new Assert\NotBlank()] : [];

        return array_merge(
            $constraints,
            $additionalConstrains
        );
    }

    /**
     * Get an array of validation rules.
     *
     * @param bool $required
     * @param Constraint[]|string[] $additionalConstrains
     * @return array
     */
    public static function getRules(bool $required = false, array $additionalConstrains = []): array {
        $rules = $required ? ['required'] : [];

        $constraints = static::getConstraints(false, $additionalConstrains);
        foreach ($constraints as $constraint) {
            if (is_string($constraint)) {
                $rules[] = $constraint;
            } else {
                $rules[] = new SymfonyRule($constraint);
            }
        }

        return $rules;
    }
}
