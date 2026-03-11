<!DOCTYPE html>
<html>
<head><title>Gemini TTS</title></head>
<body>

<h1>Gemini 2.5 Pro Text-to-Speech</h1>

<form id="ttsForm">
    @csrf

    <label>Text to speak:</label><br>
    <textarea name="text" rows="4" cols="60"
        placeholder="Enter your text here..."></textarea><br><br>

    <label>Style Prompt (how to speak):</label><br>
    <input type="text" name="prompt" style="width:400px"
        value="Read aloud in a warm, welcoming tone." /><br><br>

    <label>Voice:</label>
    <select name="voice">
        <option value="Achernar">Achernar (warm)</option>
        <option value="Puck">Puck (upbeat)</option>
        <option value="Charon">Charon (calm)</option>
        <option value="Kore">Kore (neutral)</option>
        <option value="Fenrir">Fenrir (confident)</option>
        <option value="Aoede">Aoede (expressive)</option>
    </select><br><br>

    <label>Pitch (-20 to 20):</label>
    <input type="number" name="pitch" value="0" min="-20" max="20" step="0.5" /><br><br>

    <label>Speaking Rate (0.25 to 4.0):</label>
    <input type="number" name="speaking_rate" value="1" min="0.25" max="4" step="0.25" /><br><br>

    <button type="submit">🎙 Generate Speech</button>
</form>

<hr>
<div id="playerSection" style="display:none">
    <h3>Generated Audio:</h3>
    <audio id="audioPlayer" controls></audio>
    <br><br>
    <a id="downloadLink" href="#" download="speech.wav">⬇ Download WAV</a>
</div>

<div id="status"></div>

<script>
    document.getElementById('ttsForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const status = document.getElementById('status');
        status.textContent = '⏳ Generating audio...';

        const formData = new FormData(e.target);

        const res = await fetch('/speech/generate-json', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });

        const data = await res.json();

        if (data.success) {
            // Convert base64 to blob and play
            const binary   = atob(data.audio);
            const bytes    = new Uint8Array(binary.length);
            for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
            const blob     = new Blob([bytes], { type: data.mimeType });
            const url      = URL.createObjectURL(blob);

            document.getElementById('audioPlayer').src = url;
            document.getElementById('downloadLink').href = url;
            document.getElementById('playerSection').style.display = 'block';
            document.getElementById('audioPlayer').play();
            status.textContent = '✅ Done!';
        } else {
            status.textContent = '❌ Error: ' + data.error;
        }
    });
</script>

</body>
</html>
