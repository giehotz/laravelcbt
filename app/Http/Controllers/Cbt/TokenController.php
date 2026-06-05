<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use App\Models\Cbt\Token;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TokenController extends Controller
{
    public function index()
    {
        $token = Token::first();
        
        if (!$token) {
            $token = Token::generate();
        }

        return Inertia::render('Cbt/Token/Index', [
            'token' => $token
        ]);
    }

    public function generate()
    {
        Token::generate();
        return back()->with('success', 'Token ujian berhasil diperbarui.');
    }

    public function toggleAuto(Request $request)
    {
        $request->validate([
            'auto' => 'required|boolean'
        ]);

        $token = Token::first();
        if ($token) {
            $token->update(['auto' => $request->auto]);
        }

        return back()->with('success', 'Status auto-generate token berhasil diubah.');
    }
}
