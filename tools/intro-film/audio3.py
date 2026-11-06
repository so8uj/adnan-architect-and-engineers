import subprocess, os, json
HERE = os.path.dirname(os.path.abspath(__file__))
FF = os.path.join(HERE, '..', 'ff', 'node_modules', 'ffmpeg-static', 'ffmpeg.exe')
DUR = 56
wipes = [6.2, 14.6, 26.6, 30.0, 33.0, 36.0, 38.6, 44.8]
voice = json.load(open(os.path.join(HERE, 'voice', 'info.json')))

inputs, chains, music, vlabels = [], [], [], []
n = [0]
def add(args, chain, bus):
    i = n[0]; n[0] += 1
    inputs.extend(args)
    chains.append('[%d:a]%s[a%d]' % (i, chain, i))
    bus.append('[a%d]' % i)

def lavfi(src, d): return ['-f', 'lavfi', '-t', str(d), '-i', src]

# ---------------- music bus: the synthesised energetic track (music.py) + a little whoosh on every slash wipe
add(['-i', os.path.join(HERE, 'music.wav')], 'aformat=sample_rates=48000:channel_layouts=stereo,volume=.5', music)
for t in wipes:
    ms = int((t - 0.6) * 1000)
    add(lavfi('anoisesrc=d=1.2:c=pink:a=0.7:sample_rate=48000', 1.2), 'highpass=f=450,lowpass=f=7000,afade=t=in:d=0.6,afade=t=out:st=0.6:d=0.6,volume=.16,aformat=channel_layouts=stereo,adelay=%d|%d' % (ms, ms), music)

# ---------------- voice bus: studio-ish treatment, placed at the planned seconds
for v in voice:
    ms = int(v['start'] * 1000)
    add(['-i', os.path.join(HERE, 'voice', v['name'] + '.mp3')],
        'aformat=sample_rates=48000:channel_layouts=mono,highpass=f=85,equalizer=f=180:t=q:w=1:g=2,equalizer=f=3200:t=q:w=1:g=2.5,'
        'acompressor=threshold=0.09:ratio=3:attack=8:release=140:makeup=2,aecho=0.9:0.9:70:0.12,volume=1.7,adelay=%d|%d' % (ms, ms), vlabels)

filt = ';'.join(chains)
filt += ';' + ''.join(music) + 'amix=inputs=%d:normalize=0:duration=longest,aformat=channel_layouts=stereo[music]' % len(music)
filt += ';' + ''.join(vlabels) + 'amix=inputs=%d:normalize=0:duration=longest,apad,atrim=0:%d,aformat=channel_layouts=stereo,asplit=2[vmix][vsc]' % (len(vlabels), DUR)
# the music dips whenever the voice speaks
filt += ';[music][vsc]sidechaincompress=threshold=0.02:ratio=3:attack=20:release=450:makeup=1[ducked]'
filt += ';[ducked][vmix]amix=inputs=2:normalize=0:duration=longest,afade=t=in:d=1.5,afade=t=out:st=%d:d=2.5,loudnorm=I=-16:TP=-1.5:LRA=9,alimiter=limit=0.92,aformat=sample_fmts=fltp:channel_layouts=stereo[out]' % (DUR - 2.5)
cmd = [FF, '-y', '-v', 'error'] + inputs + ['-filter_complex', filt, '-map', '[out]', '-t', str(DUR), '-c:a', 'pcm_s16le', os.path.join(HERE, 'audio3.wav')]
r = subprocess.run(cmd, capture_output=True, text=True)
print('exit', r.returncode, r.stderr[:500])
