<?php

namespace App\Http\Controllers;

use App\Services\ImageGenerationService;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function __construct(private ImageGenerationService $imageService)
    {
    }
    public function index(Request $request)
    {
        $models = $this->imageService->getModels();
        $sizes = $this->imageService->getSizes();
        $history = session('image_history', []);
        return view("image_generator", compact("models","sizes","history"));
    }

    public function generate(Request $request)
    {
       $request->validate([
        "prompt"=> "required|string",
        "model"=> "required|string",
        "size"=> "required|string",
       ]);

       $result = $this->imageService->generate(
        prompt: $request->input("prompt"),
        model: $request->input("model"),
        size: $request->input("size"),
        seed: $request->input("seed", 0),
        enhance: $request->input("enhance", true),
       );

       $history = session('image_history', []);
       $history[] = $result;

       session(['image_history' => array_slice($history, -20)]);

       return response()->json($result);

    }
}
