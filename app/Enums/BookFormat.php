<?php

namespace App\Enums;

enum BookFormat: string
{
    case Paperback = 'paperback';
    case Hardcover = 'hardcover';
    case Ebook = 'ebook';

    public function label(): string
    {
        return match ($this) {
            self::Paperback => 'Paperback',
            self::Hardcover => 'Hardcover',
            self::Ebook => 'eBook',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Paperback => 'ti-book',
            self::Hardcover => 'ti-book-2',
            self::Ebook => 'ti-device-tablet',
        };
    }

    /**
     * eBooks are downloaded, so they have a file instead of stock.
     */
    public function isDigital(): bool
    {
        return $this === self::Ebook;
    }

    /**
     * Usual GST in India: printed books are exempt, eBooks are taxed as digital services.
     * Confirm with your CA; the rate can be changed per book.
     */
    public function defaultGstRate(): float
    {
        return $this->isDigital() ? 18.0 : 0.0;
    }
}
