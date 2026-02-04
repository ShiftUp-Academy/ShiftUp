<?php

namespace App\Http\Controllers;

use App\Services\GeminiChatService;
use Illuminate\Http\Request;

class GeminiChatController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiChatService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        try {
            $response = $this->geminiService->getResponse($request->message);
            return response()->json([
                'status' => 'success',
                'response' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Désolé, une erreur est survenue lors de la communication avec l\'IA.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
