<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'theme' => ['required', 'in:light,dark,auto'],
        ]);

        if ($user = $request->user()) {
            $user->theme = $data['theme'];
            $user->save();
        }

        return response()->json(['ok' => true, 'theme' => $data['theme']]);
    }
}
