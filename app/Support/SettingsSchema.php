<?php

namespace App\Support;

/**
 * The fields shown under Admin → Settings, grouped into tabs and sections.
 * Types: text, textarea, email, url, number, toggle, image, category, book, features, team.
 * Default values live in config/store-settings.php.
 */
class SettingsSchema
{
    public static function groups(): array
    {
        // Every field becomes [label, type, required, hint].
        return collect(static::definitions())->map(function ($group) {
            $group['sections'] = collect($group['sections'])
                ->map(fn ($fields) => array_map(fn ($field) => array_pad($field, 4, null), $fields))
                ->all();

            return $group;
        })->all();
    }

    private static function definitions(): array
    {
        return [
            'store' => [
                'label' => 'Store info', 'icon' => 'ti-building-store',
                'description' => 'Your store name, logo, contact details, GST and delivery charges.',
                'sections' => [
                    'Store' => [
                        'store.name' => ['Store name', 'text', 'required'],
                        'store.tagline' => ['Tagline', 'text'],
                        'store.logo' => ['Logo', 'image', null, 'PNG or SVG with a transparent background works best. Shown in the header.'],
                        'store.favicon' => ['Browser tab icon', 'image', null, 'A small square image (e.g. 64×64).'],
                        'store.footer_about' => ['Footer text', 'textarea', null, 'A short line about your store, shown in the footer.'],
                    ],
                    'Contact' => [
                        'store.email' => ['Email', 'email'],
                        'store.phone' => ['Phone', 'text'],
                        'store.whatsapp' => ['WhatsApp number', 'text', null, 'With country code, e.g. 919876543210. Adds a WhatsApp chat link.'],
                        'store.address' => ['Address', 'textarea'],
                    ],
                    'Money and tax' => [
                        'store.currency' => ['Currency', 'text', null, 'INR (₹). Prices are always shown in rupees.'],
                        'store.gstin' => ['GSTIN', 'text', null, 'Your 15-character GST number, printed on invoices.'],
                    ],
                    'Delivery and Cash on Delivery' => [
                        'store.cod_enabled' => ['Cash on Delivery', 'toggle', null, 'Let customers pay when the order arrives.'],
                        'store.cod_limit' => ['COD limit (₹)', 'number', null, 'Orders above this amount can\'t use COD. Leave empty for no limit.'],
                        'store.shipping_fee' => ['Delivery charge (₹)', 'number'],
                        'store.free_shipping_min' => ['Free delivery above (₹)', 'number', null, 'Orders at or above this amount get free delivery.'],
                    ],
                    'Social media' => [
                        'store.facebook' => ['Facebook page', 'url'],
                        'store.instagram' => ['Instagram', 'url'],
                        'store.x' => ['X (Twitter)', 'url'],
                        'store.youtube' => ['YouTube', 'url'],
                    ],
                ],
            ],

            'email' => [
                'label' => 'Email', 'icon' => 'ti-mail',
                'description' => 'Who emails come from, and which emails are sent. Order emails start with checkout (phase 3).',
                'sections' => [
                    'Sender' => [
                        'email.from_name' => ['From name', 'text', 'required'],
                        'email.from_address' => ['From address', 'email', 'required', 'Use an address on your own domain so emails don\'t land in spam.'],
                        'email.admin_address' => ['Admin notification address', 'email', null, 'Where new-order and contact-form alerts are sent.'],
                    ],
                    'Emails to send' => [
                        'email.order_confirmation' => ['Order confirmation to customer', 'toggle'],
                        'email.notify_admin_new_order' => ['New order alert to admin', 'toggle'],
                        'email.notify_admin_contact' => ['Contact form alert to admin', 'toggle'],
                        'email.footer_text' => ['Email footer', 'textarea'],
                    ],
                ],
            ],

            'seo' => [
                'label' => 'SEO', 'icon' => 'ti-world-search',
                'description' => 'How your store appears in Google and when links are shared on WhatsApp or social media.',
                'sections' => [
                    'Search engines' => [
                        'seo.meta_title' => ['Home page title', 'text', null, 'About 50–60 characters.'],
                        'seo.meta_description' => ['Home page description', 'textarea', null, 'About 150–160 characters. Shown under your link in Google.'],
                        'seo.meta_keywords' => ['Keywords', 'text', null, 'Comma separated.'],
                        'seo.allow_indexing' => ['Allow search engines', 'toggle', null, 'Turn off on staging so Google doesn\'t index test data.'],
                    ],
                    'Sharing and analytics' => [
                        'seo.og_image' => ['Share image', 'image', null, 'Shown when your link is shared. 1200×630 works best.'],
                        'seo.google_analytics_id' => ['Google Analytics ID', 'text', null, 'Looks like G-XXXXXXXXXX. Leave empty to turn off.'],
                    ],
                ],
            ],

            'home' => [
                'label' => 'Home page', 'icon' => 'ti-home',
                'description' => 'Turn home page sections on or off and change their text. Slides are managed under Marketing → Banners & slider.',
                'sections' => [
                    'Announcement bar' => [
                        'home.announcement_enabled' => ['Show announcement bar', 'toggle'],
                        'home.announcement_text' => ['Text', 'text'],
                        'home.announcement_link' => ['Link', 'text', null, 'e.g. /books or a full URL.'],
                    ],
                    'Categories' => [
                        'home.show_categories' => ['Show', 'toggle'],
                        'home.categories_title' => ['Heading', 'text'],
                    ],
                    'Bestsellers' => [
                        'home.show_bestsellers' => ['Show', 'toggle', null, 'Pick the books under Marketing → Featured & bestsellers.'],
                        'home.bestsellers_title' => ['Heading', 'text'],
                    ],
                    'New arrivals' => [
                        'home.show_new_arrivals' => ['Show', 'toggle', null, 'Shows the books added most recently.'],
                        'home.new_arrivals_title' => ['Heading', 'text'],
                    ],
                    'eBooks promotion' => [
                        'home.show_ebooks' => ['Show', 'toggle'],
                        'home.ebooks_eyebrow' => ['Small label', 'text'],
                        'home.ebooks_title' => ['Heading', 'text'],
                        'home.ebooks_text' => ['Text', 'textarea'],
                        'home.ebooks_button' => ['Button text', 'text'],
                    ],
                    'Two category columns' => [
                        'home.show_category_columns' => ['Show', 'toggle', null, 'Two shelves of books side by side, with your offer box (Marketing → Banners) beside them.'],
                        'home.column_category_1' => ['First column', 'category'],
                        'home.column_category_2' => ['Second column', 'category'],
                    ],
                    'Book spotlight' => [
                        'home.show_spotlight' => ['Show', 'toggle'],
                        'home.spotlight_book_id' => ['Book', 'book', null, 'Leave empty to use the first featured book.'],
                        'home.spotlight_eyebrow' => ['Small label', 'text'],
                        'home.spotlight_label' => ['Highlight line', 'text'],
                        'home.spotlight_text' => ['Text', 'textarea', null, 'Leave empty to use the book\'s description.'],
                        'home.spotlight_button' => ['Button text', 'text'],
                    ],
                    'Testimonials, blog and newsletter' => [
                        'home.show_testimonials' => ['Show testimonials', 'toggle'],
                        'home.testimonials_title' => ['Testimonials heading', 'text'],
                        'home.show_blog' => ['Show latest blog posts', 'toggle'],
                        'home.blog_title' => ['Blog heading', 'text'],
                        'home.show_newsletter' => ['Show newsletter signup', 'toggle'],
                        'home.newsletter_title' => ['Newsletter heading', 'text'],
                        'home.newsletter_text' => ['Newsletter text', 'textarea'],
                    ],
                ],
            ],

            'about' => [
                'label' => 'About page', 'icon' => 'ti-info-circle',
                'description' => 'Text, images and team members on your About page.',
                'sections' => [
                    'Our story' => [
                        'about.hero_title' => ['Page title', 'text'],
                        'about.intro_eyebrow' => ['Small label', 'text'],
                        'about.intro_title' => ['Heading', 'text'],
                        'about.intro_text' => ['Text', 'textarea', null, 'Leave a blank line between paragraphs.'],
                        'about.intro_image' => ['Image', 'image', null, 'Leave empty to show a collage of your book covers.'],
                        'about.intro_button' => ['Button text', 'text'],
                    ],
                    'Quote' => [
                        'about.quote' => ['Quote', 'textarea'],
                        'about.quote_author' => ['Quote by', 'text'],
                        'about.quote_text' => ['Text below the quote', 'textarea'],
                        'about.quote_image' => ['Image', 'image'],
                        'about.quote_button' => ['Button text', 'text'],
                    ],
                    'Why shop with us' => [
                        'about.features' => ['Highlights', 'features', null, 'Four short highlights shown in a row.'],
                    ],
                    'Team' => [
                        'about.team_title' => ['Heading', 'text'],
                        'about.team' => ['Team members', 'team'],
                    ],
                    'Pricing page' => [
                        'pricing.eyebrow' => ['Small label', 'text'],
                        'pricing.title' => ['Heading', 'text'],
                        'pricing.text' => ['Text', 'textarea'],
                        'pricing.note' => ['Note under the plans', 'text', null, 'Plans themselves are under Marketing → Pricing plans.'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: ?string, 3: ?string}>
     */
    public static function fields(string $group): array
    {
        return collect(static::groups()[$group]['sections'] ?? [])->collapse()->all();
    }
}
