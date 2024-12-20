<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Chat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Group Chat</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- <div class="mt-5">
            <h2>Chat Window</h2>
            <iframe src="{{ $chatboxUrl }}" width="100%" height="500px" frameborder="0"></iframe>
        </div> --}}

        <div class="mt-3">
            <a href="https://us05web.zoom.us/launch/chat/v2/eyJzaWQiOiI5Y2Y1NzM4OTEwYWY0ODk3OGIwYzAxNTI1NWMyMzliZUBjb25mZXJlbmNlLnhtcHAuem9vbS51cyJ9"
               class="btn btn-primary" target="_blank">Visit the Zoom Chat Now</a>
        </div>
    </div>
</body>
</html>
