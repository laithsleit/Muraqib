<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@quiz.com'],
            [
                'name' => 'Dr. Sarah Johnson',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $teacher->addRole('teacher');

        $student1 = User::firstOrCreate(
            ['email' => 'student1@quiz.com'],
            [
                'name' => 'Ahmed Al-Rashid',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $student1->addRole('student');

        $student2 = User::firstOrCreate(
            ['email' => 'student2@quiz.com'],
            [
                'name' => 'Lena Fischer',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $student2->addRole('student');

        // Subjects
        $webDev = Subject::firstOrCreate(
            ['name' => 'Web Development', 'teacher_id' => $teacher->id],
            ['description' => 'Full-stack web development covering HTML, CSS, JavaScript, and PHP with Laravel.']
        );

        $databases = Subject::firstOrCreate(
            ['name' => 'Database Systems', 'teacher_id' => $teacher->id],
            ['description' => 'Relational database design, SQL queries, normalization, and indexing strategies.']
        );

        $networking = Subject::firstOrCreate(
            ['name' => 'Computer Networking', 'teacher_id' => $teacher->id],
            ['description' => 'Network protocols, TCP/IP, routing, and network security fundamentals.']
        );

        // Enroll both students in all subjects
        foreach ([$webDev, $databases, $networking] as $subject) {
            $subject->students()->syncWithoutDetaching([$student1->id, $student2->id]);
        }

        // --- Web Development Quizzes ---

        $htmlQuiz = Quiz::firstOrCreate(
            ['title' => 'HTML & CSS Basics', 'subject_id' => $webDev->id],
            [
                'description' => 'Test your knowledge of HTML elements, attributes, and CSS selectors.',
                'duration_minutes' => 15,
                'is_published' => true,
                'score_threshold' => config('anticheat.default_threshold'),
            ]
        );

        $this->seedQuestions($htmlQuiz, [
            [
                'text' => 'Which HTML tag is used to define an internal stylesheet?',
                'options' => [
                    ['text' => '<css>', 'correct' => false],
                    ['text' => '<style>', 'correct' => true],
                    ['text' => '<script>', 'correct' => false],
                    ['text' => '<link>', 'correct' => false],
                ],
            ],
            [
                'text' => 'Which CSS property is used to change the text color of an element?',
                'options' => [
                    ['text' => 'font-color', 'correct' => false],
                    ['text' => 'text-color', 'correct' => false],
                    ['text' => 'color', 'correct' => true],
                    ['text' => 'foreground-color', 'correct' => false],
                ],
            ],
            [
                'text' => 'What does the "href" attribute in an anchor tag specify?',
                'options' => [
                    ['text' => 'The tooltip text', 'correct' => false],
                    ['text' => 'The URL the link points to', 'correct' => true],
                    ['text' => 'The link color', 'correct' => false],
                    ['text' => 'The link weight', 'correct' => false],
                ],
            ],
            [
                'text' => 'Which HTML element is used for the largest heading?',
                'options' => [
                    ['text' => '<h6>', 'correct' => false],
                    ['text' => '<heading>', 'correct' => false],
                    ['text' => '<head>', 'correct' => false],
                    ['text' => '<h1>', 'correct' => true],
                ],
            ],
            [
                'text' => 'Which CSS property controls the space between elements?',
                'options' => [
                    ['text' => 'spacing', 'correct' => false],
                    ['text' => 'margin', 'correct' => true],
                    ['text' => 'border', 'correct' => false],
                    ['text' => 'gap-size', 'correct' => false],
                ],
            ],
        ]);

        $jsQuiz = Quiz::firstOrCreate(
            ['title' => 'JavaScript Fundamentals', 'subject_id' => $webDev->id],
            [
                'description' => 'Variables, data types, functions, and DOM manipulation basics.',
                'duration_minutes' => 20,
                'is_published' => true,
                'score_threshold' => 50,
            ]
        );

        $this->seedQuestions($jsQuiz, [
            [
                'text' => 'Which keyword declares a block-scoped variable in JavaScript?',
                'options' => [
                    ['text' => 'var', 'correct' => false],
                    ['text' => 'let', 'correct' => true],
                    ['text' => 'define', 'correct' => false],
                    ['text' => 'int', 'correct' => false],
                ],
            ],
            [
                'text' => 'What does "===" check in JavaScript?',
                'options' => [
                    ['text' => 'Value only', 'correct' => false],
                    ['text' => 'Value and type', 'correct' => true],
                    ['text' => 'Type only', 'correct' => false],
                    ['text' => 'Reference equality', 'correct' => false],
                ],
            ],
            [
                'text' => 'Which method adds an element to the end of an array?',
                'options' => [
                    ['text' => 'append()', 'correct' => false],
                    ['text' => 'push()', 'correct' => true],
                    ['text' => 'add()', 'correct' => false],
                    ['text' => 'insert()', 'correct' => false],
                ],
            ],
            [
                'text' => 'What is the output of typeof null in JavaScript?',
                'options' => [
                    ['text' => '"null"', 'correct' => false],
                    ['text' => '"undefined"', 'correct' => false],
                    ['text' => '"object"', 'correct' => true],
                    ['text' => '"boolean"', 'correct' => false],
                ],
            ],
        ]);

        $laravelQuiz = Quiz::firstOrCreate(
            ['title' => 'Laravel Basics', 'subject_id' => $webDev->id],
            [
                'description' => 'Routing, Blade templating, Eloquent ORM, and middleware.',
                'duration_minutes' => 25,
                'is_published' => false,
                'score_threshold' => 40,
            ]
        );

        $this->seedQuestions($laravelQuiz, [
            [
                'text' => 'Which Artisan command creates a new controller?',
                'options' => [
                    ['text' => 'php artisan make:controller', 'correct' => true],
                    ['text' => 'php artisan create:controller', 'correct' => false],
                    ['text' => 'php artisan new:controller', 'correct' => false],
                    ['text' => 'php artisan controller:make', 'correct' => false],
                ],
            ],
            [
                'text' => 'What is the Blade directive for conditional rendering?',
                'options' => [
                    ['text' => '@when', 'correct' => false],
                    ['text' => '@if', 'correct' => true],
                    ['text' => '@check', 'correct' => false],
                    ['text' => '@condition', 'correct' => false],
                ],
            ],
            [
                'text' => 'Which Eloquent method retrieves a single record by primary key?',
                'options' => [
                    ['text' => 'get()', 'correct' => false],
                    ['text' => 'first()', 'correct' => false],
                    ['text' => 'find()', 'correct' => true],
                    ['text' => 'fetch()', 'correct' => false],
                ],
            ],
        ]);

        // --- Database Systems Quizzes ---

        $sqlQuiz = Quiz::firstOrCreate(
            ['title' => 'SQL Fundamentals', 'subject_id' => $databases->id],
            [
                'description' => 'SELECT statements, JOINs, WHERE clauses, and aggregate functions.',
                'duration_minutes' => 20,
                'is_published' => true,
                'score_threshold' => config('anticheat.default_threshold'),
            ]
        );

        $this->seedQuestions($sqlQuiz, [
            [
                'text' => 'Which SQL clause is used to filter records?',
                'options' => [
                    ['text' => 'FILTER', 'correct' => false],
                    ['text' => 'WHERE', 'correct' => true],
                    ['text' => 'HAVING', 'correct' => false],
                    ['text' => 'SELECT', 'correct' => false],
                ],
            ],
            [
                'text' => 'Which JOIN returns all rows from both tables?',
                'options' => [
                    ['text' => 'INNER JOIN', 'correct' => false],
                    ['text' => 'LEFT JOIN', 'correct' => false],
                    ['text' => 'FULL OUTER JOIN', 'correct' => true],
                    ['text' => 'CROSS JOIN', 'correct' => false],
                ],
            ],
            [
                'text' => 'Which function returns the number of rows in a result set?',
                'options' => [
                    ['text' => 'SUM()', 'correct' => false],
                    ['text' => 'TOTAL()', 'correct' => false],
                    ['text' => 'COUNT()', 'correct' => true],
                    ['text' => 'NUM()', 'correct' => false],
                ],
            ],
            [
                'text' => 'What does the DISTINCT keyword do?',
                'options' => [
                    ['text' => 'Sorts the results', 'correct' => false],
                    ['text' => 'Removes duplicate rows', 'correct' => true],
                    ['text' => 'Limits the results', 'correct' => false],
                    ['text' => 'Groups the results', 'correct' => false],
                ],
            ],
            [
                'text' => 'Which normal form eliminates transitive dependencies?',
                'options' => [
                    ['text' => '1NF', 'correct' => false],
                    ['text' => '2NF', 'correct' => false],
                    ['text' => '3NF', 'correct' => true],
                    ['text' => 'BCNF', 'correct' => false],
                ],
            ],
        ]);

        // --- Computer Networking Quizzes ---

        $networkQuiz = Quiz::firstOrCreate(
            ['title' => 'Networking Protocols', 'subject_id' => $networking->id],
            [
                'description' => 'TCP/IP model, HTTP, DNS, and transport layer protocols.',
                'duration_minutes' => 15,
                'is_published' => true,
                'score_threshold' => 45,
            ]
        );

        $this->seedQuestions($networkQuiz, [
            [
                'text' => 'Which protocol is used to resolve domain names to IP addresses?',
                'options' => [
                    ['text' => 'FTP', 'correct' => false],
                    ['text' => 'DHCP', 'correct' => false],
                    ['text' => 'DNS', 'correct' => true],
                    ['text' => 'ARP', 'correct' => false],
                ],
            ],
            [
                'text' => 'Which transport layer protocol provides reliable, ordered delivery?',
                'options' => [
                    ['text' => 'UDP', 'correct' => false],
                    ['text' => 'TCP', 'correct' => true],
                    ['text' => 'ICMP', 'correct' => false],
                    ['text' => 'IP', 'correct' => false],
                ],
            ],
            [
                'text' => 'What is the default port number for HTTP?',
                'options' => [
                    ['text' => '443', 'correct' => false],
                    ['text' => '21', 'correct' => false],
                    ['text' => '80', 'correct' => true],
                    ['text' => '8080', 'correct' => false],
                ],
            ],
            [
                'text' => 'Which layer of the OSI model handles routing?',
                'options' => [
                    ['text' => 'Data Link', 'correct' => false],
                    ['text' => 'Transport', 'correct' => false],
                    ['text' => 'Network', 'correct' => true],
                    ['text' => 'Session', 'correct' => false],
                ],
            ],
        ]);
    }

    private function seedQuestions(Quiz $quiz, array $questionsData): void
    {
        if ($quiz->questions()->exists()) {
            return;
        }

        foreach ($questionsData as $order => $qData) {
            $question = Question::create([
                'quiz_id' => $quiz->id,
                'question_text' => $qData['text'],
                'order' => $order + 1,
            ]);

            foreach ($qData['options'] as $optData) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $optData['text'],
                    'is_correct' => $optData['correct'],
                ]);
            }
        }
    }
}
