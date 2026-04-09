<?php

namespace App\Http\Controllers\Student;

use App\Actions\AntiCheat\RecordSuspiciousEventAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSuspiciousEventRequest;
use App\Models\Attempt;
use Illuminate\Support\Facades\Auth;

class SuspiciousEventController extends Controller
{
    public function store(StoreSuspiciousEventRequest $request, Attempt $attempt, RecordSuspiciousEventAction $action)
    {
        abort_unless($attempt->student_id === Auth::id(), 403);
        abort_unless($attempt->isInProgress(), 403);

        $result = $action->execute($attempt, $request->validated());

        return response()->json($result);
    }
}
