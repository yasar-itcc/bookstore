<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Language;
use App\Models\Publisher;
use Illuminate\Database\Seeder;

/**
 * Sample catalog for trying out the store: categories like Kitabay / Bookchor, with 10 well-known books in each.
 * Prices and stock are sample values; descriptions are our own words. Safe to run more than once.
 *
 * php artisan db:seed --class=SampleCatalogSeeder
 */
class SampleCatalogSeeder extends Seeder
{
    private const CATEGORY_TREE = [
        'Fiction' => ['Classic Novels', 'Romance', 'Mystery & Thriller', 'Fantasy', 'Science Fiction', 'Indian Fiction'],
        'Non-Fiction' => ['Self Help', 'Biography', 'Philosophy'],
        'Young Adult' => [],
        'Kids Books' => [],
        'Hindi Books' => [],
        'Competitive Exams' => [],
    ];

    private const PUBLISHERS = [
        'PRH' => ['Penguin Random House India', 'https://www.penguin.co.in'],
        'Harper' => ['HarperCollins India', 'https://harpercollins.co.in'],
        'Bloomsbury' => ['Bloomsbury', 'https://www.bloomsbury.com'],
        'Hodder' => ['Hodder & Stoughton', 'https://www.hodder.co.uk'],
        'Orion' => ['Orion Publishing', 'https://www.orionbooks.co.uk'],
        'S&S' => ['Simon & Schuster', 'https://www.simonandschuster.co.in'],
        'McGraw' => ['McGraw Hill', 'https://www.mheducation.co.in'],
        'UP' => ['Universities Press', 'https://www.universitiespress.com'],
        'Faber' => ['Faber & Faber', 'https://www.faber.co.uk'],
        'Pan' => ['Pan Macmillan', 'https://www.panmacmillan.com'],
        'Rupa' => ['Rupa Publications', 'https://www.rupapublications.co.in'],
        'Westland' => ['Westland Books', 'https://www.westlandbooks.in'],
        'Jaico' => ['Jaico Publishing House', 'https://www.jaicobooks.com'],
        'Hachette' => ['Hachette India', 'https://www.hachetteindia.com'],
        'BBT' => ['The Bhaktivedanta Book Trust', 'https://www.bbt.org'],
        'Scholastic' => ['Scholastic India', 'https://www.scholastic.co.in'],
        'Rajpal' => ['Rajpal & Sons', 'https://www.rajpalpublishing.com'],
        'Rajkamal' => ['Rajkamal Prakashan', 'https://www.rajkamalprakashan.com'],
        'Jnanpith' => ['Bharatiya Jnanpith', null],
        'SChand' => ['S. Chand Publishing', 'https://www.schandpublishing.com'],
        'Spectrum' => ['Spectrum Books', null],
        'OUP' => ['Oxford University Press', 'https://india.oup.com'],
        'Arihant' => ['Arihant Publications', 'https://www.arihantbooks.com'],
    ];

    private const FEATURED = [
        'Atomic Habits', 'The Alchemist', 'The Silent Patient', 'Harry Potter and the Philosopher\'s Stone',
        'The Psychology of Money', 'Wings of Fire', 'Ikigai', 'It Ends with Us',
    ];

    public function run(): void
    {
        $categories = $this->categories();
        $publishers = collect(self::PUBLISHERS)->map(fn ($p) => Publisher::firstOrCreate(['name' => $p[0]], ['website' => $p[1], 'is_active' => true]));
        $languages = Language::pluck('id', 'code');

        foreach ($this->books() as $categoryName => $books) {
            foreach ($books as [$title, $authors, $publisher, $year, $description, $formats, $subtitle]) {
                $book = Book::updateOrCreate(['title' => $title], [
                    'subtitle' => $subtitle,
                    'description' => $description,
                    'category_id' => $categories[$categoryName]->id,
                    'publisher_id' => $publisher ? $publishers[$publisher]->id : null,
                    'language_id' => $languages[$categoryName === 'Hindi Books' ? 'hi' : 'en'] ?? null,
                    'published_year' => $year,
                    'is_featured' => in_array($title, self::FEATURED, true),
                    'is_active' => true,
                ]);

                $book->authors()->sync(collect((array) $authors)->map(
                    fn ($name) => Author::firstOrCreate(['name' => $name], ['is_active' => true])->id
                ));

                foreach (['p' => 'paperback', 'h' => 'hardcover', 'e' => 'ebook'] as $key => $format) {
                    if (! isset($formats[$key])) {
                        $book->formats()->where('format', $format)->whereNull('ebook_file')->delete();

                        continue;
                    }
                    [$mrp, $price, $stock] = $formats[$key] + [2 => null];
                    $book->formats()->updateOrCreate(['format' => $format], [
                        'mrp' => $mrp,
                        'price' => $price,
                        'stock' => $format === 'ebook' ? null : $stock,
                        'gst_rate' => $format === 'ebook' ? 18 : 0,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }

    /**
     * @return array<string, Category>
     */
    private function categories(): array
    {
        $categories = [];
        $order = 0;

        foreach (self::CATEGORY_TREE as $parentName => $children) {
            $parent = Category::firstOrCreate(['name' => $parentName], ['is_active' => true]);
            $parent->update(['parent_id' => null, 'sort_order' => $order++]);
            $categories[$parentName] = $parent;

            foreach ($children as $childOrder => $childName) {
                $child = Category::firstOrCreate(['name' => $childName], ['is_active' => true]);
                $child->update(['parent_id' => $parent->id, 'sort_order' => $childOrder]);
                $categories[$childName] = $child;
            }
        }

        return $categories;
    }

    /**
     * Each book: [title, author(s), publisher key, year first published, description, formats, subtitle].
     * Formats: p = paperback, h = hardcover => [MRP, price, stock]; e = eBook => [MRP, price].
     */
    private function books(): array
    {
        return [
            'Classic Novels' => [
                ['Pride and Prejudice', 'Jane Austen', 'PRH', 1813, 'Elizabeth Bennet and the proud Mr Darcy misjudge each other at every turn in this witty story of first impressions and marriage.', ['p' => [250, 179, 30], 'e' => [99, 49]], null],
                ['1984', 'George Orwell', 'PRH', 1949, 'Winston Smith dares to think for himself in a state where Big Brother watches everyone. A chilling classic about surveillance and truth.', ['p' => [299, 199, 3]], null],
                ['To Kill a Mockingbird', 'Harper Lee', 'PRH', 1960, 'Young Scout Finch watches her father defend a Black man falsely accused of a crime in a small Alabama town.', ['p' => [399, 299, 18]], null],
                ['Jane Eyre', 'Charlotte Brontë', 'PRH', 1847, 'An orphaned governess finds love at Thornfield Hall, until a dark secret in the house changes everything.', ['p' => [250, 169, 12]], null],
                ['Wuthering Heights', 'Emily Brontë', 'PRH', 1847, 'The wild, doomed passion between Catherine and Heathcliff shapes two families on the Yorkshire moors.', ['p' => [250, 169, 0]], null],
                ['Great Expectations', 'Charles Dickens', 'PRH', 1861, 'Orphan Pip receives a fortune from a mysterious benefactor and learns hard lessons about wealth, class and loyalty.', ['p' => [299, 199, 10]], null],
                ['The Great Gatsby', 'F. Scott Fitzgerald', 'PRH', 1925, 'Nick Carraway is drawn into the glittering world of his neighbour Jay Gatsby and his longing for the lost Daisy.', ['p' => [199, 149, 25], 'e' => [99, 49]], null],
                ['Animal Farm', 'George Orwell', 'PRH', 1945, 'The animals of Manor Farm overthrow their farmer, only to find their new leaders becoming just as cruel.', ['p' => [199, 129, 40]], null],
                ['Little Women', 'Louisa May Alcott', 'PRH', 1868, 'The four March sisters grow up through hardship and joy while their father is away at war.', ['p' => [299, 219, 8]], null],
                ['Crime and Punishment', 'Fyodor Dostoevsky', 'PRH', 1866, 'A poor student commits a murder and is consumed by guilt in this gripping psychological novel.', ['p' => [399, 299, 6]], null],
            ],
            'Romance' => [
                ['It Ends with Us', 'Colleen Hoover', 'S&S', 2016, 'Lily falls for a charming neurosurgeon in Boston, but her first love returns and forces a difficult choice.', ['p' => [499, 299, 35]], null],
                ['It Starts with Us', 'Colleen Hoover', 'S&S', 2022, 'Lily and Atlas finally get a second chance at love, but the past is not ready to let them go.', ['p' => [499, 319, 20]], null],
                ['Ugly Love', 'Colleen Hoover', 'S&S', 2014, 'Tate and Miles agree to a relationship with two rules: no questions about the past and no future.', ['p' => [399, 279, 15]], null],
                ['Me Before You', 'Jojo Moyes', 'PRH', 2012, 'A cheerful small-town carer and the man she looks after change each other\'s lives in unexpected ways.', ['p' => [499, 349, 10]], null],
                ['The Notebook', 'Nicholas Sparks', null, 1996, 'An old man reads a love story from a faded notebook to a woman in a nursing home.', ['p' => [399, 279, 12]], null],
                ['Outlander', 'Diana Gabaldon', 'PRH', 1991, 'A nurse from 1945 is swept back to 18th-century Scotland and into the arms of a Highland warrior.', ['p' => [599, 449, 5]], null],
                ['Normal People', 'Sally Rooney', 'Faber', 2018, 'Connell and Marianne circle each other through school and university in this tender story of first love.', ['p' => [499, 375, 9]], null],
                ['I Too Had a Love Story', 'Ravinder Singh', 'PRH', 2008, 'A heartfelt true story of a young couple who meet online and plan a life together before tragedy strikes.', ['p' => [199, 149, 30]], null],
                ['Book Lovers', 'Emily Henry', 'PRH', 2022, 'A literary agent on a small-town holiday keeps running into the editor she cannot stand.', ['p' => [499, 349, 11]], null],
                ['Beach Read', 'Emily Henry', 'PRH', 2020, 'Two writers with opposite styles swap genres for a summer and find more than inspiration.', ['p' => [499, 349, 4]], null],
            ],
            'Mystery & Thriller' => [
                ['The Silent Patient', 'Alex Michaelides', 'Orion', 2019, 'A famous painter shoots her husband and never speaks again, and her therapist sets out to learn why.', ['p' => [499, 299, 15], 'e' => [299, 199]], null],
                ['The Girl on the Train', 'Paula Hawkins', 'PRH', 2015, 'A commuter who watches the same couple every day from her train sees something shocking.', ['p' => [399, 279, 10]], null],
                ['Gone Girl', 'Gillian Flynn', 'Orion', 2012, 'On their fifth wedding anniversary Amy disappears, and every clue points to her husband Nick.', ['p' => [499, 349, 8]], null],
                ['The Da Vinci Code', 'Dan Brown', 'PRH', 2003, 'Symbologist Robert Langdon races across Europe to decode a secret hidden in famous works of art.', ['p' => [499, 349, 20]], null],
                ['And Then There Were None', 'Agatha Christie', 'Harper', 1939, 'Ten strangers are invited to an island and begin to die one by one, following an old nursery rhyme.', ['p' => [299, 229, 16]], null],
                ['Murder on the Orient Express', 'Agatha Christie', 'Harper', 1934, 'Hercule Poirot must solve a murder aboard a snowbound train where every passenger is a suspect.', ['p' => [299, 229, 0]], null],
                ['The Hound of the Baskervilles', 'Arthur Conan Doyle', 'PRH', 1902, 'Sherlock Holmes investigates the legend of a ghostly hound haunting a family on the moors.', ['p' => [199, 149, 14], 'e' => [49, 29]], null],
                ['The Girl with the Dragon Tattoo', 'Stieg Larsson', null, 2005, 'A journalist and a brilliant hacker dig into a wealthy family\'s past to solve a forty-year-old disappearance.', ['p' => [499, 375, 6]], null],
                ['Verity', 'Colleen Hoover', null, 2018, 'A struggling writer finishing a famous author\'s book series finds a chilling unfinished autobiography.', ['p' => [499, 329, 13]], null],
                ['The Guest List', 'Lucy Foley', 'Harper', 2020, 'A glamorous wedding on a remote Irish island ends with a body, and everyone has a secret.', ['p' => [499, 349, 9]], null],
            ],
            'Fantasy' => [
                ['Harry Potter and the Philosopher\'s Stone', 'J. K. Rowling', 'Bloomsbury', 1997, 'On his eleventh birthday Harry learns he is a wizard and leaves for Hogwarts, where friendship and danger await.', ['p' => [499, 399, 22], 'h' => [899, 799, 4]], null],
                ['Harry Potter and the Chamber of Secrets', 'J. K. Rowling', 'Bloomsbury', 1998, 'In his second year at Hogwarts, Harry hears a mysterious voice as students are found turned to stone.', ['p' => [499, 399, 17]], null],
                ['Harry Potter and the Prisoner of Azkaban', 'J. K. Rowling', 'Bloomsbury', 1999, 'A dangerous prisoner escapes from Azkaban, and he seems to be coming for Harry.', ['p' => [499, 399, 2]], null],
                ['The Hobbit', 'J. R. R. Tolkien', 'Harper', 1937, 'Bilbo Baggins joins a wizard and thirteen dwarves on a quest to reclaim treasure guarded by the dragon Smaug.', ['p' => [499, 375, 12]], null],
                ['The Fellowship of the Ring', 'J. R. R. Tolkien', 'Harper', 1954, 'Frodo sets out from the Shire with a small band of companions to destroy the One Ring.', ['p' => [599, 449, 7], 'h' => [999, 849, 3]], null],
                ['A Game of Thrones', 'George R. R. Martin', 'Harper', 1996, 'Noble families fight for the Iron Throne while an ancient threat stirs beyond the Wall.', ['p' => [699, 499, 10]], null],
                ['The Name of the Wind', 'Patrick Rothfuss', 'Orion', 2007, 'Kvothe, a legendary musician and magician, tells the true story of his remarkable early life.', ['p' => [599, 449, 5]], null],
                ['Mistborn: The Final Empire', 'Brandon Sanderson', 'Orion', 2006, 'A street thief with rare powers joins a rebel crew planning to overthrow an immortal emperor.', ['p' => [599, 449, 9]], null],
                ['The Lion, the Witch and the Wardrobe', 'C. S. Lewis', 'Harper', 1950, 'Four children step through a wardrobe into Narnia, a land held in endless winter by the White Witch.', ['p' => [299, 229, 15]], null],
                ['Six of Crows', 'Leigh Bardugo', 'Orion', 2015, 'A teenage criminal mastermind assembles a crew of outcasts for an impossible heist.', ['p' => [499, 375, 11]], null],
            ],
            'Science Fiction' => [
                ['Dune', 'Frank Herbert', 'Hodder', 1965, 'On the desert planet Arrakis, young Paul Atreides is caught in a struggle over the universe\'s most precious substance.', ['p' => [599, 449, 0], 'e' => [399, 279]], null],
                ['Project Hail Mary', 'Andy Weir', 'PRH', 2021, 'A lone astronaut wakes up with no memory, far from Earth, on a desperate mission to save humanity.', ['p' => [599, 449, 8]], null],
                ['The Martian', 'Andy Weir', 'PRH', 2011, 'Stranded on Mars, botanist Mark Watney uses science and humour to survive until rescue.', ['p' => [499, 349, 12]], null],
                ['Foundation', 'Isaac Asimov', 'Harper', 1951, 'A mathematician predicts the fall of a galactic empire and creates a plan to shorten the dark age to come.', ['p' => [499, 375, 6]], null],
                ['Ender\'s Game', 'Orson Scott Card', null, 1985, 'A gifted child is trained through war games at a battle school to defend Earth from an alien invasion.', ['p' => [499, 349, 4]], null],
                ['The Hitchhiker\'s Guide to the Galaxy', 'Douglas Adams', 'Pan', 1979, 'Seconds before Earth is demolished, Arthur Dent is rescued by an alien friend for a very silly trip through space.', ['p' => [399, 299, 14]], null],
                ['Brave New World', 'Aldous Huxley', 'PRH', 1932, 'In a world built on comfort and control, one man begins to question the price of happiness.', ['p' => [299, 219, 10]], null],
                ['Fahrenheit 451', 'Ray Bradbury', 'Harper', 1953, 'A fireman whose job is to burn books starts to read them, and his life begins to unravel.', ['p' => [299, 229, 7]], null],
                ['The Three-Body Problem', 'Cixin Liu', null, 2008, 'A secret project during China\'s Cultural Revolution makes contact with an alien civilisation.', ['p' => [599, 449, 3]], null],
                ['Frankenstein', 'Mary Shelley', 'PRH', 1818, 'Victor Frankenstein brings a creature to life and then abandons it, with tragic results for both.', ['p' => [199, 149, 20], 'e' => [49, 29]], null],
            ],
            'Indian Fiction' => [
                ['The Namesake', 'Jhumpa Lahiri', 'Harper', 2003, 'The Ganguli family moves from Calcutta to Massachusetts, and their son Gogol grows up between two cultures.', ['p' => [599, 399, 7]], null],
                ['The White Tiger', 'Aravind Adiga', 'Harper', 2008, 'Balram, a driver from a poor village, tells the darkly funny story of how he rose in modern India.', ['p' => [399, 299, 11]], null],
                ['The God of Small Things', 'Arundhati Roy', 'PRH', 1997, 'Twins Estha and Rahel look back on one fateful summer in Kerala that changed their family forever.', ['p' => [499, 375, 9]], null],
                ['Midnight\'s Children', 'Salman Rushdie', 'PRH', 1981, 'Saleem Sinai, born at the stroke of India\'s independence, discovers his life is bound up with the nation\'s.', ['p' => [599, 449, 4]], null],
                ['A Suitable Boy', 'Vikram Seth', 'PRH', 1993, 'In 1950s India, Mrs Rupa Mehra searches for a suitable husband for her daughter Lata.', ['p' => [999, 799, 3], 'h' => [1499, 1299, 2]], null],
                ['Five Point Someone', 'Chetan Bhagat', 'Rupa', 2004, 'Three friends struggling with grades at IIT learn that life is more than marks.', ['p' => [199, 149, 40], 'e' => [99, 59]], null],
                ['The Immortals of Meluha', 'Amish Tripathi', 'Westland', 2010, 'A Tibetan tribal chief named Shiva is hailed as a saviour in the land of Meluha. Book one of the Shiva Trilogy.', ['p' => [399, 299, 25], 'e' => [199, 129]], null],
                ['Train to Pakistan', 'Khushwant Singh', 'PRH', 1956, 'A quiet village on the India-Pakistan border is torn apart by the violence of Partition in 1947.', ['p' => [299, 219, 13]], null],
                ['The Palace of Illusions', 'Chitra Banerjee Divakaruni', 'Pan', 2008, 'The Mahabharata retold through the eyes of Panchaali, the fiery princess at its heart.', ['p' => [499, 375, 8]], null],
                ['Malgudi Days', 'R. K. Narayan', 'PRH', 1943, 'Gentle, funny short stories about the people of Malgudi, a small town in South India.', ['p' => [299, 219, 16]], null],
            ],
            'Self Help' => [
                ['Atomic Habits', 'James Clear', 'PRH', 2018, 'How tiny changes in daily routines add up to remarkable results, with simple steps to build good habits.', ['p' => [799, 499, 40], 'h' => [999, 799, 6], 'e' => [499, 299]], 'An Easy & Proven Way to Build Good Habits & Break Bad Ones'],
                ['The Psychology of Money', 'Morgan Housel', 'Jaico', 2020, 'Short stories about how people think about money, and why behaviour matters more than knowledge.', ['p' => [399, 299, 30], 'e' => [199, 149]], 'Timeless Lessons on Wealth, Greed, and Happiness'],
                ['Rich Dad Poor Dad', 'Robert T. Kiyosaki', null, 1997, 'Lessons from two father figures on money, assets and why the rich think differently about work.', ['p' => [499, 349, 22]], null],
                ['The 7 Habits of Highly Effective People', 'Stephen R. Covey', 'S&S', 1989, 'A principle-based approach to personal and professional effectiveness, built on seven lasting habits.', ['p' => [599, 449, 10]], null],
                ['How to Win Friends and Influence People', 'Dale Carnegie', 'S&S', 1936, 'Timeless advice on getting along with people, making a good impression and leading with kindness.', ['p' => [299, 199, 28]], null],
                ['Think and Grow Rich', 'Napoleon Hill', null, 1937, 'Principles of success drawn from studying hundreds of high achievers, starting with a burning desire.', ['p' => [199, 149, 35]], null],
                ['The Power of Your Subconscious Mind', 'Joseph Murphy', null, 1963, 'How the beliefs held in your subconscious shape your health, relationships and success.', ['p' => [199, 129, 19]], null],
                ['Deep Work', 'Cal Newport', null, 2016, 'Why focused, distraction-free work is rare and valuable, and how to train yourself to do it.', ['p' => [499, 375, 7]], null],
                ['Can\'t Hurt Me', 'David Goggins', null, 2018, 'A former Navy SEAL shares how he overcame a hard childhood and how to push past your own limits.', ['p' => [699, 499, 5]], null],
                ['You Can Win', 'Shiv Khera', 'Bloomsbury', 1998, 'A practical guide to positive thinking, attitude and building the habits that lead to success.', ['p' => [399, 299, 14]], null],
            ],
            'Biography' => [
                ['Wings of Fire', 'A. P. J. Abdul Kalam', 'UP', 1999, 'The autobiography of A. P. J. Abdul Kalam, from his childhood in Rameswaram to leading India\'s missile programme.', ['p' => [350, 299, 4], 'e' => [199, 149]], 'An Autobiography'],
                ['The Story of My Experiments with Truth', 'Mahatma Gandhi', null, 1927, 'Gandhi\'s own account of his life up to 1921, and the ideas of truth and non-violence that guided him.', ['p' => [199, 149, 20]], null],
                ['Steve Jobs', 'Walter Isaacson', 'S&S', 2011, 'The life of Apple\'s co-founder, based on interviews with Jobs, his family, friends and rivals.', ['p' => [799, 599, 6], 'h' => [1299, 999, 2]], null],
                ['Becoming', 'Michelle Obama', 'PRH', 2018, 'The former First Lady of the United States tells her story, from Chicago\'s South Side to the White House.', ['p' => [699, 499, 9]], null],
                ['The Diary of a Young Girl', 'Anne Frank', 'PRH', 1947, 'The diary kept by a Jewish teenager hiding from the Nazis in Amsterdam during the Second World War.', ['p' => [299, 219, 15]], null],
                ['Long Walk to Freedom', 'Nelson Mandela', null, 1994, 'Mandela\'s autobiography, from his rural childhood and long imprisonment to becoming South Africa\'s president.', ['p' => [699, 549, 3]], null],
                ['I Am Malala', 'Malala Yousafzai', 'Orion', 2013, 'The girl who stood up for education in Pakistan, survived an attack and became a global voice for change.', ['p' => [499, 349, 8]], null],
                ['Playing It My Way', 'Sachin Tendulkar', 'Hachette', 2014, 'Sachin Tendulkar looks back on his life and his record-breaking 24 years in international cricket.', ['p' => [699, 499, 12], 'h' => [999, 849, 4]], null],
                ['An Autobiography', 'Jawaharlal Nehru', 'PRH', 1936, 'Nehru\'s account of his life and India\'s freedom struggle, much of it written in prison.', ['p' => [599, 449, 5]], null],
                ['Elon Musk', 'Walter Isaacson', 'S&S', 2023, 'An inside look at the founder of Tesla and SpaceX, based on two years spent shadowing him.', ['h' => [1499, 1199, 7], 'e' => [799, 599]], null],
            ],
            'Philosophy' => [
                ['The Alchemist', 'Paulo Coelho', 'Harper', 1988, 'A young shepherd travels to the Egyptian desert in search of treasure and learns to follow his dreams.', ['p' => [399, 279, 25], 'e' => [199, 149]], null],
                ['Ikigai', ['Héctor García', 'Francesc Miralles'], 'PRH', 2016, 'Lessons from the long-lived people of Okinawa on finding purpose and living with meaning.', ['h' => [499, 375, 18], 'e' => [299, 199]], 'The Japanese Secret to a Long and Happy Life'],
                ['Meditations', 'Marcus Aurelius', 'PRH', null, 'Private reflections of a Roman emperor on duty, self-control and calm in the face of hardship.', ['p' => [299, 219, 13]], null],
                ['The Prophet', 'Kahlil Gibran', 'Rupa', 1923, 'Poetic essays on love, work, joy, sorrow and other parts of life, spoken by a departing prophet.', ['p' => [150, 125, 24]], null],
                ['Man\'s Search for Meaning', 'Viktor E. Frankl', 'PRH', 1946, 'A psychiatrist\'s account of surviving Nazi camps, and his belief that meaning can be found in any life.', ['p' => [299, 229, 17]], null],
                ['Siddhartha', 'Hermann Hesse', null, 1922, 'A young man leaves his privileged life in ancient India on a lifelong search for enlightenment.', ['p' => [199, 149, 11]], null],
                ['Sophie\'s World', 'Jostein Gaarder', 'Orion', 1991, 'A teenage girl receives mysterious letters that lead her through the history of Western philosophy.', ['p' => [499, 375, 6]], null],
                ['The Monk Who Sold His Ferrari', 'Robin Sharma', 'Jaico', 1997, 'A burnt-out lawyer travels to the Himalayas and returns with lessons for a more balanced life.', ['p' => [299, 199, 27]], null],
                ['The Power of Now', 'Eckhart Tolle', null, 1997, 'A guide to spiritual awakening through living fully in the present moment.', ['p' => [399, 299, 9]], null],
                ['Bhagavad Gita As It Is', 'A. C. Bhaktivedanta Swami Prabhupada', 'BBT', 1968, 'The Bhagavad Gita in Sanskrit with translation and detailed commentary.', ['h' => [450, 350, 20]], null],
            ],
            'Young Adult' => [
                ['The Fault in Our Stars', 'John Green', 'PRH', 2012, 'Two teenagers who meet at a cancer support group share a funny, honest and heartbreaking love story.', ['p' => [450, 299, 9]], null],
                ['Looking for Alaska', 'John Green', 'Harper', 2005, 'At boarding school, Miles falls for the brilliant and unpredictable Alaska Young.', ['p' => [399, 299, 6]], null],
                ['The Hunger Games', 'Suzanne Collins', 'Scholastic', 2008, 'Katniss volunteers to take her sister\'s place in a televised fight to the death.', ['p' => [499, 375, 18]], null],
                ['Catching Fire', 'Suzanne Collins', 'Scholastic', 2009, 'After winning the Games, Katniss becomes a symbol of rebellion and is forced back into the arena.', ['p' => [499, 375, 10]], null],
                ['Divergent', 'Veronica Roth', 'Harper', 2011, 'In a city divided into factions, Tris discovers she does not fit any one group, and that makes her a target.', ['p' => [499, 349, 7]], null],
                ['The Perks of Being a Wallflower', 'Stephen Chbosky', 'S&S', 1999, 'Through letters to an unknown friend, shy Charlie describes his first year of high school.', ['p' => [399, 299, 12]], null],
                ['Wonder', 'R. J. Palacio', 'PRH', 2012, 'Auggie, born with a facial difference, starts mainstream school for the first time in fifth grade.', ['p' => [399, 299, 21]], null],
                ['The Book Thief', 'Markus Zusak', 'PRH', 2005, 'In Nazi Germany, a foster girl steals books and shares them with neighbours and the man hidden in her basement.', ['p' => [499, 375, 5]], null],
                ['A Good Girl\'s Guide to Murder', 'Holly Jackson', null, 2019, 'For a school project, Pip reinvestigates a closed murder case in her quiet English town.', ['p' => [499, 349, 14]], null],
                ['The Maze Runner', 'James Dashner', null, 2009, 'Thomas wakes in a glade surrounded by a deadly, ever-changing maze, with no memory of who he is.', ['p' => [399, 299, 0]], null],
            ],
            'Kids Books' => [
                ['The Very Hungry Caterpillar', 'Eric Carle', 'PRH', 1969, 'A tiny caterpillar eats its way through a week of food before turning into a beautiful butterfly.', ['h' => [499, 399, 14]], null],
                ['Charlie and the Chocolate Factory', 'Roald Dahl', 'PRH', 1964, 'Charlie Bucket wins a golden ticket to tour Willy Wonka\'s magical chocolate factory.', ['p' => [399, 299, 20]], null],
                ['Matilda', 'Roald Dahl', 'PRH', 1988, 'A brilliant little girl with awful parents and a terrifying headmistress discovers she has special powers.', ['p' => [399, 299, 16]], null],
                ['The Gruffalo', 'Julia Donaldson', 'Pan', 1999, 'A clever mouse invents a scary monster to frighten off hungry animals in the deep dark wood.', ['p' => [399, 319, 12]], null],
                ['Diary of a Wimpy Kid', 'Jeff Kinney', 'PRH', 2007, 'Greg Heffley records the ups and (mostly) downs of middle school in his hilarious illustrated journal.', ['p' => [399, 299, 25]], null],
                ['Grandma\'s Bag of Stories', 'Sudha Murty', 'PRH', 2012, 'Children spending the summer with their grandparents hear wonderful stories from Grandma\'s bag.', ['p' => [250, 199, 30]], null],
                ['The Magic of the Lost Temple', 'Sudha Murty', 'PRH', 2015, 'City girl Nooni visits her grandparents in a Karnataka village and sets out to find a lost temple.', ['p' => [250, 199, 3]], null],
                ['Charlotte\'s Web', 'E. B. White', 'PRH', 1952, 'A pig named Wilbur is saved by the friendship of a clever spider named Charlotte.', ['p' => [299, 229, 9]], null],
                ['Five on a Treasure Island', 'Enid Blyton', 'Hodder', 1942, 'The Famous Five explore Kirrin Island and its shipwreck in their very first adventure.', ['p' => [250, 199, 18]], null],
                ['Lost Treasure of the Emerald Eye', 'Geronimo Stilton', 'Scholastic', 2004, 'Mouse journalist Geronimo is dragged on a treasure hunt to a mysterious island. Fun, colourful text for young readers.', ['p' => [299, 249, 11]], null],
            ],
            'Hindi Books' => [
                ['Godaan', 'Munshi Premchand', null, 1936, 'Premchand\'s great novel about the poor farmer Hori and his dream of owning a cow, painting village life in colonial India.', ['p' => [250, 175, 20], 'e' => [99, 69]], 'गोदान'],
                ['Gaban', 'Munshi Premchand', null, 1931, 'A young clerk\'s wish to please his wife leads him to embezzle money, with serious consequences.', ['p' => [199, 149, 15]], 'ग़बन'],
                ['Nirmala', 'Munshi Premchand', null, 1927, 'A young woman married to a much older widower faces suspicion and sorrow in a dowry-bound society.', ['p' => [150, 120, 12]], 'निर्मला'],
                ['Madhushala', 'Harivansh Rai Bachchan', 'Rajpal', 1935, 'Bachchan\'s famous collection of quatrains using the tavern as a metaphor for life.', ['h' => [250, 199, 10]], 'मधुशाला'],
                ['Raag Darbari', 'Shrilal Shukla', 'Rajkamal', 1968, 'A sharp satire on politics and power in a north Indian village called Shivpalganj.', ['p' => [399, 299, 6]], 'राग दरबारी'],
                ['Gunahon Ka Devta', 'Dharamvir Bharati', 'Jnanpith', 1949, 'A much-loved, tender story of the unspoken love between Chander and Sudha in Allahabad.', ['p' => [299, 225, 14]], 'गुनाहों का देवता'],
                ['Rashmirathi', 'Ramdhari Singh Dinkar', null, 1952, 'An epic poem retelling the life of Karna from the Mahabharata.', ['p' => [199, 149, 9]], 'रश्मिरथी'],
                ['Tamas', 'Bhisham Sahni', 'Rajkamal', 1973, 'A powerful novel about communal violence in a Punjab town in the days before Partition.', ['p' => [399, 299, 4]], 'तमस'],
                ['Maila Anchal', 'Phanishwar Nath Renu', 'Rajkamal', 1954, 'A landmark regional novel about life, politics and change in a village in Bihar.', ['p' => [399, 299, 7]], 'मैला आँचल'],
                ['Chitralekha', 'Bhagwati Charan Verma', 'Rajkamal', 1934, 'A philosophical novel that asks what sin is, through the story of the dancer Chitralekha.', ['p' => [250, 199, 0]], 'चित्रलेखा'],
            ],
            'Competitive Exams' => [
                ['Indian Polity', 'M. Laxmikanth', 'McGraw', null, 'A widely used guide to the Indian Constitution and political system for UPSC and state exams.', ['p' => [945, 799, 2]], 'For Civil Services and Other State Examinations'],
                ['Quantitative Aptitude for Competitive Examinations', 'R. S. Aggarwal', 'SChand', null, 'Arithmetic topics explained with solved examples and practice questions for bank, SSC and other exams.', ['p' => [895, 699, 15]], null],
                ['A Modern Approach to Verbal & Non-Verbal Reasoning', 'R. S. Aggarwal', 'SChand', null, 'Reasoning concepts and thousands of practice questions for competitive examinations.', ['p' => [795, 625, 10]], null],
                ['Word Power Made Easy', 'Norman Lewis', null, 1949, 'A step-by-step programme to build a richer English vocabulary in a few weeks.', ['p' => [299, 199, 40]], null],
                ['Indian Economy', 'Ramesh Singh', 'McGraw', null, 'Concepts and current issues in the Indian economy for civil services and other exams.', ['p' => [899, 749, 5]], null],
                ['A Brief History of Modern India', 'Rajiv Ahir', 'Spectrum', null, 'A concise account of modern Indian history, popular with UPSC aspirants.', ['p' => [545, 449, 12]], null],
                ['Certificate Physical and Human Geography', 'Goh Cheng Leong', 'OUP', null, 'A classic introduction to physical and human geography with clear diagrams.', ['p' => [495, 425, 8]], null],
                ['Objective General English', 'S. P. Bakshi', 'Arihant', null, 'Grammar, vocabulary and comprehension practice for competitive examinations.', ['p' => [495, 375, 17]], null],
                ['India\'s Struggle for Independence', ['Bipan Chandra'], 'PRH', 1988, 'A detailed history of the Indian national movement from 1857 to 1947.', ['p' => [599, 449, 6]], null],
                ['Fast Track Objective Arithmetic', 'Rajesh Verma', 'Arihant', null, 'Short-cut methods and practice sets to solve arithmetic questions quickly.', ['p' => [575, 425, 0]], null],
            ],
        ];
    }
}
