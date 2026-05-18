<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Image Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">

    <h2 class="mb-4 text-center">AI Image Generator</h2>

    {{-- Form --}}
    <div class="card mb-4">
        <div class="card-body">

            <div class="mb-3">
                <label class="form-label fw-semibold">Prompt</label>
                <input type="text" id="prompt" class="form-control" placeholder="e.g. a cat sitting on the moon">
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Model</label>
                    <select id="model" class="form-select">
                        @foreach($models as $id => $label)
                            <option value="{{ $id }}" {{ $id === 'flux' ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Size</label>
                    <select id="size" class="form-select">
                        @foreach($sizes as $key => $s)
                            <option value="{{ $key }}">{{ $s['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button id="generate-btn" class="btn btn-primary w-100">
                Generate Image
            </button>

        </div>
    </div>

    {{-- Simple prompt ideas --}}
    <div class="mb-4">
        <p class="fw-semibold mb-2">Simple prompt ideas — click to use:</p>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-outline-secondary btn-sm" type="button">a cat sitting on the moon</button>
            <button class="btn btn-outline-secondary btn-sm" type="button">a dragon flying over mountains</button>
            <button class="btn btn-outline-secondary btn-sm" type="button">sunset over the ocean</button>
            <button class="btn btn-outline-secondary btn-sm" type="button">a robot reading a book</button>
            <button class="btn btn-outline-secondary btn-sm" type="button">snowy village at night</button>
            <button class="btn btn-outline-secondary btn-sm" type="button">a lion wearing a crown</button>
        </div>
    </div>

    {{-- Result --}}
    <div id="result" class="text-center" style="display: none;">
        <img id="generated-image" src="https://via.placeholder.com/800x500?text=Generated+Image" alt="Generated Image"
             class="img-fluid rounded shadow mb-2" style="max-height: 500px;">
        <div>
            <a id="download-btn" href="#" download class="btn btn-success btn-sm mt-2">
                Download Image
            </a>
        </div>
    </div>

    {{-- Loading --}}
    <div id="loading" class="text-center" style="display: none;">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 text-muted">Generating... please wait</p>
    </div>

    {{-- Session history --}}
    <hr class="my-4">
    <h5 class="mb-3">Previously Generated</h5>
    <div class="row g-3">
        @foreach(array_reverse($history) as $item)
                <div class="col-6 col-md-3">
                    <img src="{{ $item['url'] }}" alt="{{ $item['prompt'] }}"
                         class="img-fluid rounded shadow-sm">
                    <p class="text-muted small mt-1">{{ $item['prompt'] }}</p>
                </div>
            @endforeach
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

    const CSRF = document.querySelector('meta[name="csrf-token"]').content
    document.getElementById('generate-btn').addEventListener('click', async () =>{
        const prompt = document.getElementById('prompt').value.trim()
        if(!prompt)
        {
           alert('prompt is required')
           return    
        }

        const result =  await fetch('{{ route("image.generate") }}', {
            method:'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body:JSON.stringify({
                prompt: prompt,
                model: document.getElementById('model').value,
                size: document.getElementById('size').value,
                seed: 0,
                enhance: true,  
            })
        })

        const resultData = await result.json()

        if(resultData.success){
            document.getElementById('result').style.display = 'block'
            document.getElementById('generated-image').src = resultData.url
        }

    })

</script>

</body>
</html>