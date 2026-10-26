<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Staff = 'staff';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Everything, including settings and admin users',
            self::Manager => 'Catalog, marketing, content and messages. No settings or users',
            self::Staff => 'Catalog and contact messages only',
        };
    }

    /**
     * Admin panel sections this role can open.
     *
     * @return list<string>
     */
    public function sections(): array
    {
        return match ($this) {
            self::Admin => ['dashboard', 'catalog', 'marketing', 'content', 'messages', 'settings', 'users'],
            self::Manager => ['dashboard', 'catalog', 'marketing', 'content', 'messages'],
            self::Staff => ['dashboard', 'catalog', 'messages'],
        };
    }

    public function canAccess(string $section): bool
    {
        return in_array($section, $this->sections(), true);
    }
}
