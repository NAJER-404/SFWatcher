<?php

namespace Database\Seeders;

use App\Models\Library;
use Illuminate\Database\Seeder;

class LibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = [
            [
                'Title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'Author' => 'Robert C. Martin',
                'Genre' => 'Technology',
                'Quantity' => 15,
                'Price' => 45,
            ],
            [
                'Title' => 'The Pragmatic Programmer: Your Journey To Mastery',
                'Author' => 'David Thomas & Andrew Hunt',
                'Genre' => 'Technology',
                'Quantity' => 20,
                'Price' => 50,
            ],
            [
                'Title' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
                'Author' => 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides',
                'Genre' => 'Technology',
                'Quantity' => 10,
                'Price' => 55,
            ],
            [
                'Title' => 'To Kill a Mockingbird',
                'Author' => 'Harper Lee',
                'Genre' => 'Classic Fiction',
                'Quantity' => 30,
                'Price' => 18,
            ],
            [
                'Title' => '1984',
                'Author' => 'George Orwell',
                'Genre' => 'Dystopian Fiction',
                'Quantity' => 25,
                'Price' => 16,
            ],
            [
                'Title' => 'The Great Gatsby',
                'Author' => 'F. Scott Fitzgerald',
                'Genre' => 'Classic Fiction',
                'Quantity' => 22,
                'Price' => 15,
            ],
            [
                'Title' => 'Dune',
                'Author' => 'Frank Herbert',
                'Genre' => 'Science Fiction',
                'Quantity' => 18,
                'Price' => 25,
            ],
            [
                'Title' => 'Project Hail Mary',
                'Author' => 'Andy Weir',
                'Genre' => 'Science Fiction',
                'Quantity' => 28,
                'Price' => 28,
            ],
            [
                'Title' => 'Atomic Habits: An Easy & Proven Way to Build Good Habits',
                'Author' => 'James Clear',
                'Genre' => 'Self-Improvement',
                'Quantity' => 40,
                'Price' => 22,
            ],
            [
                'Title' => 'Sapiens: A Brief History of Humankind',
                'Author' => 'Yuval Noah Harari',
                'Genre' => 'History / Non-Fiction',
                'Quantity' => 16,
                'Price' => 30,
            ],
            [
                'Title' => 'Thinking, Fast and Slow',
                'Author' => 'Daniel Kahneman',
                'Genre' => 'Psychology',
                'Quantity' => 12,
                'Price' => 24,
            ],
            [
                'Title' => 'The Psychology of Money',
                'Author' => 'Morgan Housel',
                'Genre' => 'Personal Finance',
                'Quantity' => 35,
                'Price' => 20,
            ],
        ];

        foreach ($books as $book) {
            Library::updateOrCreate(
                ['Title' => $book['Title']],
                $book
            );
        }
    }
}
