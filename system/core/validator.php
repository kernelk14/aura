<?php

namespace AuraCore;

class Validator
{
    protected $data = [];
    protected $rules = [];
    protected $errors = [];
    protected $customMessages = [];
    protected $fieldLabels = [];

    public function validate(array $data, array $rules, array $messages = [], array $labels = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $messages;
        $this->fieldLabels = $labels;
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            if (is_string($fieldRules)) {
                $fieldRules = explode('|', $fieldRules);
            }

            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                $params = [];

                if (strpos($rule, ':') !== false) {
                    list($rule, $paramStr) = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $method = 'rule' . ucfirst($rule);
                if (method_exists($this, $method)) {
                    $this->$method($field, $value, $params);
                }
            }
        }

        return $this;
    }

    public function passes()
    {
        return empty($this->errors);
    }

    public function fails()
    {
        return !empty($this->errors);
    }

    public function errors()
    {
        return $this->errors;
    }

    public function error($field = null)
    {
        if ($field === null) {
            return $this->errors;
        }
        return $this->errors[$field][0] ?? null;
    }

    public function errorBag()
    {
        return $this->errors;
    }

    protected function addError($field, $rule, $message = null)
    {
        if ($message === null) {
            $message = $this->customMessages[$field . '.' . $rule]
                ?? $this->customMessages[$rule]
                ?? $this->defaultMessage($field, $rule);
        }

        $this->errors[$field][] = $message;
    }

    protected function label($field)
    {
        return $this->fieldLabels[$field] ?? str_replace('_', ' ', $field);
    }

    protected function defaultMessage($field, $rule)
    {
        $label = $this->label($field);
        $messages = [
            'required' => "The {$label} field is required.",
            'email' => "The {$label} must be a valid email address.",
            'min' => "The {$label} must be at least :min characters.",
            'max' => "The {$label} may not be greater than :max characters.",
            'numeric' => "The {$label} must be a number.",
            'integer' => "The {$label} must be an integer.",
            'confirmed' => "The {$label} confirmation does not match.",
            'same' => "The {$label} and :other must match.",
            'unique' => "The {$label} has already been taken.",
            'url' => "The {$label} format is invalid.",
            'alpha' => "The {$label} may only contain letters.",
            'alpha_num' => "The {$label} may only contain letters and numbers.",
            'alpha_dash' => "The {$label} may only contain letters, numbers, dashes and underscores.",
            'string' => "The {$label} must be a string.",
            'boolean' => "The {$label} must be true or false.",
            'array' => "The {$label} must be an array.",
            'date' => "The {$label} is not a valid date.",
            'after' => "The {$label} must be a date after :date.",
            'before' => "The {$label} must be a date before :date.",
            'size' => "The {$label} must be :size.",
            'between' => "The {$label} must be between :min and :max.",
        ];

        $message = $messages[$rule] ?? "The {$label} field is invalid.";

        if (isset($this->customMessages[$rule])) {
            $message = $this->customMessages[$rule];
        }

        return $message;
    }

    protected function ruleRequired($field, $value)
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, 'required');
        }
    }

    protected function ruleEmail($field, $value)
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email');
        }
    }

    protected function ruleMin($field, $value, $params)
    {
        $min = (int) ($params[0] ?? 0);
        if ($value !== null && $value !== '') {
            if (is_string($value) && mb_strlen($value) < $min) {
                $this->addError($field, 'min');
            } elseif (is_numeric($value) && (float) $value < $min) {
                $this->addError($field, 'min');
            } elseif (is_array($value) && count($value) < $min) {
                $this->addError($field, 'min');
            }
        }
    }

    protected function ruleMax($field, $value, $params)
    {
        $max = (int) ($params[0] ?? 0);
        if ($value !== null && $value !== '') {
            if (is_string($value) && mb_strlen($value) > $max) {
                $this->addError($field, 'max');
            } elseif (is_numeric($value) && (float) $value > $max) {
                $this->addError($field, 'max');
            } elseif (is_array($value) && count($value) > $max) {
                $this->addError($field, 'max');
            }
        }
    }

    protected function ruleNumeric($field, $value)
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, 'numeric');
        }
    }

    protected function ruleInteger($field, $value)
    {
        if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, 'integer');
        }
    }

    protected function ruleString($field, $value)
    {
        if ($value !== null && !is_string($value)) {
            $this->addError($field, 'string');
        }
    }

    protected function ruleBoolean($field, $value)
    {
        if ($value !== null && $value !== '' && !in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true)) {
            $this->addError($field, 'boolean');
        }
    }

    protected function ruleConfirmed($field, $value)
    {
        $confirmationField = $field . '_confirmation';
        $confirmation = $this->data[$confirmationField] ?? null;
        if ($value !== $confirmation) {
            $this->addError($field, 'confirmed');
        }
    }

    protected function ruleSame($field, $value, $params)
    {
        $other = $params[0] ?? '';
        $otherValue = $this->data[$other] ?? null;
        if ($value !== $otherValue) {
            $this->addError($field, 'same');
        }
    }

    protected function ruleUrl($field, $value)
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, 'url');
        }
    }

    protected function ruleAlpha($field, $value)
    {
        if ($value !== null && $value !== '' && !ctype_alpha($value)) {
            $this->addError($field, 'alpha');
        }
    }

    protected function ruleAlphaNum($field, $value)
    {
        if ($value !== null && $value !== '' && !ctype_alnum($value)) {
            $this->addError($field, 'alpha_num');
        }
    }

    protected function ruleAlphaDash($field, $value)
    {
        if ($value !== null && $value !== '' && !preg_match('/^[A-Za-z0-9_-]+$/', $value)) {
            $this->addError($field, 'alpha_dash');
        }
    }

    protected function ruleDate($field, $value)
    {
        if ($value !== null && $value !== '' && strtotime($value) === false) {
            $this->addError($field, 'date');
        }
    }

    protected function ruleAfter($field, $value, $params)
    {
        $date = $params[0] ?? date('Y-m-d');
        if ($value !== null && $value !== '' && strtotime($value) <= strtotime($date)) {
            $this->addError($field, 'after');
        }
    }

    protected function ruleBefore($field, $value, $params)
    {
        $date = $params[0] ?? date('Y-m-d');
        if ($value !== null && $value !== '' && strtotime($value) >= strtotime($date)) {
            $this->addError($field, 'before');
        }
    }

    protected function ruleSize($field, $value, $params)
    {
        $size = (int) ($params[0] ?? 0);
        if ($value !== null && $value !== '') {
            if (is_string($value) && mb_strlen($value) !== $size) {
                $this->addError($field, 'size');
            } elseif (is_numeric($value) && (float) $value !== $size) {
                $this->addError($field, 'size');
            } elseif (is_array($value) && count($value) !== $size) {
                $this->addError($field, 'size');
            }
        }
    }

    protected function ruleBetween($field, $value, $params)
    {
        $min = (float) ($params[0] ?? 0);
        $max = (float) ($params[1] ?? 0);
        if ($value !== null && $value !== '') {
            if (is_string($value)) {
                $len = mb_strlen($value);
                if ($len < $min || $len > $max) {
                    $this->addError($field, 'between');
                }
            } elseif (is_numeric($value)) {
                $val = (float) $value;
                if ($val < $min || $val > $max) {
                    $this->addError($field, 'between');
                }
            } elseif (is_array($value)) {
                $cnt = count($value);
                if ($cnt < $min || $cnt > $max) {
                    $this->addError($field, 'between');
                }
            }
        }
    }

    protected function ruleUnique($field, $value, $params)
    {
        if ($value === null || $value === '') {
            return;
        }

        $table = $params[0] ?? null;
        $column = $params[1] ?? $field;
        $ignoreId = $params[2] ?? null;
        $ignoreColumn = $params[3] ?? 'id';

        if (!$table) {
            return;
        }

        try {
            $db = new Database();
            $query = $db->table($table)->where($column, $value);

            if ($ignoreId !== null) {
                $query->where($ignoreColumn, '!=', $ignoreId);
            }

            if ($query->exists()) {
                $this->addError($field, 'unique');
            }
        } catch (\Exception $e) {
            $this->addError($field, 'unique');
        }
    }
}
