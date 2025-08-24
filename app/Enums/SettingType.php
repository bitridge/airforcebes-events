<?php

namespace App\Enums;

enum SettingType: string
{
    case TEXT = 'text';
    case EMAIL = 'email';
    case PASSWORD = 'password';
    case BOOLEAN = 'boolean';
    case SELECT = 'select';
    case JSON = 'json';
    case INTEGER = 'integer';
    case FLOAT = 'float';
    case URL = 'url';
    case COLOR = 'color';
    case FILE = 'file';

    public function getLabel(): string
    {
        return match($this) {
            self::TEXT => 'Text',
            self::EMAIL => 'Email',
            self::PASSWORD => 'Password',
            self::BOOLEAN => 'Boolean',
            self::SELECT => 'Select',
            self::JSON => 'JSON',
            self::INTEGER => 'Integer',
            self::FLOAT => 'Float',
            self::URL => 'URL',
            self::COLOR => 'Color',
            self::FILE => 'File',
        };
    }

    public function getInputType(): string
    {
        return match($this) {
            self::TEXT => 'text',
            self::EMAIL => 'email',
            self::PASSWORD => 'password',
            self::BOOLEAN => 'checkbox',
            self::SELECT => 'select',
            self::JSON => 'textarea',
            self::INTEGER => 'number',
            self::FLOAT => 'number',
            self::URL => 'url',
            self::COLOR => 'color',
            self::FILE => 'file',
        };
    }

    public function needsEncryption(): bool
    {
        return in_array($this, [self::PASSWORD, self::JSON]);
    }

    public function getValidationRules(): array
    {
        return match($this) {
            self::TEXT => ['nullable', 'string', 'max:255'],
            self::EMAIL => ['nullable', 'email', 'max:255'],
            self::PASSWORD => ['nullable', 'string', 'min:6'],
            self::BOOLEAN => ['nullable', 'boolean'],
            self::SELECT => ['nullable', 'string', 'max:255'],
            self::JSON => ['nullable', 'json'],
            self::INTEGER => ['nullable', 'integer'],
            self::FLOAT => ['nullable', 'numeric'],
            self::URL => ['nullable', 'url', 'max:255'],
            self::COLOR => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            self::FILE => ['nullable', 'file', 'max:2048'],
        };
    }
}
