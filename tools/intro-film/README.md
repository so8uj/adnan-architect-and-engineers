# Intro film — source (how it was made)

`public/uploads/2026/11/adnan-intro-film-v3.mp4` (1920×1080, 30 fps, 56 s, Bangla narration) is rendered from these files.
It is the video in the home page's intro video section (Admin → Settings → Home sections → Intro video link / poster).

| File | What it does |
|---|---|
| `composer.html` | The whole film as one GSAP timeline (scenes, text, transitions, counters). Edit text / images / timing here. `DUR` = length in seconds. |
| `capture.js` | Headless Chrome (puppeteer-core) steps the timeline frame by frame and saves JPEGs. `node capture.js "" <fromFrame>` re-renders only the frames from `<fromFrame>` on. |
| `voice.py` | Bangla narration with Microsoft's neural voice (`pip install edge-tts`, voice `bn-BD-PradeepNeural`; female: `bn-BD-NabanitaNeural`). Needs internet; the text is sent to Microsoft's service. Each line has a planned start second. |
| `music.py` | The energetic background track, synthesised with numpy (120 BPM, C major, kick / clap / hats / bass / plucks / stabs / risers / crashes; sections follow the film: intro → build → groove → montage drive → breakdown → finale). No samples, no licence needed. |
| `audio3.py` | Mixes the sound: `music.wav`, light whooshes on the wipes, the voice (EQ / compression), the music ducking under the voice, loudness normalisation → `audio3.wav`. |

Rebuild (paths inside the files point at the author's temp folder — adjust them first):
1. `python voice.py` → `voice/v1..v6.mp3` + `voice/info.json`
2. `node capture.js` → `frames/00000.jpg …`
3. `python music.py` → `music.wav`, then `python audio3.py` → `audio3.wav`
4. `ffmpeg -framerate 30 -i frames/%05d.jpg -i audio3.wav -vf format=yuv420p -c:v libx264 -crf 22 -c:a aac -b:a 160k -shortest -movflags +faststart out.mp4`

Needs: Chrome, `npm i puppeteer-core ffmpeg-static ffprobe-static`, Python + `numpy`, `scipy`, `edge-tts`. The numbers in scene 5 (1500+ / 8 / 1500+) and the contact details in the last scene are typed into `composer.html` — keep them in sync with the site.
