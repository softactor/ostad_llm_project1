<?php

namespace App\Services;

class ImageGenerationService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    private string $baseUrl = "https://image.pollinations.ai/prompt";

    public const MODELS = [
        'flux'           => 'Flux (Best Quality)',
        'flux-realism'   => 'Flux Realism (Photorealistic)',
        'flux-anime'     => 'Flux Anime',
        'flux-3d'        => 'Flux 3D',
        'turbo'          => 'Turbo (Fastest)',
    ];
     
    public const SIZES = [
        'square'    => ['width' => 1024, 'height' => 1024, 'label' => 'Square (1:1)'],
        'landscape' => ['width' => 1280, 'height' => 720,  'label' => 'Landscape (16:9)'],
        'portrait'  => ['width' => 720,  'height' => 1280, 'label' => 'Portrait (9:16)'],
        'wide'      => ['width' => 1920, 'height' => 1080, 'label' => 'Wide (1920×1080)'],
    ];

    public function buildImageUrl(
        string $prompt,
        string $model,
        string $size,
        int $seed = 0,
        bool $enhance = false
    ): string{
        $dimensions = self::SIZES[$size] ?? self::SIZES['square']; 

        $params = [
            'width' =>   $dimensions['width'],
            'height' =>   $dimensions['height'],
            'model' =>   $model,
            'enhance' =>   $enhance,
            'nologo' =>   true,
            'private' =>   true,
        ];
    
        $encodePrompt = rawurlencode($prompt);
        $queryString = http_build_query($params);
        
        return $this->baseUrl .'/'. $encodePrompt .'/'. $queryString;
    }


    public function generate(
        string $prompt,
        string $model = 'flux',
        string $size = 'square',
        int $seed = 0,
        bool $enhance = false
        ): array
    {
        $useSeed = $seed > 0 ? $seed : rand(1000, 9999999);

        $imageData = $this->buildImageUrl($prompt, $model, $size, $useSeed, $enhance);

        return [
            'success'=> true,
            'url'=> $imageData,
            'prompt'=> $prompt,
            'model'=> $model,
            'size'=> $size,
            'seed'=> $seed,
            'enhance'=> $enhance,
            'dimensions'=> self::SIZES[$size] ?? self::SIZES['square'],
        ];
    }

    public function getModels(): array
    {
        return self::MODELS;
    }
    
    public function getSizes(): array
    {
        return self::SIZES;
    }

}
