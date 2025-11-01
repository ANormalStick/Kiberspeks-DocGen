<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    public function store(Request $r)
    {
        $id = DB::table('assessments')->insertGetId([
            'company' => $r->input('company'),
            'sector'  => $r->input('sector'),
            'size'    => $r->input('size'),
            'notes'   => $r->input('notes'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['id' => $id, 'message' => 'saved'];
    }
}
