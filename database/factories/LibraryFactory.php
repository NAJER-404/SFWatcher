<?php

namespace Database\Factories;

use App\Models\Library;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Library>
 */
class LibraryFactory extends Factory
{
    protected $model = Library::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $books = [
            ['title' => 'Clean Code: A Handbook of Agile Software Craftsmanship', 'author' => 'Robert C. Martin', 'genre' => 'Technology'],
            ['title' => 'The Pragmatic Programmer', 'author' => 'Andrew Hunt & David Thomas', 'genre' => 'Technology'],
            ['title' => 'Design Patterns: Elements of Reusable Object-Oriented Software', 'author' => 'Erich Gamma et al.', 'genre' => 'Technology'],
            ['title' => 'Refactoring: Improving the Design of Existing Code', 'author' => 'Martin Fowler', 'genre' => 'Technology'],
            ['title' => 'To Kill a Mockingbird', 'author' => 'Harper Lee', 'genre' => 'Classic Fiction'],
            ['title' => '1984', 'author' => 'George Orwell', 'genre' => 'Dystopian'],
            ['title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'genre' => 'Classic Fiction'],
            ['title' => 'Dune', 'author' => 'Frank Herbert', 'genre' => 'Sci-Fi'],
            ['title' => 'Project Hail Mary', 'author' => 'Andy Weir', 'genre' => 'Sci-Fi'],
            ['title' => 'Atomic Habits', 'author' => 'James Clear', 'genre' => 'Self-Help'],
            ['title' => 'Thinking, Fast and Slow', 'author' => 'Daniel Kahneman', 'genre' => 'Psychology'],
            ['title' => 'Sapiens: A Brief History of Humankind', 'author' => 'Yuval Noah Harari', 'genre' => 'History'],
            ['title' => 'Deep Work', 'author' => 'Cal Newport', 'genre' => 'Productivity'],
            ['title' => 'The Psychology of Money', 'author' => 'Morgan Housel', 'genre' => 'Finance'],
            ['title' => 'Zero to One', 'author' => 'Peter Thiel', 'genre' => 'Business'],
        ];

        $selected = $this->faker->randomElement($books);

        return [
            'Title' => $selected['title'],
            'Author' => $selected['author'],
            'Genre' => $selected['genre'],
            'Quantity' => $this->faker->numberBetween(5, 50),
            'Price' => $this->faker->numberBetween(15, 65),
        ];
    }
}
