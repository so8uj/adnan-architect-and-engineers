"""Energetic, fresh corporate-pop bed for the intro film, synthesised with numpy (no samples, no licensing).
120 BPM, C major, I-V-vi-IV. Sections follow the film: intro -> build -> groove -> montage drive -> breakdown -> finale."""
import numpy as np, os, wave
from scipy import signal

SR = 48000
DUR = 56.0
BPM = 120.0
BEAT = 60.0 / BPM
OFF = 0.5                                   # bars start here so the section changes land on the film's cuts
N = int(SR * DUR)
rng = np.random.default_rng(7)

def tseq(d): return np.arange(int(SR * d)) / SR
def env_ad(n, a=0.005, d=0.2):
    t = np.arange(n) / SR
    return np.minimum(1, t / max(a, 1e-4)) * np.exp(-t / d)

L = np.zeros(N); Rr = np.zeros(N)           # stereo bus
def put(buf, start, sig, gain=1.0):
    i = int(start * SR)
    if i >= N or i < 0: return
    e = min(N, i + len(sig))
    buf[i:e] += sig[:e - i] * gain
def stereo(start, sig, gain=1.0, pan=0.0):
    put(L, start, sig, gain * (1 - max(0, pan))); put(Rr, start, sig, gain * (1 + min(0, pan)))

def hz(m): return 440.0 * 2 ** ((m - 69) / 12)

# ---------------- instruments
def kick():
    n = int(SR * 0.45); t = np.arange(n) / SR
    f = 48 + 110 * np.exp(-t * 28)
    ph = 2 * np.pi * np.cumsum(f) / SR
    return np.sin(ph) * np.exp(-t * 9) * 1.2 + np.sin(ph * 2) * np.exp(-t * 40) * 0.2
def clap():
    n = int(SR * 0.28); t = np.arange(n) / SR
    nz = rng.standard_normal(n)
    b, a = signal.butter(2, [1100 / (SR / 2), 7500 / (SR / 2)], 'band')
    x = signal.lfilter(b, a, nz)
    e = np.exp(-t * 22) * (1 + 0.8 * (np.sin(t * 2 * np.pi * 90) > 0.6))
    return x * e * 0.9
def hat(open_=False):
    n = int(SR * (0.22 if open_ else 0.06)); t = np.arange(n) / SR
    x = signal.lfilter(*signal.butter(3, 7000 / (SR / 2), 'high'), rng.standard_normal(n))
    return x * np.exp(-t * (14 if open_ else 70)) * 0.35
def bass(m, d):
    n = int(SR * d); t = np.arange(n) / SR; f = hz(m)
    x = signal.sawtooth(2 * np.pi * f * t) * 0.6 + np.sin(2 * np.pi * f * t) * 0.8
    x = signal.lfilter(*signal.butter(2, 380 / (SR / 2)), x)
    return x * np.minimum(1, t / 0.008) * np.exp(-t / (d * 0.9)) * 0.9
def pluck(m, d=0.35):
    n = int(SR * d); t = np.arange(n) / SR; f = hz(m)
    x = signal.sawtooth(2 * np.pi * f * t) * 0.5 + np.sin(2 * np.pi * f * 2 * t) * 0.25 + np.sin(2 * np.pi * f * t) * 0.4
    cut = 1200 + 5000 * np.exp(-t * 14)
    y = np.zeros(n); z = 0.0
    a = np.exp(-2 * np.pi * cut / SR)
    for i in range(n):                      # one-pole sweeping low-pass (bright attack -> mellow tail)
        z = (1 - a[i]) * x[i] + a[i] * z
        y[i] = z
    return y * np.minimum(1, t / 0.004) * np.exp(-t / (d * 0.55)) * 0.55
def bell(m, d=1.4):
    n = int(SR * d); t = np.arange(n) / SR; f = hz(m)
    x = np.sin(2 * np.pi * f * t) + 0.45 * np.sin(2 * np.pi * f * 2.76 * t) * np.exp(-t * 3) + 0.25 * np.sin(2 * np.pi * f * 5.4 * t) * np.exp(-t * 6)
    return x * np.exp(-t / (d * 0.35)) * 0.35
def pad(ms, d):
    n = int(SR * d); t = np.arange(n) / SR; x = np.zeros(n)
    for m in ms:
        for det in (-0.07, 0.0, 0.07):
            x += signal.sawtooth(2 * np.pi * hz(m + det) * t) * 0.18
    x = signal.lfilter(*signal.butter(2, 1500 / (SR / 2)), x)
    return x * np.minimum(1, t / 0.5) * np.minimum(1, (d - t) / 0.4)
def stab(ms, d=0.22):
    n = int(SR * d); t = np.arange(n) / SR; x = np.zeros(n)
    for m in ms:
        x += signal.square(2 * np.pi * hz(m) * t, duty=0.4) * 0.16 + signal.sawtooth(2 * np.pi * hz(m) * t) * 0.12
    x = signal.lfilter(*signal.butter(2, 3200 / (SR / 2)), x)
    return x * np.minimum(1, t / 0.003) * np.exp(-t / 0.09)
def riser(d):
    n = int(SR * d); t = np.arange(n) / SR
    nz = rng.standard_normal(n)
    out = np.zeros(n)
    for k in range(8):                      # a noise sweep that opens up
        lo = 300 * (1.5 ** k); hi = min(lo * 1.8, 20000)
        band = signal.lfilter(*signal.butter(2, [lo / (SR / 2), hi / (SR / 2)], 'band'), nz)
        out += band * (t / d) ** (1.2 + k * 0.1) * 0.35
    tone = np.sin(2 * np.pi * np.cumsum(180 + 900 * (t / d) ** 2) / SR) * (t / d) ** 2 * 0.25
    return (out + tone) * np.minimum(1, (d - t) / 0.02 + 0.0)
def crash():
    n = int(SR * 1.8); t = np.arange(n) / SR
    x = signal.lfilter(*signal.butter(2, 3500 / (SR / 2), 'high'), rng.standard_normal(n))
    return x * np.exp(-t * 2.4) * 0.5
def boom():
    n = int(SR * 1.6); t = np.arange(n) / SR
    f = 38 + 70 * np.exp(-t * 6)
    return np.sin(2 * np.pi * np.cumsum(f) / SR) * np.exp(-t * 2.2) * 0.9

# ---------------- arrangement
CH = [  # (name, bass midi, chord midi notes)
    ('C', 36, [60, 64, 67]), ('G', 43, [59, 62, 67]), ('Am', 45, [57, 60, 64]), ('F', 41, [57, 60, 65]),
]
SECTIONS = [  # (start second, end second, name)
    (0.0, 6.5, 'intro'), (6.5, 14.5, 'build'), (14.5, 26.5, 'groove'), (26.5, 38.5, 'drive'), (38.5, 44.5, 'break'), (44.5, 56.0, 'finale'),
]
def section_at(t):
    for a, b, n in SECTIONS:
        if a <= t < b: return n
    return 'finale'

kick_times = []
bar = 0
t0 = OFF
bars = int((DUR - OFF) / (4 * BEAT)) + 1
K = kick(); C_ = clap(); HC = hat(False); HO = hat(True); CR = crash(); BO = boom()
for b in range(bars):
    bs = t0 + b * 4 * BEAT
    if bs >= DUR: break
    name, bmid, chord = CH[b % 4]
    sec = section_at(bs)
    # pad on every bar (quiet in drive, prominent in intro / break)
    padg = {'intro': .55, 'build': .30, 'groove': .26, 'drive': .22, 'break': .62, 'finale': .30}[sec]
    stereo(bs, pad([c for c in chord] + [chord[0] - 12], 4 * BEAT + 0.3), padg, pan=0.15)
    for s in range(16):                       # 16th-note grid
        t = bs + s * BEAT / 4
        if t >= DUR: break
        beat = s // 4; sub = s % 4
        # ---- drums
        four = sec in ('groove', 'drive', 'finale')
        if four and sub == 0:
            put(L, t, K, .95); put(Rr, t, K, .95); kick_times.append(t)
        elif sec == 'build' and sub == 0 and beat in (0, 2):
            put(L, t, K, .8); put(Rr, t, K, .8); kick_times.append(t)
        if sec in ('groove', 'drive', 'finale') and sub == 0 and beat in (1, 3):
            stereo(t, C_, .62)
        elif sec == 'build' and sub == 0 and beat == 3:
            stereo(t, C_, .5)
        if sec in ('build', 'groove', 'drive', 'finale'):
            if sub == 2: stereo(t, HO if sec != 'build' else HC, .5, pan=.1)             # off-beat hat
            if sec in ('drive', 'finale') and sub in (1, 3): stereo(t, HC, .28, pan=-.1)   # 16ths = more drive
            if sec in ('build', 'groove') and sub == 0: stereo(t, HC, .22)
        # ---- bass: 8ths, octave jumps
        if sec in ('build', 'groove', 'drive', 'finale') and sub in (0, 2):
            m = bmid + (12 if (sub == 2 and beat % 2) else 0)
            stereo(t, bass(m, BEAT * .48), .72)
        # ---- bright plucks (the "fresh" sparkle)
        notes = chord + [chord[0] + 12]
        if sec in ('groove', 'drive', 'finale') and (sec != 'groove' or sub in (0, 2)):
            n_ = notes[(s) % 4] + 12
            stereo(t, pluck(n_, .32), .5 if sec == 'drive' else .4, pan=0.25 if (s // 2) % 2 else -0.25)
        elif sec == 'build' and sub == 2:
            stereo(t, pluck(notes[beat % 4] + 12, .3), .32, pan=.2)
        # ---- chord stabs on the off-beats
        if sec in ('groove', 'drive', 'finale') and sub == 2 and beat in (0, 2, 3 if sec != 'groove' else 0):
            stereo(t, stab([c + 12 for c in chord]), .38 if sec != 'groove' else .3)
        # ---- bells in intro and breakdown
        if sec in ('intro', 'break') and sub in (0, 2) and beat in (0, 2):
            stereo(t, bell(notes[(s // 2) % 4] + 24), .5, pan=.3 if beat else -.3)
    if sec == 'break':                         # gentle downbeat pulse through the breakdown
        stereo(bs, K, .35)

# ---- risers + impacts at the section lines (they sit under the film's slash wipes)
for a, b, nme in SECTIONS[1:]:
    d = 2.0 if nme != 'finale' else 2.6
    st = a - d
    stereo(st, riser(d), .45 if nme != 'finale' else .6)
    stereo(a, CR, .42); stereo(a, BO, .7)
for tcut in (30.0, 33.0, 36.0):                # the montage cuts
    stereo(tcut - .15, CR, .22)

# ---- sidechain pump: duck everything except the kick every kick hit (that "breathing" energy)
duck = np.ones(N)
for kt in kick_times:
    i = int(kt * SR); n = int(SR * 0.28)
    if i + n < N:
        shape = 1 - 0.55 * np.exp(-np.arange(n) / (SR * 0.09))
        duck[i:i + n] = np.minimum(duck[i:i + n], shape)
# (the kick itself is already in the bus; ducking the bus slightly lowers it too — fine, it keeps the pump even)
L *= duck ** 0.6; Rr *= duck ** 0.6

# ---- simple room: short reverb on the whole bus
ir_n = int(SR * 0.9); t = np.arange(ir_n) / SR
ir = rng.standard_normal(ir_n) * np.exp(-t * 5.2); ir[0] = 0
ir /= np.abs(ir).sum() * 0.55
wetL = signal.fftconvolve(L, ir)[:N]; wetR = signal.fftconvolve(Rr, ir[::-1] * 0.98)[:N]
L = L * 0.82 + wetL * 0.35; Rr = Rr * 0.82 + wetR * 0.35

# ---- master: fade in / out, soft clip, normalise
fade_in = np.minimum(1, np.arange(N) / (SR * 1.2)); fade_out = np.minimum(1, (N - np.arange(N)) / (SR * 3.2))
L *= fade_in * fade_out; Rr *= fade_in * fade_out
peak = max(np.abs(L).max(), np.abs(Rr).max())
L = np.tanh(L / peak * 1.5) / np.tanh(1.5) * 0.88; Rr = np.tanh(Rr / peak * 1.5) / np.tanh(1.5) * 0.88
out = (np.stack([L, Rr], 1) * 32767).astype(np.int16)
here = os.path.dirname(os.path.abspath(__file__))
with wave.open(os.path.join(here, 'music.wav'), 'wb') as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR); w.writeframes(out.tobytes())
print('music.wav written', out.shape, 'kicks', len(kick_times))
