<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('website')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 10)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Common languages for an Indian bookstore, so the book form works on a fresh install.
        $now = now();
        DB::table('languages')->insert(array_map(fn ($language) => $language + [
            'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ], [
            ['name' => 'English', 'code' => 'en'],
            ['name' => 'Hindi', 'code' => 'hi'],
            ['name' => 'Urdu', 'code' => 'ur'],
            ['name' => 'Arabic', 'code' => 'ar'],
            ['name' => 'Marathi', 'code' => 'mr'],
            ['name' => 'Gujarati', 'code' => 'gu'],
            ['name' => 'Bengali', 'code' => 'bn'],
            ['name' => 'Tamil', 'code' => 'ta'],
            ['name' => 'Telugu', 'code' => 'te'],
        ]));

        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->string('isbn', 20)->nullable()->unique();
            $table->longText('description')->nullable();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('publisher_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('language_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('pages')->nullable();
            $table->unsignedSmallInteger('published_year')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('author_book', function (Blueprint $table) {
            $table->foreignId('author_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->primary(['author_id', 'book_id']);
        });

        // One row per format a book is sold in (paperback, hardcover, ebook), each with its own price.
        Schema::create('book_formats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('format', 20);
            $table->string('sku', 64)->nullable()->unique();
            $table->decimal('mrp', 10, 2);
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock')->nullable();
            $table->string('ebook_file')->nullable();
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['book_id', 'format']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_formats');
        Schema::dropIfExists('author_book');
        Schema::dropIfExists('books');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('publishers');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('categories');
    }
};
