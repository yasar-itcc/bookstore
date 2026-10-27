<?php

/*
 * Default values for store settings. Anything saved in Admin → Settings overrides these.
 * Read with setting('group.key'), e.g. setting('store.name').
 */

return [
    'store' => [
        'name' => 'Book Store',
        'tagline' => 'Your favourite books, all in one place',
        'logo' => null,
        'logo_light' => null,
        'theme' => 'ocean',
        'favicon' => null,
        'email' => 'hello@example.com',
        'phone' => '+91 98765 43210',
        'whatsapp' => null,
        'address' => "Jaipur, Rajasthan\nIndia",
        'gstin' => null,
        'currency' => 'INR',
        'cod_enabled' => '1',
        'cod_limit' => '5000',
        'shipping_fee' => '49',
        'free_shipping_min' => '499',
        'footer_about' => 'An online bookstore for every reader: new releases, timeless classics, Hindi literature, exam guides and eBooks, delivered across India with Cash on Delivery.',
        'facebook' => null,
        'instagram' => null,
        'x' => null,
        'youtube' => null,
    ],

    'email' => [
        'from_name' => 'Book Store',
        'from_address' => 'orders@example.com',
        'admin_address' => 'admin@example.com',
        'order_confirmation' => '1',
        'notify_admin_new_order' => '1',
        'notify_admin_contact' => '1',
        'footer_text' => 'Thank you for shopping with us. Happy reading!',
    ],

    'seo' => [
        'meta_title' => 'Book Store | Buy Books Online in India',
        'meta_description' => 'Buy new books, classics, Hindi books, exam guides and eBooks online with Cash on Delivery across India.',
        'meta_keywords' => 'books, buy books online, hindi books, ebooks, cash on delivery, india',
        'og_image' => null,
        'google_analytics_id' => null,
        'allow_indexing' => '1',
    ],

    'home' => [
        'announcement_enabled' => '1',
        'announcement_text' => 'Free delivery on orders above ₹499. Cash on Delivery available across India.',
        'announcement_link' => '/books',

        'show_categories' => '1',
        'categories_title' => 'Book Categories',

        'show_bestsellers' => '1',
        'bestsellers_title' => 'Bestsellers of the week',

        'show_new_arrivals' => '1',
        'new_arrivals_title' => 'New Arrivals',

        'show_ebooks' => '1',
        'ebooks_eyebrow' => 'eBooks',
        'ebooks_title' => 'Read instantly with our eBooks',
        'ebooks_text' => 'Download popular titles in PDF or EPUB and start reading in seconds, on your phone, tablet or laptop.',
        'ebooks_button' => 'Browse eBooks',

        'show_category_columns' => '1',
        'column_category_1' => null,
        'column_category_2' => null,

        'show_spotlight' => '1',
        'spotlight_book_id' => null,
        'spotlight_eyebrow' => 'Book of the month',
        'spotlight_label' => 'Special price this month',
        'spotlight_text' => null,
        'spotlight_button' => 'Buy now',

        'show_testimonials' => '1',
        'testimonials_title' => 'What readers say',

        'show_blog' => '1',
        'blog_title' => 'From our blog',

        'show_newsletter' => '1',
        'newsletter_title' => 'Join our newsletter',
        'newsletter_text' => 'New arrivals, reading lists and member-only offers, straight to your inbox. No spam.',
    ],

    'about' => [
        'hero_title' => 'About Us',
        'intro_eyebrow' => 'Who we are',
        'intro_title' => 'A bookstore made by readers, for readers',
        'intro_text' => "We started Book Store with a simple idea: everyone in India should be able to find the books they love at a fair price, without a long trip to a big-city bookshop.\n\nToday we stock thousands of titles, from new releases and timeless classics to Hindi literature, children's books and exam guides, and deliver them to your door with Cash on Delivery.",
        'intro_image' => null,
        'intro_button' => 'Shop books',
        'quote' => 'A reader lives a thousand lives before he dies. The man who never reads lives only one.',
        'quote_author' => 'George R. R. Martin',
        'quote_text' => 'That is why we pick every book on our shelves with care, write honest descriptions, and keep our prices low, so that the next great story is always within reach.',
        'quote_image' => null,
        'quote_button' => 'Read our blog',
        'features' => [
            ['icon' => 'ti-truck-delivery', 'title' => 'Fast delivery', 'text' => 'Across India in 3–7 days'],
            ['icon' => 'ti-cash', 'title' => 'Cash on Delivery', 'text' => 'Pay when your books arrive'],
            ['icon' => 'ti-refresh', 'title' => 'Easy returns', 'text' => 'Damaged copy? We replace it'],
            ['icon' => 'ti-certificate', 'title' => 'Original books', 'text' => 'Straight from publishers'],
        ],
        'team_title' => 'Meet our team',
        'team' => [
            ['name' => 'Yasar', 'role' => 'Founder', 'photo' => null],
            ['name' => 'Ananya Sharma', 'role' => 'Head of Curation', 'photo' => null],
            ['name' => 'Rahul Verma', 'role' => 'Customer Happiness', 'photo' => null],
        ],
    ],

    'pricing' => [
        'eyebrow' => 'Membership',
        'title' => 'Choose your reading plan',
        'text' => 'Save more on every order with a Book Store membership. Cancel anytime.',
        'note' => 'Memberships are coming soon. Prices are shown for reference.',
    ],
];
