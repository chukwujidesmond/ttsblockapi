import io
import time
import tempfile
from pathlib import Path

import numpy as np
import soundfile as sf
import noisereduce as nr
import librosa

from fastapi import FastAPI, UploadFile, File, Header, Form, HTTPException, BackgroundTasks
from fastapi.responses import StreamingResponse
from df.enhance import enhance, init_df, load_audio, save_audio
from contextlib import asynccontextmanager
import asyncio
import os

API_SECRET = os.getenv("API_SECRET", "change-me")

_df_model, _df_state, _df_sr = None, None, None

def load_model():
    global _df_model, _df_state, _df_sr
    print("Loading DeepFilterNet...")
    _df_model, _df_state, _ = init_df()  # returns 3 values, ignore the 3rd
    _df_sr = _df_state.sr()
    print("Model ready.")

@asynccontextmanager
async def lifespan(app: FastAPI):
    loop = asyncio.get_event_loop()
    await loop.run_in_executor(None, load_model)
    yield

app = FastAPI(title="Audio Cleaner", lifespan=lifespan)


def guard(x_api_secret: str = None):
    if x_api_secret != API_SECRET:
        raise HTTPException(status_code=401, detail="Unauthorized")


@app.get("/health")
def health():
    return {"status": "ok", "model_loaded": _df_model is not None}


@app.post("/clean")
async def clean_audio(
    background_tasks: BackgroundTasks,
    file: UploadFile = File(...),
    x_api_secret: str = Header(None),
    # FIX 2: declare as Form fields so multipart form data is parsed correctly
    strength: float = Form(0.85),
    model: str = Form("deepfilter"),
):
    guard(x_api_secret)

    raw_bytes = await file.read()

    with tempfile.NamedTemporaryFile(suffix=".wav", delete=False) as tmp_in:
        tmp_in.write(raw_bytes)
        input_path = Path(tmp_in.name)

    # FIX 1: with_suffix() only replaces the extension; build the output name explicitly
    output_path = input_path.parent / (input_path.stem + "_clean.wav")

    try:
        loop = asyncio.get_event_loop()
        await loop.run_in_executor(
            None, _process, input_path, output_path, model, strength
        )

        # FIX 4: use BackgroundTask for cleanup so it runs after response is sent,
        # even if the client disconnects mid-stream
        def cleanup():
            input_path.unlink(missing_ok=True)
            output_path.unlink(missing_ok=True)

        background_tasks.add_task(cleanup)

        return StreamingResponse(
            open(output_path, "rb"),
            media_type="audio/wav",
            headers={"X-Processing-Model": model},
            background=background_tasks,
        )

    except Exception as e:
        input_path.unlink(missing_ok=True)
        output_path.unlink(missing_ok=True)
        raise HTTPException(status_code=500, detail=str(e))


def _process(input_path, output_path, model, strength):
    if model == "deepfilter":
        _run_deepfilter(input_path, output_path, strength)
    else:
        _run_noisereduce(input_path, output_path, strength)
    _normalize(output_path)


def _run_deepfilter(input_path, output_path, strength):
    audio, meta = load_audio(str(input_path), sr=_df_sr)
    enhanced = enhance(_df_model, _df_state, audio, atten_lim_db=int(strength * 100))
    save_audio(str(output_path), enhanced, _df_sr)


def _run_noisereduce(input_path, output_path, strength):
    audio, sr = librosa.load(str(input_path), sr=None, mono=False)
    if audio.ndim == 2:
        cleaned = np.vstack([
            nr.reduce_noise(y=ch, sr=sr, prop_decrease=strength, stationary=False)
            for ch in audio
        ])
    else:
        cleaned = nr.reduce_noise(y=audio, sr=sr, prop_decrease=strength, stationary=False)
    sf.write(str(output_path), cleaned.T if cleaned.ndim == 2 else cleaned, sr)


def _normalize(path, target_db=-16.0):
    audio, sr = librosa.load(str(path), sr=None, mono=False)
    peak = np.max(np.abs(audio))
    if peak > 0:
        gain = (10 ** (target_db / 20)) / peak
        audio = np.clip(audio * gain, -1.0, 1.0)
    sf.write(str(path), audio.T if audio.ndim == 2 else audio, sr)