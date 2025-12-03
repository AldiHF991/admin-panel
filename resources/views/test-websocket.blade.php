<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebSocket Test</title>
    @vite(['resources/js/app.js'])
</head>
<body>
    <h1>WebSocket Connection Test</h1>
    <p>Status: <span id="status">Waiting for event...</span></p>
    <ul id="messages"></ul>

    <script type="module">
        window.Echo.channel('test-channel')
            .listen('TestWebSocket', (e) => {
                console.log('Event received:', e);
                document.getElementById('status').innerText = 'Event received!';
                const li = document.createElement('li');
                li.innerText = e.message + ' (' + new Date().toLocaleTimeString() + ')';
                document.getElementById('messages').appendChild(li);
            });
            
        console.log('Listening on test-channel...');
    </script>
</body>
</html>
