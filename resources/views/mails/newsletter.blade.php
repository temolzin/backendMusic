<html>
<head>
    <title>{{ $subject }}</title>
</head>
<body>
    <img src="{{ $message->embed(public_path('logovibeer.png')) }}" alt="Vibeer" style="height: 70px;">
    {{ $content }}
</body>
</html>
