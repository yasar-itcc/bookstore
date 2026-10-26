<?php

namespace App\Http\Requests\Admin;

use App\Enums\BookFormat as Format;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $book = $this->route('book');

        $rules = [
            'title' => ['required', 'string', 'max:200'],
            'subtitle' => ['nullable', 'string', 'max:200'],
            'slug' => ['nullable', 'alpha_dash', 'max:220', Rule::unique('books')->ignore($book)],
            'isbn' => ['nullable', 'string', 'max:20', 'regex:/^[0-9Xx-]+$/', Rule::unique('books')->ignore($book)],
            'description' => ['nullable', 'string', 'max:20000'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'publisher_id' => ['nullable', Rule::exists('publishers', 'id')],
            'language_id' => ['nullable', Rule::exists('languages', 'id')],
            'author_ids' => ['array'],
            'author_ids.*' => [Rule::exists('authors', 'id')],
            'pages' => ['nullable', 'integer', 'min:1', 'max:20000'],
            'published_year' => ['nullable', 'integer', 'min:1000', 'max:'.(now()->year + 1)],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_bestseller' => ['boolean'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'remove_cover' => ['boolean'],
        ];

        foreach (Format::cases() as $format) {
            $key = "formats.{$format->value}";
            $existingId = $book?->formats->firstWhere('format', $format)?->id;
            $whenEnabled = "exclude_unless:{$key}.enabled,1";

            $rules["{$key}.enabled"] = ['boolean'];
            $rules["{$key}.mrp"] = [$whenEnabled, 'required', 'numeric', 'min:0', 'max:999999'];
            $rules["{$key}.price"] = [$whenEnabled, 'required', 'numeric', 'min:0', "lte:{$key}.mrp"];
            $rules["{$key}.sku"] = [$whenEnabled, 'nullable', 'string', 'max:64', Rule::unique('book_formats', 'sku')->ignore($existingId)];
            $rules["{$key}.gst_rate"] = [$whenEnabled, 'required', 'numeric', 'min:0', 'max:28'];
            $rules["{$key}.is_active"] = [$whenEnabled, 'boolean'];

            if ($format->isDigital()) {
                $rules["{$key}.ebook_file"] = [$whenEnabled, 'nullable', 'file', 'mimes:pdf,epub', 'max:51200'];
            } else {
                $rules["{$key}.stock"] = [$whenEnabled, 'required', 'integer', 'min:0', 'max:1000000'];
            }
        }

        return $rules;
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $enabled = collect(Format::cases())->filter(fn ($format) => $this->boolean("formats.{$format->value}.enabled"));

                if ($enabled->isEmpty()) {
                    $validator->errors()->add('formats', 'Turn on at least one format (Paperback, Hardcover or eBook) and set its price.');
                }
            },
        ];
    }

    public function attributes(): array
    {
        $attributes = ['category_id' => 'category', 'author_ids.*' => 'author', 'cover_image' => 'cover image'];

        foreach (Format::cases() as $format) {
            foreach (['mrp' => 'MRP', 'price' => 'selling price', 'sku' => 'SKU', 'gst_rate' => 'GST rate', 'stock' => 'stock', 'ebook_file' => 'eBook file'] as $field => $label) {
                $attributes["formats.{$format->value}.{$field}"] = "{$format->label()} {$label}";
            }
        }

        return $attributes;
    }

    public function messages(): array
    {
        $messages = [];

        foreach (Format::cases() as $format) {
            $messages["formats.{$format->value}.price.lte"] = 'The selling price can\'t be more than the MRP.';
        }

        return $messages;
    }
}
