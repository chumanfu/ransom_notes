<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromptCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromptCardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cards = PromptCard::query()->latest()->paginate(50);
        return response()->json($cards);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'text' => ['required', 'string', 'max:1000'],
        ]);

        $card = PromptCard::create([
            'text' => $request->text,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($card, 201);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:txt,csv', 'max:10240'],
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        $lines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $content)));
        $created = 0;
        $userId = $request->user()->id;

        DB::transaction(function () use ($lines, $userId, &$created) {
            foreach ($lines as $line) {
                if ($line === '') {
                    continue;
                }
                PromptCard::create(['text' => $line, 'created_by' => $userId]);
                $created++;
            }
        });

        return response()->json(['message' => "Imported {$created} prompt cards.", 'count' => $created]);
    }

    public function destroy(PromptCard $promptCard): JsonResponse
    {
        $promptCard->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
