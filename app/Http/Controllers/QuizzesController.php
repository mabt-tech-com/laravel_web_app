<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Models\QuizQuestionOptionItem;
use App\Models\QuizQuestionType;
use App\Models\QuizStudentAttempt;
use App\Models\Training;
use App\Models\User;

class QuizzesController extends Controller
{
    public function index()
    {
        try {
            request()->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'chapter_id' => 'sometimes|integer|exists:chapters,id',
                'is_quiz' => 'sometimes|boolean',
                'is_exam' => 'sometimes|boolean',
                'min_price' => 'sometimes|integer',
                'max_price' => 'sometimes|integer',
                'is_published' => 'sometimes|boolean',
            ]);

            $quizzes_query_builder = Quiz::where('company_id', request('company_id'))
                ->with('categories', 'reviews', 'chapter', 'quiz_questions.quiz_question_type', 'quiz_questions.quiz_question_options.quiz_question_option_items')
                ->latest()
                ->withAvg('reviews', 'rating');

            if (request('is_quiz')) {
                $quizzes_query_builder->isQuiz();
            }

            if (request('is_exam')) {
                $quizzes_query_builder->isExam();
            }

            if (request('chapter_id')) {
                $quizzes_query_builder->where('chapter_id', request('chapter_id'));
            }

            if (request('min_price')) {
                $quizzes_query_builder->where('price', '>', request('min_price'));
            }

            if (request('max_price')) {
                $quizzes_query_builder->where('price', '<', request('max_price'));
            }

            if (request('categories')) {
                $quizzes_query_builder->whereHas('categories', function ($query) {
                    $query->whereIn('categories.id', json_decode(request('categories'), true));
                });
            }

            if (request('reviews')) {
                $quizzes_query_builder->havingBetween('reviews_avg_rating', json_decode(request('reviews'), true));
            }

            if (request('is_published')) {
                $quizzes_query_builder->where('is_published', request('is_published'));
            }

            $quizzes = $quizzes_query_builder->paginate(config('custom_config.pagination_items'));

            return response()->json($quizzes);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function store()
    {
        try {
            request()->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'training_id' => 'nullable|integer|exists:trainings,id',
                'chapter_id' => 'nullable|integer|exists:chapters,id',
                'label' => 'required|string|min:1|max:255',
                'description' => 'nullable|string|min:1|max:255',
                'max_attempts' => 'nullable|integer',
                'passing_percentage' => 'nullable|integer',
                'duration' => 'required|integer',
                'break_interval' => 'nullable|integer|min:1',
                'break_duration_in_mins' => 'nullable|integer|min:1',
                'is_published' => 'required|boolean',
                'price' => 'nullable|string',
                'discounted_price' => 'nullable|string',
            ]);

            if (request('training_id') && request('chapter_id')) {
                $training = Training::findOrFail(request('training_id'));

                if (!$training->chapters->contains(request('chapter_id'))) {
                    return response()->json([
                        'message' => 'Training ' . request('training_id') . ' doesn\'t have the chapter ' . request('chapter_id'),
                    ], 401);
                }
            }

            $quiz = Quiz::create([
                'company_id' => request('company_id'),
                'training_id' => request('training_id'),
                'chapter_id' => request('chapter_id'),
                'label' => request('label'),
                'description' => request('description'),
                'max_attempts' => request('max_attempts'),
                'passing_percentage' => request('passing_percentage'),
                'duration' => request('duration'),
                'break_interval' => request('break_interval'),
                'break_duration_in_mins' => request('break_duration_in_mins'),
                'is_published' => request('is_published'),
                'price' => request('price'),
                'discounted_price' => request('discounted_price'),
            ]);

            insert_in_history_table('created', $quiz->id, $quiz->getTable());

            return response()->json([
                'quiz_id' => $quiz->id,
                'message' => 'Quiz created successfully.',
            ]);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function quiz_with_questions_and_options()
    {
        try {
            request()->validate([
                'training_id' => 'nullable|integer|exists:trainings,id',
                'chapter_id' => 'nullable|integer|exists:chapters,id',
                'label' => 'required|string|min:1|max:255',
                'description' => 'nullable|string|min:1|max:255',
                'max_attempts' => 'nullable|integer',
                'passing_percentage' => 'nullable|integer',
                'duration' => 'required|integer',
                'break_interval' => 'nullable|integer|min:1',
                'break_duration_in_mins' => 'nullable|integer|min:1',
                'is_published' => 'required|boolean',
                'price' => 'nullable|string',
                'discounted_price' => 'nullable|string',

                'quiz_questions' => 'required|array|min:1',
                'quiz_questions.*.quiz_question_type_id' => 'required|integer|exists:quiz_question_types,id',
                'quiz_questions.*.label' => 'required|string|min:1|max:255',
                'quiz_questions.*.tip' => 'nullable|string|min:1|max:255',
                'quiz_questions.*.explanation' => 'nullable|string|min:1|max:255',

                'quiz_questions.*.quiz_question_options' => 'required|array|min:1',
                'quiz_questions.*.quiz_question_options.*.label' => 'required|string|min:1|max:255',
            ]);

            if (request('training_id') && request('chapter_id')) {
                $training = Training::findOrFail(request('training_id'));

                if (!$training->chapters->contains(request('chapter_id'))) {
                    return response()->json([
                        'message' => 'Training ' . request('training_id') . ' doesn\'t have the chapter ' . request('chapter_id'),
                    ], 401);
                }
            }

            $quiz = Quiz::create([
                'training_id' => request('training_id'),
                'chapter_id' => request('chapter_id'),
                'label' => request('label'),
                'description' => request('description'),
                'max_attempts' => request('max_attempts'),
                'passing_percentage' => request('passing_percentage'),
                'duration' => request('duration'),
                'break_interval' => request('break_interval'),
                'break_duration_in_mins' => request('break_duration_in_mins'),
                'is_published' => request('is_published'),
                'price' => request('price'),
                'discounted_price' => request('discounted_price'),
            ]);

            for ($i = 0; $i < count(request('quiz_questions')); $i++) {
                $quiz_question = QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'quiz_question_type_id' => request('quiz_questions')[$i]['quiz_question_type_id'],
                    'label' => request('quiz_questions')[$i]['label'],
                    'tip' => request('quiz_questions')[$i]['tip'],
                    'explanation' => request('quiz_questions')[$i]['explanation'],
                ]);

                for ($j = 0; $j < count(request('quiz_questions')[$i]['quiz_question_options']); $j++) {
                    $quiz_question_option = QuizQuestionOption::create([
                        'quiz_question_id' => $quiz_question->id,
                        'label' => request('quiz_questions')[$i]['quiz_question_options'][$j]['label'],
                        'is_correct' => request('quiz_questions')[$i]['quiz_question_options'][$j]['is_correct'],
                    ]);

                    if (request('quiz_questions')[$i]['quiz_question_type_id'] === QuizQuestionType::DRAG_AND_DROP_ID) {
                        for ($k = 0; $k < count(request('quiz_questions')[$i]['quiz_question_options'][$j]['quiz_question_option_items']); $k++) {
                            $quiz_question_option_item = QuizQuestionOptionItem::create([
                                'quiz_question_option_id' => $quiz_question_option->id,
                                'label' => request('quiz_questions')[$i]['quiz_question_options'][$j]['quiz_question_option_items'][$k]['label'],
                            ]);
                        }
                    }
                }
            }

            insert_in_history_table('created', $quiz->id, $quiz->getTable());

            return response()->json([
                'quiz_id' => $quiz->id,
                'message' => 'Quiz created successfully.',
            ]);
            // }
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $quiz = Quiz::with('categories', 'reviews', 'chapter', 'quiz_questions.quiz_question_type', 'quiz_questions.quiz_question_options.quiz_question_option_items')
                ->findOrFail($id);

            return response()->json($quiz);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            request()->validate([
                'training_id' => 'nullable|integer|exists:trainings,id',
                'chapter_id' => 'nullable|integer|exists:chapters,id',
                'label' => 'required|string|min:1|max:255',
                'description' => 'nullable|string|min:1|max:255',
                'max_attempts' => 'nullable|integer',
                'passing_percentage' => 'nullable|integer',
                'duration' => 'required|integer',
                'break_interval' => 'nullable|integer|min:1',
                'break_duration_in_mins' => 'nullable|integer|min:1',
                'is_published' => 'required|boolean',
                'price' => 'nullable|string',
                'discounted_price' => 'nullable|string',
            ]);

            if (request('training_id') && request('chapter_id')) {
                $training = Training::findOrFail(request('training_id'));

                if (!$training->chapters->contains(request('chapter_id'))) {
                    return response()->json([
                        'message' => 'Training ' . request('training_id') . ' doesn\'t have the chapter ' . request('chapter_id'),
                    ], 401);
                }
            }

            $quiz = Quiz::findOrFail($id);

            $quiz->training_id = request('training_id');
            $quiz->chapter_id = request('chapter_id');
            $quiz->label = request('label');
            $quiz->description = request('description');
            $quiz->max_attempts = request('max_attempts');
            $quiz->passing_percentage = request('passing_percentage');
            $quiz->duration = request('duration');
            $quiz->break_interval = request('break_interval');
            $quiz->break_duration_in_mins = request('break_duration_in_mins');
            $quiz->is_published = request('is_published');
            $quiz->price = request('price');
            $quiz->discounted_price = request('discounted_price');

            $quiz->save();

            insert_in_history_table('updated', $quiz->id, $quiz->getTable());

            return response()->json(['message' => 'Quiz updated successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update_quiz_with_questions_and_options($id)
    {
        request()->validate([
            'training_id' => 'nullable|integer|exists:trainings,id',
            'chapter_id' => 'nullable|integer|exists:chapters,id',
            'label' => 'required|string|min:1|max:255',
            'description' => 'nullable|string|min:1|max:255',
            'max_attempts' => 'nullable|integer',
            'passing_percentage' => 'nullable|integer',
            'duration' => 'required|integer',
            'break_interval' => 'nullable|integer|min:1',
            'break_duration_in_mins' => 'nullable|integer|min:1',
            'is_published' => 'required|boolean',
            'price' => 'nullable|string',
            'discounted_price' => 'nullable|string',

            'quiz_questions.*.quiz_question_type_id' => 'required|integer|exists:quiz_question_types,id',
            'quiz_questions.*.label' => 'required|string|min:1|max:255',
            'quiz_questions.*.tip' => 'nullable|string|min:1|max:255',
            'quiz_questions.*.explanation' => 'nullable|string|min:1|max:255',

            'quiz_questions.*.quiz_question_options' => 'required|array|min:1',
            'quiz_questions.*.quiz_question_options.*.label' => 'required|string|min:1|max:255',
        ]);

        if (request('training_id') && request('chapter_id')) {
            $training = Training::findOrFail(request('training_id'));

            if (!$training->chapters->contains(request('chapter_id'))) {
                return response()->json([
                    'message' => 'Training ' . request('training_id') . ' doesn\'t have the chapter ' . request('chapter_id'),
                ], 401);
            }
        }

        $quiz = Quiz::with('quiz_questions.quiz_question_options.quiz_question_option_items')->findOrFail($id);

        $quiz->training_id = request('training_id');
        $quiz->chapter_id = request('chapter_id');
        $quiz->label = request('label');
        $quiz->description = request('description');
        $quiz->max_attempts = request('max_attempts');
        $quiz->passing_percentage = request('passing_percentage');
        $quiz->duration = request('duration');
        $quiz->break_interval = request('break_interval');
        $quiz->break_duration_in_mins = request('break_duration_in_mins');
        $quiz->price = request('price');
        $quiz->discounted_price = request('discounted_price');

        $quiz->save();

        foreach ($quiz->quiz_questions as $quiz_question) {
            $quiz_question->quiz_question_options()->delete();
        }
        $quiz->quiz_questions()->delete();

        for ($i = 0; $i < count(request('quiz_questions')); $i++) {
            $quiz_question = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'quiz_question_type_id' => request('quiz_questions')[$i]['quiz_question_type_id'],
                'label' => request('quiz_questions')[$i]['label'],
                'tip' => request('quiz_questions')[$i]['tip'],
                'explanation' => request('quiz_questions')[$i]['explanation'],
            ]);

            for ($j = 0; $j < count(request('quiz_questions')[$i]['quiz_question_options']); $j++) {
                $quiz_question_option = QuizQuestionOption::create([
                    'quiz_question_id' => $quiz_question->id,
                    'label' => request('quiz_questions')[$i]['quiz_question_options'][$j]['label'],
                    'is_correct' => request('quiz_questions')[$i]['quiz_question_options'][$j]['is_correct'],
                ]);

                if (request('quiz_questions')[$i]['quiz_question_type_id'] === QuizQuestionType::DRAG_AND_DROP_ID) {
                    for ($k = 0; $k < count(request('quiz_questions')[$i]['quiz_question_options'][$j]['quiz_question_option_items']); $k++) {
                        $quiz_question_option_item = QuizQuestionOptionItem::create([
                            'quiz_question_option_id' => $quiz_question_option->id,
                            'label' => request('quiz_questions')[$i]['quiz_question_options'][$j]['quiz_question_option_items'][$k]['label'],
                        ]);
                    }
                }
            }
        }

        insert_in_history_table('updated', $quiz->id, $quiz->getTable());

        return response()->json([
            'quiz_id' => $quiz->id,
            'message' => 'Quiz updated successfully.',
        ]);
    }

    public function destroy($id)
    {
        try {
            $quiz = Quiz::with('quiz_questions.quiz_question_options.quiz_question_option_items')->findOrFail($id);

            $quiz->categories()->delete();
            $quiz->reviews()->delete();

            $quiz->quiz_questions()->delete();

            foreach ($quiz->quiz_questions as $quiz_question) {
                $quiz_question->quiz_question_options()->delete();
            }

            $quiz->delete();

            insert_in_history_table('deleted', $quiz->id, $quiz->getTable());

            return response()->json(['message' => 'Quiz deleted successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function start_student_quiz()
    {
        try {
            request()->validate([
                'student_id' => 'required|integer|exists:users,id',
                'quiz_id' => 'required|integer|exists:quizzes,id',
            ]);

            $student = User::findOrFail(request('student_id'));
            $quiz = Quiz::findOrFail(request('quiz_id'));

            $attempt = QuizStudentAttempt::where('quiz_id', request('quiz_id'))->where('student_id', request('student_id'))->latest()->first()?->attempt + 1 ?? 1;

            if ($quiz->max_attempts > 0 && $attempt > $quiz->max_attempts) {
                return response()->json(['message' => 'Quiz cannot be started, too many attempts.'], 401);
            }

            $quiz_student_attempt = QuizStudentAttempt::create([
                'quiz_id' => request('quiz_id'),
                'student_id' => request('student_id'),
                'attempt' => $attempt,
            ]);

            insert_in_history_table('start_student_quiz', $student->id, 'quiz_student_attempts');

            return response()->json(['message' => 'Quiz started successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function finish_student_quiz()
    {
        try {
            request()->validate([
                'student_id' => 'required|integer|exists:users,id',
                'quiz_id' => 'required|integer|exists:quizzes,id',
                'quiz_question_options_ids.*' => 'required|integer|distinct|exists:quiz_question_options,id',
            ]);

            $quiz_student_attempt = QuizStudentAttempt::where('quiz_id', request('quiz_id'))
                ->where('student_id', request('student_id'))
                ->latest('attempt')->first();

            if ($quiz_student_attempt) {
                $quiz_student_attempt->finished_at = now();
                $quiz_student_attempt->save();

                for ($i = 0; $i < count(request('quiz_question_options_ids')); $i++) {
                    $quiz_question_option = QuizQuestionOption::with('quiz_question.quiz')->findOrFail(request('quiz_question_options_ids')[$i]);

                    $quiz_student_attempt->quiz_question_options()->attach(
                        $quiz_question_option->id,
                        [
                            'quiz_student_attempt_id' => $quiz_student_attempt->id,
                            'quiz_question_id' => $quiz_question_option->quiz_question->id,
                        ],
                    );
                }

                for ($i = 0; $i < count(request('quiz_question_option_items_ids')); $i++) {
                    $quiz_question_option_item = QuizQuestionOptionItem::with('quiz_question_option.quiz_question.quiz')->findOrFail(request('quiz_question_option_items_ids')[$i]);

                    $quiz_student_attempt->quiz_question_option_items()->attach(
                        $quiz_question_option_item->id,
                        [
                            'quiz_student_attempt_id' => $quiz_student_attempt->id,
                            'quiz_question_id' => $quiz_question_option_item->quiz_question_option->quiz_question->id,
                            'quiz_question_option_id' => $quiz_question_option_item->quiz_question_option->id,
                        ],
                    );
                }

                insert_in_history_table('finish_student_quiz', request('student_id'), 'quiz_student_attempts');

                $quiz_student_attempt = QuizStudentAttempt::where('quiz_id', request('quiz_id'))
                    ->where('student_id', request('student_id'))
                    ->with('quiz_questions')
                    ->get();

                return response()->json([
                    'message' => 'Quiz Options saved successfully.',
                    'quiz_student_attempt' => $quiz_student_attempt,
                ]);
            }
            return response()->json([
                'message' => 'Quiz has not started ! Please start a quiz before finishing it.',
            ], 500);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show_quiz_student_answers()
    {
        try {
            $quiz_student_attempt = QuizStudentAttempt::where('quiz_id', request('quiz_id'))
                ->where('student_id', request('student_id'))
                ->with('quiz_questions')
                ->get();

            return response()->json($quiz_student_attempt);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function assign_catgories_to_quiz($id)
    {
        try {
            request()->validate([
                'categories' => 'present|array|min:1',
                'categories.*' => 'required|integer|distinct|exists:categories,id',
            ]);

            $quiz = Quiz::findOrFail($id);

            $quiz->categories()->sync(request('categories'));

            insert_in_history_table('assigned_categories', $quiz->id, 'category_quiz');

            return response()->json(['message' => 'Categories assigned to quiz successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
