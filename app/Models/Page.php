<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Support\Markdown;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'slug', 'content', 'meta_description', 'show_in_footer', 'is_published'])]
class Page extends Model
{
    use HasSlug;

    protected function casts(): array
    {
        return ['show_in_footer' => 'boolean', 'is_published' => 'boolean'];
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    /**
     * Page body as safe HTML (Markdown; raw HTML and unsafe links are stripped).
     */
    public function html(): string
    {
        return Markdown::toHtml($this->content);
    }
}
