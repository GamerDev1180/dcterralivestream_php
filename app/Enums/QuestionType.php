<?php

namespace App\Enums;

enum QuestionType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Select = 'select';
    case Radio = 'radio';
    case Checkbox = 'checkbox';

    /**
     * Get the label shown in the admin panel.
     */
    public function label(): string
    {
        return match ($this) {
            self::Text => 'Text Input',
            self::Textarea => 'Long Text',
            self::Select => 'Dropdown',
            self::Radio => 'Radio Buttons',
            self::Checkbox => 'Checkboxes',
        };
    }

    /**
     * Determine if this question type needs a list of options.
     */
    public function hasOptions(): bool
    {
        return in_array($this, [self::Select, self::Radio, self::Checkbox]);
    }
}
