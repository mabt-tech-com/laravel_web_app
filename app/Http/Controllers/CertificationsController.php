<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\Quiz;
use App\Models\Training;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificationsController extends Controller
{
    public function certified_trainings_by_student($student_id)
    {
        try {
            $trainings = Training::whereHas('certified_students', function ($q) use ($student_id) {
                $q->where('certifications.student_id', $student_id);
            })->get();

            return response()->json($trainings);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function certified_quizzes_by_student($student_id)
    {
        try {
            $quizzes = Quiz::whereHas('certified_students', function ($q) use ($student_id) {
                $q->where('certifications.student_id', $student_id);
            })->get();

            return response()->json($quizzes);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function certified_students_by_training($training_id)
    {
        try {
            $students = User::whereHas('certified_trainings', function ($q) use ($training_id) {
                $q->where('certifications.training_id', $training_id);
            })->get();

            return response()->json($students);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function certified_students_by_quiz($quiz_id)
    {
        try {
            $students = User::whereHas('certified_quizzes', function ($q) use ($quiz_id) {
                $q->where('certifications.quiz_id', $quiz_id);
            })->get();

            return response()->json($students);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function store()
    {
        try {
            request()->validate([
                'student_id' => 'required|integer|exists:users,id',
                'training_id' => 'required|integer|exists:trainings,id',
            ]);

            $certification = Certification::create([
                'student_id' => request('student_id'),
                'training_id' => request('training_id'),
            ]);

            insert_in_history_table('created', $certification->id, $certification->getTable());

            return response()->json([
                'certification_id' => $certification->id,
                'message' => 'Certification created successfully.',
            ]);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $certification = Certification::with('student', 'training')->findOrFail($id);

            $data = [
                'user_full_name' => $certification->student->full_name,
                'training_label' => $certification->training->label,
                'created_at' => $certification->created_at,
            ];

            $pdf = Pdf::loadView('certification', $data)->setPaper('A4', 'landscape');

            return $pdf->download('certification.pdf');
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $certification = Certification::findOrFail($id);

            $certification->delete();

            insert_in_history_table('deleted', $certification->id, $certification->getTable());

            return response()->json(['message' => 'Certification deleted successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }

    }
}
