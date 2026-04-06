<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

use App\Models\PreUser;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Helpers\DateHelper;

class RegisterController extends Controller
{
    // 一覧ページ
    public function index(Request $request)
    {
        $query = PreUser::query();

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
var_dump($sortBy);
        $allowedSorts = ['agent','email', 'created_at', 'expires_at','verified_at'];

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $perPage = (int) $request->input('per_page', 20);

        $PreRegister = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();


        return Inertia::render('Admin/Registers/Index', [
            'preregisters'     => $PreRegister,
        ]);
    }
}