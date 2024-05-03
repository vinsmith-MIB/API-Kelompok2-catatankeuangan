<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class ExpensesControllerAPI extends Controller
{
    public function index(): JsonResponse
    {
        $expenses = Auth::user()->expenses()->get();
        $totalExpenses = Auth::user()->expenses()->sum('amount');

        return response()->json([
            'expenses' => $expenses,
            'total_expenses' => $totalExpenses,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'nullable',
            'amount' => 'required|numeric',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $expense = Auth::user()->expenses()->create($request->all());

        return response()->json($expense, 200);
    }

    public function show($id): JsonResponse
    {
        $expense = Auth::user()->expenses()->findOrFail($id);
        return response()->json($expense, 200);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'nullable',
            'amount' => 'required|numeric',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $expense = Auth::user()->expenses()->findOrFail($id);
        $expense->update($request->all());

        return response()->json($expense, 200);
    }

    public function destroy($id): JsonResponse
    {
        $expense = Auth::user()->expenses()->findOrFail($id);
        $expense->delete();

        return response()->json([], 200);
    }

    public function filterByDate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $expenses = Auth::user()->expenses()
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->latest()
            ->get();

        $totalExpenses = $expenses->sum('amount');

        return response()->json([
            'expenses' => $expenses,
            'total_expenses' => $totalExpenses,
        ], 200);
    }
}
