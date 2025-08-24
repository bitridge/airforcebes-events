<?php

namespace App\Enums;

enum SettingGroup: string
{
    case GENERAL = 'general';
    case SMTP = 'smtp';
    case NOTIFICATIONS = 'notifications';
    case APPEARANCE = 'appearance';
    case SYSTEM = 'system';
    case EMAIL_TEMPLATES = 'email_templates';
    case INTEGRATIONS = 'integrations';
    case SECURITY = 'security';

    public function getLabel(): string
    {
        return match($this) {
            self::GENERAL => 'General',
            self::SMTP => 'SMTP Configuration',
            self::NOTIFICATIONS => 'Notifications',
            self::APPEARANCE => 'Appearance',
            self::SYSTEM => 'System',
            self::EMAIL_TEMPLATES => 'Email Templates',
            self::INTEGRATIONS => 'Integrations',
            self::SECURITY => 'Security',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::GENERAL => 'Basic application settings and configuration',
            self::SMTP => 'Email server configuration and SMTP settings',
            self::NOTIFICATIONS => 'Notification preferences and settings',
            self::APPEARANCE => 'Visual customization and branding',
            self::SYSTEM => 'System-level configuration and performance',
            self::EMAIL_TEMPLATES => 'Customizable email templates',
            self::INTEGRATIONS => 'Third-party service integrations',
            self::SECURITY => 'Security and access control settings',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::GENERAL => 'cog',
            self::SMTP => 'mail',
            self::NOTIFICATIONS => 'bell',
            self::APPEARANCE => 'paint-brush',
            self::SYSTEM => 'server',
            self::EMAIL_TEMPLATES => 'document-text',
            self::INTEGRATIONS => 'link',
            self::SECURITY => 'shield-check',
        };
    }

    public function getOrder(): int
    {
        return match($this) {
            self::GENERAL => 1,
            self::APPEARANCE => 2,
            self::SMTP => 3,
            self::EMAIL_TEMPLATES => 4,
            self::NOTIFICATIONS => 5,
            self::INTEGRATIONS => 6,
            self::SECURITY => 7,
            self::SYSTEM => 8,
        };
    }
}
