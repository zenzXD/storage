<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test btch-downloader</title>
  <script src="https://unpkg.com/@webcontainer/api@1"></script>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white font-mono min-h-screen flex flex-col">
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4 text-center">Test btch-downloader Module</h1>
    <div class="flex flex-col md:flex-row gap-4">
      <div class="w-full md:w-1/2">
        <label for="code-select" class="block text-sm mb-2">Pilih Kode Test</label>
        <select id="code-select" class="w-full p-2 bg-gray-800 rounded border border-gray-600 text-white mb-4">
          <option value="igdl">Instagram Downloader</option>
          <option value="ytdl">YouTube Downloader</option>
          <option value="tkdl">TikTok Downloader</option>
        </select>
        <textarea id="code-editor" class="w-full h-96 p-2 bg-gray-800 rounded border border-gray-600 text-white resize-none" spellcheck="false"></textarea>
      </div>
      <div class="w-full md:w-1/2">
        <button id="run-btn" class="w-full p-2 bg-blue-600 hover:bg-blue-700 rounded mb-4 transition">Run Code</button>
        <pre id="output" class="w-full h-96 p-2 bg-gray-800 rounded border border-gray-600 overflow-auto"></pre>
      </div>
    </div>
  </div>

  <script>
    const codeSelect = document.getElementById('code-select');
    const codeEditor = document.getElementById('code-editor');
    const runBtn = document.getElementById('run-btn');
    const outputEl = document.getElementById('output');

    const codeTemplates = {
      igdl: `const { igdl } = require('btch-downloader');
const url = 'https://www.instagram.com/p/example-post/'; // Ganti dengan URL valid
igdl(url)
  .then(data => console.log(JSON.stringify(data, null, 2)))
  .catch(err => console.error('Error:', err.message));`,
      ytdl: `const { ytdl } = require('btch-downloader');
const url = 'https://www.youtube.com/watch?v=example-video'; // Ganti dengan URL valid
ytdl(url)
  .then(data => console.log(JSON.stringify(data, null, 2)))
  .catch(err => console.error('Error:', err.message));`,
      tkdl: `const { tkdl } = require('btch-downloader');
const url = 'https://www.tiktok.com/@user/video/example-id'; // Ganti dengan URL valid
tkdl(url)
  .then(data => console.log(JSON.stringify(data, null, 2)))
  .catch(err => console.error('Error:', err.message));`
    };

    codeEditor.value = codeTemplates.igdl;
    codeSelect.addEventListener('change', () => {
      codeEditor.value = codeTemplates[codeSelect.value];
    });

    runBtn.addEventListener('click', async () => {
      outputEl.textContent = 'Running...\n';
      try {
        const webcontainerInstance = await WebContainer.boot();

        await webcontainerInstance.fs.writeFile('/package.json', JSON.stringify({
          dependencies: { 'btch-downloader': 'latest' }
        }));

        const installProcess = await webcontainerInstance.spawn('npm', ['install']);
        installProcess.output.pipeTo(new WritableStream({
          write(data) { outputEl.textContent += data; }
        }));
        await installProcess.exit;

        await webcontainerInstance.fs.writeFile('/test.js', codeEditor.value);

        const runProcess = await webcontainerInstance.spawn('node', ['test.js']);
        runProcess.output.pipeTo(new WritableStream({
          write(data) { outputEl.textContent += data; }
        }));
        await runProcess.exit;

        outputEl.textContent += '\nSelesai!';
      } catch (err) {
        outputEl.textContent = 'Error: ' + err.message;
      }
    });
  </script>
</body>
</html>