<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API Documentation</title>
    <script src="https://cdn.jsdelivr.net/npm/@scalar/api-reference"></script>
</head>
<body style="margin: 0;">
    <div id="app" style="height: 100vh;"></div>
    <script>
        Scalar.createApiReference('#app', {
            sources: [
            @foreach($services as $key => $service)
            {
                title: '{{ $service['name'] }}',
                slug: '{{ $key }}',
                url: '{{ config('docs.base_path') }}/specs/{{ $key }}.json',
            },
                @endforeach
            ],
        })
    </script>
</body>
</html>
