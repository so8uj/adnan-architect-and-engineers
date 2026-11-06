import asyncio, os, subprocess, sys, json
import edge_tts

HERE = os.path.dirname(os.path.abspath(__file__))
FP = os.path.join(HERE, '..', 'ff', 'node_modules', 'ffprobe-static', 'bin', 'win32', 'x64', 'ffprobe.exe')
VOICE = sys.argv[1] if len(sys.argv) > 1 else 'bn-BD-PradeepNeural'
RATE = sys.argv[2] if len(sys.argv) > 2 else '-6%'

# (file, planned start second, narration text)
LINES = [
    ('v1', 2.0,  'অ্যাডনান আর্কিটেক্ট অ্যান্ড ইঞ্জিনিয়ার্স। আপনার নির্ভরযোগ্য সঙ্গী।'),
    ('v2', 7.9,  'নির্মাণের আগেই দেখুন, আপনার স্বপ্নের ভবন। বাস্তবসম্মত থ্রিডি ডিজাইনে।'),
    ('v3', 16.0, 'আর্কিটেকচারাল থেকে স্ট্রাকচারাল, ইলেকট্রিক্যাল থেকে ফায়ারফাইটিং, প্ল্যান পাস থেকে ইন্টেরিয়র। সব সেবা, এক ছাদের নিচে।'),
    ('v4', 27.6, 'আমাদের থ্রিডি ও ল্যান্ডস্কেপ ডিজাইনে, নির্মাণের আগেই দেখে নিন আপনার ভবনের চূড়ান্ত রূপ।'),
    ('v5', 39.6, 'দেড় হাজারেরও বেশি সম্পন্ন প্রজেক্ট। আর দেড় হাজারেরও বেশি সন্তুষ্ট ক্লায়েন্ট।'),
    ('v6', 47.2, 'আপনার প্রজেক্ট নিয়ে কথা বলুন। কল করুন, স্ক্রিনের নম্বরে।'),
]

async def main():
    os.makedirs(os.path.join(HERE, 'voice'), exist_ok=True)
    info = []
    for name, start, text in LINES:
        out = os.path.join(HERE, 'voice', name + '.mp3')
        await edge_tts.Communicate(text, VOICE, rate=RATE).save(out)
        r = subprocess.run([FP, '-v', 'error', '-show_entries', 'format=duration', '-of', 'default=nw=1:nk=1', out], capture_output=True, text=True)
        dur = float(r.stdout.strip())
        info.append({'name': name, 'start': start, 'dur': round(dur, 2), 'end': round(start + dur, 2)})
        print(name, 'start', start, 'dur', round(dur, 2), 'end', round(start + dur, 2))
    json.dump(info, open(os.path.join(HERE, 'voice', 'info.json'), 'w'))

asyncio.run(main())
