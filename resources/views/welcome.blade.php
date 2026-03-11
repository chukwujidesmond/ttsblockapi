<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SpeechBlocks – Voiceover Studio</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,400&display=swap" rel="stylesheet">
<style>
/* ─── RESET & TOKENS ─────────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  /* Brand */
  --coral:     #F05A3A;
  --coral-l:   #F7896E;
  --coral-d:   #C8401F;
  --coral-glow:rgba(240,90,58,.15);

  /* Surface */
  --sand:      #F0EDE6;
  --sand-mid:  #E3DED5;
  --sand-dark: #D4CEC4;
  --white:     #FDFCFB;

  /* Text */
  --dark:  #1A1714;
  --muted: #8C847A;

  /* Timeline dark palette */
  --tl-bg:   #1A1715;
  --tl-row:  #232120;
  --tl-row2: #201E1C;
  --tl-b:    #2C2A28;
  --tl-b2:   #363230;
  --tl-text: #9A928A;
  --tl-dim:  #504845;

  /* Sizes */
  --track-h:  60px;
  --ruler-h:  32px;
  --label-w:  216px;
  --sb-w:     220px;
  --scripts-w:276px;

  /* Elevation */
  --shadow-sm: 0 1px 3px rgba(0,0,0,.07),0 1px 2px rgba(0,0,0,.05);
  --shadow-md: 0 4px 12px rgba(0,0,0,.1),0 2px 4px rgba(0,0,0,.06);
  --shadow-lg: 0 12px 32px rgba(0,0,0,.18),0 4px 8px rgba(0,0,0,.1);

  /* Motion */
  --ease-out: cubic-bezier(.22,1,.36,1);
  --ease-in-out: cubic-bezier(.65,0,.35,1);
}

/* ─── BASE ───────────────────────────────────────────────────────────── */
html, body { height: 100%; }

body {
  font-family: 'DM Sans', sans-serif;
  background: var(--sand);
  color: var(--dark);
  display: flex;
  overflow: hidden;
  font-size: 14px;
  -webkit-font-smoothing: antialiased;
}

button { font-family: inherit; cursor: pointer; }
input, select, textarea { font-family: inherit; }

/* ─── SCROLLBAR ──────────────────────────────────────────────────────── */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--sand-dark); border-radius: 10px; }
.tl-scroll::-webkit-scrollbar-track { background: #141210; }
.tl-scroll::-webkit-scrollbar-thumb { background: var(--tl-b2); }

/* ─── SIDEBAR ────────────────────────────────────────────────────────── */
.sidebar {
  width: var(--sb-w);
  background: var(--white);
  border-right: 1px solid var(--sand-mid);
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  z-index: 10;
}

.logo {
  padding: 20px;
  font-family: 'Syne', sans-serif;
  font-size: 17px;
  font-weight: 800;
  letter-spacing: -.6px;
  border-bottom: 1px solid var(--sand-mid);
  display: flex;
  align-items: center;
  gap: 8px;
  user-select: none;
}
.logo-mark {
  width: 28px; height: 28px;
  background: var(--coral);
  border-radius: 7px;
  display: flex; align-items: center; justify-content: center;
  color: #fff;
  font-size: 14px;
  flex-shrink: 0;
  box-shadow: 0 2px 8px var(--coral-glow);
}
.logo span { color: var(--coral); }

.nav-section-label {
  padding: 18px 20px 6px;
  font-size: 9.5px;
  font-weight: 700;
  color: var(--muted);
  letter-spacing: 1.2px;
  text-transform: uppercase;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 20px;
  font-size: 13px;
  font-weight: 500;
  color: var(--muted);
  cursor: pointer;
  transition: color .15s, background .15s;
  border-left: 2px solid transparent;
  position: relative;
}
.nav-item .nav-icon { font-size: 15px; width: 20px; text-align: center; }
.nav-item:hover { color: var(--dark); background: var(--sand); }
.nav-item.active {
  color: var(--coral);
  background: #FEF0EC;
  border-left-color: var(--coral);
  font-weight: 600;
}

.sb-footer {
  margin-top: auto;
  padding: 16px 20px;
  border-top: 1px solid var(--sand-mid);
  font-size: 12.5px;
  color: var(--muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: color .15s;
}
.sb-footer:hover { color: var(--dark); }

/* ─── MAIN ───────────────────────────────────────────────────────────── */
.main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

/* ─── TOP HEADER ─────────────────────────────────────────────────────── */
.top-hdr {
  height: 60px;
  background: var(--white);
  border-bottom: 1px solid var(--sand-mid);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  flex-shrink: 0;
  gap: 16px;
}

.proj-info { display: flex; align-items: center; gap: 12px; }
.proj-title {
  font-family: 'Syne', sans-serif;
  font-size: 15px;
  font-weight: 700;
  letter-spacing: -.3px;
}
.proj-badge {
  font-size: 10px;
  font-weight: 600;
  background: #F0F9F4;
  color: #2A8A50;
  border: 1px solid #B8E8C8;
  border-radius: 20px;
  padding: 2px 8px;
  letter-spacing: .3px;
}

.hdr-right { display: flex; align-items: center; gap: 12px; }

.hdr-icon-btn {
  width: 34px; height: 34px;
  border-radius: 8px;
  background: none;
  border: 1px solid var(--sand-mid);
  display: flex; align-items: center; justify-content: center;
  color: var(--muted);
  font-size: 15px;
  transition: all .15s;
}
.hdr-icon-btn:hover { background: var(--sand); color: var(--dark); border-color: var(--sand-dark); }

.user-chip {
  display: flex; align-items: center; gap: 8px;
  padding: 4px 10px 4px 4px;
  border: 1px solid var(--sand-mid);
  border-radius: 24px;
  cursor: pointer;
  transition: border-color .15s;
}
.user-chip:hover { border-color: var(--sand-dark); }
.avatar {
  width: 28px; height: 28px;
  border-radius: 50%;
  background: var(--coral);
  display: flex; align-items: center; justify-content: center;
  color: #fff;
  font-weight: 700;
  font-size: 11px;
  letter-spacing: .5px;
}
.user-meta { line-height: 1.2; }
.user-name  { font-weight: 600; font-size: 11.5px; display: block; }
.user-email { color: var(--muted); font-size: 10px; }

/* ─── CONTENT AREA ───────────────────────────────────────────────────── */
.content-area { flex: 1; display: flex; overflow: hidden; min-height: 0; }

/* ─── SCRIPTS PANEL ──────────────────────────────────────────────────── */
.scripts-panel {
  width: var(--scripts-w);
  flex-shrink: 0;
  background: var(--white);
  border-right: 1px solid var(--sand-mid);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.sp-hdr {
  padding: 16px 18px;
  border-bottom: 1px solid var(--sand-mid);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}
.sp-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 14.5px; }

.add-script-btn {
  display: flex; align-items: center; gap: 4px;
  font-size: 11.5px; font-weight: 600;
  color: var(--coral);
  padding: 5px 10px;
  border: 1.5px solid var(--coral);
  border-radius: 6px;
  background: none;
  transition: all .15s;
  letter-spacing: -.1px;
}
.add-script-btn:hover { background: var(--coral); color: #fff; }

.section-label {
  padding: 14px 18px 6px;
  font-size: 9.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--muted);
}

.scripts-list { flex: 1; overflow-y: auto; padding: 8px 8px 20px; }

/* Script card */
.sc-card {
  background: #FFF8F5;
  border: 1.5px solid var(--sand-mid);
  border-radius: 10px;
  padding: 11px 12px;
  margin-bottom: 6px;
  cursor: pointer;
  transition: border-color .15s, background .15s, box-shadow .15s;
}
.sc-card:hover { border-color: var(--coral-l); background: #FEF5F2; box-shadow: var(--shadow-sm); }
.sc-card.active { border-color: var(--coral); background: #FEF0EC; }

.sc-top { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }

.sc-play-btn {
  width: 26px; height: 26px;
  border-radius: 50%;
  background: var(--dark);
  border: none;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  transition: background .15s, transform .12s;
}
.sc-play-btn:hover { background: var(--coral); transform: scale(1.08); }
.sc-play-btn svg { width: 9px; fill: #fff; margin-left: 2px; }

.sc-name {
  font-size: 12px; font-weight: 600; flex: 1;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sc-dur { font-size: 10px; color: var(--muted); flex-shrink: 0; }

.wf-mini {
  height: 20px; border-radius: 4px;
  overflow: hidden; background: #F0E8E4;
}

.sc-actions {
  display: flex; align-items: center; justify-content: space-between;
  margin-top: 9px;
}
.sc-icon-group { display: flex; gap: 2px; }
.sc-icon-btn {
  background: none; border: none;
  color: var(--muted); font-size: 12px;
  padding: 3px 5px; border-radius: 4px;
  transition: color .12s, background .12s;
}
.sc-icon-btn:hover { color: var(--dark); background: var(--sand); }

.add-to-tl-btn {
  font-size: 10px; font-weight: 600;
  background: var(--coral); color: #fff;
  border: none; border-radius: 5px;
  padding: 4px 9px;
  transition: background .15s;
  letter-spacing: -.1px;
}
.add-to-tl-btn:hover { background: var(--coral-d); }

.sc-play-btn.sc-playing { background: var(--coral); }
.sc-play-btn.sc-playing:hover { background: var(--coral-d); }

.sc-no-audio {
  font-size: 9.5px; color: var(--muted);
  font-style: italic; padding: 2px 0 4px;
}

.sc-icon-btn:disabled { opacity: .3; cursor: not-allowed; }
.sc-icon-btn:disabled:hover { color: var(--muted); background: none; }
.sc-icon-danger:hover { color: #D05040 !important; }

.sc-name[data-action="rename"] { cursor: text; }
.sc-name[data-action="rename"]:hover { text-decoration: underline dotted var(--muted); }

.file-badge {
  display: inline-flex; align-items: center; gap: 4px;
  font-size: 9px; font-weight: 600;
  background: #EAF0FE; color: #2D5BBF;
  border-radius: 4px; padding: 2px 6px;
  margin-top: 4px;
  border: 1px solid #C8D8F8;
}

/* ─── EDITOR PANEL ───────────────────────────────────────────────────── */
.editor-panel { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

/* Toolbar */
.ed-toolbar {
  height: 54px;
  background: var(--white);
  border-bottom: 1px solid var(--sand-mid);
  display: flex;
  align-items: center;
  padding: 0 20px;
  gap: 6px;
  flex-shrink: 0;
  overflow-x: auto;
  scrollbar-width: none;
}
.ed-toolbar::-webkit-scrollbar { display: none; }

.tb-control {
  display: flex; align-items: center; gap: 6px;
  border: 1.5px solid var(--sand-mid);
  border-radius: 8px;
  padding: 5px 10px;
  font-size: 12.5px;
  background: var(--white);
  transition: border-color .15s;
  white-space: nowrap;
  flex-shrink: 0;
}
.tb-control:hover { border-color: var(--sand-dark); }
.tb-control:focus-within { border-color: var(--coral-l); }

.tb-control select {
  border: none; outline: none;
  font-size: 12.5px; background: transparent;
  color: var(--dark); cursor: pointer;
}

.tb-separator { width: 1px; height: 26px; background: var(--sand-mid); margin: 0 4px; flex-shrink: 0; }

.tb-label { font-size: 11px; color: var(--muted); user-select: none; }

.num-spinner { display: flex; align-items: center; gap: 2px; }
.num-spinner input[type="number"] {
  width: 30px; border: none; outline: none;
  text-align: center; font-size: 12.5px;
  background: transparent; color: var(--dark);
  -moz-appearance: textfield;
}
.num-spinner input::-webkit-inner-spin-button { -webkit-appearance: none; }
.spin-col { display: flex; flex-direction: column; gap: 1px; }
.spin-btn {
  width: 13px; height: 10px;
  border: none; background: none;
  cursor: pointer; color: var(--muted);
  font-size: 8px; padding: 0; line-height: 1;
  transition: color .1s;
}
.spin-btn:hover { color: var(--dark); }

.tb-plain-btn {
  border: 1.5px solid var(--sand-mid);
  border-radius: 8px; padding: 6px 13px;
  font-size: 12.5px; font-weight: 500;
  background: var(--white);
  transition: all .15s; flex-shrink: 0;
}
.tb-plain-btn:hover { border-color: var(--coral-l); color: var(--coral); }

.generate-btn {
  background: var(--coral); color: #fff;
  border: none; border-radius: 8px;
  padding: 8px 18px;
  font-weight: 600; font-size: 13px;
  transition: background .15s, box-shadow .15s;
  white-space: nowrap; flex-shrink: 0;
}
.generate-btn:hover {
  background: var(--coral-d);
  box-shadow: 0 4px 12px rgba(240,90,58,.3);
}

.ml-auto { margin-left: auto; }

/* Voice avatar bubble */
.voice-avatar {
  width: 22px; height: 22px;
  border-radius: 50%;
  background: var(--coral-l);
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; flex-shrink: 0;
}

/* Script editor */
.script-editor {
  flex: 1; padding: 20px 24px;
  overflow-y: auto; background: var(--white);
  min-height: 0;
}
.field-label {
  font-size: 9.5px; font-weight: 700;
  text-transform: uppercase; letter-spacing: 1px;
  color: var(--muted); margin-bottom: 10px;
}
.script-textarea {
  width: 100%; min-height: 90px;
  border: none; outline: none; resize: none;
  font-size: 14px; line-height: 1.75;
  color: var(--dark); background: transparent;
}

.ed-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 24px;
  border-top: 1px solid var(--sand-mid);
  background: var(--white);
  flex-shrink: 0;
}
.ed-action-group { display: flex; gap: 4px; }
.ed-action-btn {
  background: none; border: none;
  color: var(--muted); font-size: 14px;
  padding: 5px 7px; border-radius: 6px;
  transition: color .12s, background .12s;
}
.ed-action-btn:hover { color: var(--dark); background: var(--sand); }
.ed-duration {
  font-size: 12px; color: var(--muted); font-weight: 500;
  font-variant-numeric: tabular-nums;
}

/* ─── TIMELINE ───────────────────────────────────────────────────────── */
.tl-section {
  flex-shrink: 0;
  background: var(--tl-bg);
  border-top: 2px solid var(--coral);
  display: flex;
  flex-direction: column;
  height: 272px;
}

/* Topbar */
.tl-topbar {
  display: flex; align-items: center;
  padding: 7px 14px; gap: 8px;
  border-bottom: 1px solid var(--tl-b2);
  flex-shrink: 0;
  min-height: 46px;
}

.tl-btn {
  display: flex; align-items: center; gap: 5px;
  font-size: 11.5px; font-weight: 600;
  border: none; border-radius: 6px;
  padding: 6px 12px;
  transition: all .15s; flex-shrink: 0;
  letter-spacing: -.1px;
}
.tl-btn-primary { background: var(--coral); color: #fff; }
.tl-btn-primary:hover { background: var(--coral-d); box-shadow: 0 2px 8px rgba(240,90,58,.3); }
.tl-btn-ghost { background: #272422; color: var(--tl-text); border: 1px solid var(--tl-b2); }
.tl-btn-ghost:hover { background: #302D2A; color: #D0C8C0; }

/* Zoom */
.zoom-group { display: flex; align-items: center; gap: 3px; }
.zoom-label { font-size: 10.5px; color: var(--tl-dim); width: 46px; text-align: center; }
.zoom-btn {
  background: #242220; border: none; border-radius: 4px;
  color: #706A65; width: 20px; height: 20px;
  font-size: 13px; line-height: 1;
  display: flex; align-items: center; justify-content: center;
  transition: all .12s;
}
.zoom-btn:hover { background: #302D2A; color: #D0C8C0; }

/* Transport */
.tl-transport { display: flex; align-items: center; gap: 10px; margin-left: auto; }

.tl-timecode {
  font-size: 13px; font-weight: 600;
  color: #C8C0B8; letter-spacing: .5px;
  font-variant-numeric: tabular-nums;
  display: flex; align-items: center; gap: 1px;
}
.tl-timecode-sep { color: var(--tl-dim); margin: 0 3px; }

.tl-play-btn {
  width: 32px; height: 32px;
  border-radius: 50%;
  background: rgba(255,255,255,.9);
  border: none;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  transition: background .15s, transform .12s, box-shadow .15s;
}
.tl-play-btn:hover {
  background: var(--coral);
  transform: scale(1.06);
  box-shadow: 0 2px 10px rgba(240,90,58,.4);
}
.tl-play-btn:hover svg { fill: #fff; }
.tl-play-btn svg { width: 11px; fill: var(--dark); margin-left: 2px; }

.tl-action-group { display: flex; gap: 2px; align-items: center; }
.tl-icon-btn {
  background: none; border: none;
  color: var(--tl-dim); font-size: 14px;
  padding: 5px 6px; border-radius: 5px;
  transition: color .12s, background .12s;
}
.tl-icon-btn:hover { color: #D0C8C0; background: #2A2725; }

.tl-save-btn {
  background: #F5C842; color: #1A1410;
  border: none; border-radius: 6px;
  padding: 6px 12px;
  font-size: 11.5px; font-weight: 700;
  transition: background .15s;
}
.tl-save-btn:hover { background: #E2B530; }

/* Hidden file input */
#fileInput { display: none; }

/* ─── TIMELINE BODY ──────────────────────────────────────────────────── */
.tl-body { flex: 1; display: flex; overflow: hidden; }

/* Label column */
.tl-labels {
  width: var(--label-w);
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  background: #17150F;
  border-right: 1px solid var(--tl-b2);
  overflow: hidden;
}
.tl-ruler-spacer {
  height: var(--ruler-h);
  flex-shrink: 0;
  border-bottom: 1px solid var(--tl-b2);
  background: #161410;
}
.tl-label-list { flex: 1; overflow: hidden; }

/* Track label row */
.tl-lbl-row {
  height: var(--track-h);
  border-bottom: 1px solid var(--tl-b);
  display: flex; align-items: center;
  padding: 0 8px 0 6px; gap: 5px;
  cursor: grab; user-select: none;
  transition: background .12s;
  position: relative;
}
.tl-lbl-row:hover { background: #211E1A; }
.tl-lbl-row.lbl-dragging { opacity: .3; background: rgba(240,90,58,.06) !important; }
.tl-lbl-row.lbl-drop-above { border-top: 2px solid var(--coral) !important; }
.tl-lbl-row.lbl-drop-below { border-bottom: 2px solid var(--coral) !important; }

.drag-grip {
  color: #302E2C; font-size: 11px; cursor: grab;
  flex-shrink: 0; line-height: 1;
  transition: color .12s;
}
.tl-lbl-row:hover .drag-grip { color: #504845; }

.lbl-name-wrap { flex: 1; overflow: hidden; min-width: 0; }
.lbl-name {
  font-size: 11px; font-weight: 600;
  color: #6A6460;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  display: block;
  transition: color .12s;
}
.lbl-name.has-file { color: #6898E8; }
.tl-lbl-row:hover .lbl-name { color: #908880; }

.lbl-file-info {
  font-size: 9px; color: #3A5070;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  display: block; margin-top: 1px;
}

.lbl-btn-group { display: flex; gap: 0; flex-shrink: 0; }
.lbl-btn {
  background: none; border: none;
  color: #3A3835; font-size: 11px;
  padding: 3px 4px; border-radius: 4px;
  transition: color .1s, background .1s;
}
.lbl-btn:hover { color: #A8A09A; background: #252220; }
.lbl-btn.active { color: var(--coral); }
.lbl-btn.mute-btn.active { color: #F0A030; }

.vol-strip {
  position: absolute;
  bottom: 4px; left: 26px; right: 6px;
  display: flex; align-items: center; gap: 4px;
}
.vol-slider {
  flex: 1; height: 3px; cursor: pointer;
  accent-color: var(--coral); opacity: .6;
  transition: opacity .15s;
}
.tl-lbl-row:hover .vol-slider { opacity: 1; }
.vol-pct { font-size: 8px; color: #3A3835; width: 22px; text-align: right; }

/* ─── SCROLLABLE TRACK AREA ──────────────────────────────────────────── */
.tl-scroll {
  flex: 1;
  overflow: auto;
  position: relative;
}

.tl-inner {
  position: relative;
  display: flex;
  flex-direction: column;
}

/* Ruler */
.ruler-row {
  height: var(--ruler-h);
  background: #161410;
  position: sticky; top: 0; z-index: 8;
  border-bottom: 1px solid var(--tl-b2);
  cursor: pointer; flex-shrink: 0;
}
.ruler-row canvas { display: block; }

/* Track rows */
.track-row {
  height: var(--track-h);
  border-bottom: 1px solid var(--tl-b);
  position: relative;
  background: var(--tl-row);
  flex-shrink: 0;
  transition: background .1s;
}
.track-row:nth-child(even) { background: var(--tl-row2); }
.track-row.row-dragover { background: rgba(240,90,58,.07); }
.track-row.muted-row { opacity: .45; }

.empty-hint {
  position: absolute; inset: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 10px; color: #282420;
  pointer-events: none; letter-spacing: .3px;
  gap: 5px;
}

/* ─── CLIP ───────────────────────────────────────────────────────────── */
.clip {
  position: absolute;
  top: 5px;
  height: calc(var(--track-h) - 10px);
  border-radius: 6px;
  overflow: hidden;
  cursor: grab;
  user-select: none;
  box-shadow: 0 2px 8px rgba(0,0,0,.55), inset 0 1px 0 rgba(255,255,255,.08);
  min-width: 20px;
  transition: box-shadow .15s;
}
.clip:active { cursor: grabbing; }
.clip:hover { box-shadow: 0 4px 14px rgba(0,0,0,.65), inset 0 1px 0 rgba(255,255,255,.12); }
.clip.sel {
  outline: 2px solid rgba(255,255,255,.8);
  outline-offset: 1px;
  box-shadow: 0 4px 16px rgba(0,0,0,.7);
}

.clip.voice { background: linear-gradient(135deg, #E85030 0%, #A02818 100%); }
.clip.music { background: linear-gradient(135deg, #2E80F0 0%, #1450B8 100%); }
.clip.sfx   { background: linear-gradient(135deg, #28C07A 0%, #157840 100%); }
.clip.muted-clip { opacity: .3; }

.clip-wf { position: absolute; inset: 0; opacity: .35; pointer-events: none; }

.clip-body {
  position: relative; z-index: 2;
  display: flex; align-items: center;
  width: 100%; height: 100%;
  padding: 0 8px; gap: 5px; overflow: hidden;
}
.clip-icon { font-size: 9px; flex-shrink: 0; }
.clip-label {
  font-size: 9px; font-weight: 700;
  color: rgba(255,255,255,.92);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  flex: 1;
}
.clip-duration {
  font-size: 8px;
  color: rgba(255,255,255,.45);
  white-space: nowrap; font-variant-numeric: tabular-nums;
}

/* Resize handles */
.rs-handle {
  position: absolute; top: 0; bottom: 0; width: 6px;
  background: rgba(255,255,255,.08); z-index: 3;
  transition: background .15s;
}
.rs-handle:hover { background: rgba(255,255,255,.3); }
.rs-l { left: 0; border-radius: 6px 0 0 6px; cursor: w-resize; }
.rs-r { right: 0; border-radius: 0 6px 6px 0; cursor: e-resize; }

/* ─── PLAYHEAD ───────────────────────────────────────────────────────── */
.playhead {
  position: absolute;
  top: 0; bottom: 0;
  width: 1.5px;
  background: var(--coral);
  z-index: 10;
  pointer-events: none;
  will-change: transform;
}
.playhead::before {
  content: '';
  position: absolute;
  top: 0; left: 50%;
  transform: translateX(-50%);
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-top: 7px solid var(--coral);
}
.playhead::after {
  content: '';
  position: absolute;
  top: 0; bottom: 0;
  left: 0; width: 1.5px;
  background: linear-gradient(to bottom, var(--coral) 0%, rgba(240,90,58,.2) 100%);
}

/* ─── CONTEXT MENU ───────────────────────────────────────────────────── */
.ctx-menu {
  position: fixed;
  background: #181412;
  border: 1px solid #2E2A28;
  border-radius: 10px;
  padding: 4px;
  z-index: 500;
  min-width: 175px;
  box-shadow: 0 12px 36px rgba(0,0,0,.75), 0 2px 8px rgba(0,0,0,.4);
  display: none;
  opacity: 0;
  transform: scale(.96) translateY(-4px);
  transform-origin: top left;
  transition: opacity .12s var(--ease-out), transform .12s var(--ease-out);
}
.ctx-menu.show {
  display: block;
  opacity: 1;
  transform: scale(1) translateY(0);
}
.ctx-item {
  padding: 7px 12px;
  font-size: 12px; color: #A8A098;
  cursor: pointer; border-radius: 6px;
  transition: background .08s, color .08s;
  display: flex; align-items: center; gap: 9px;
}
.ctx-item:hover { background: #272422; color: #F0E8E0; }
.ctx-item.danger { color: #D05040; }
.ctx-item.danger:hover { background: rgba(208,80,64,.12); color: #E87060; }
.ctx-sep { height: 1px; background: #2A2622; margin: 3px 4px; }

/* ─── TOAST ──────────────────────────────────────────────────────────── */
.toast {
  position: fixed;
  bottom: 26px; right: 26px;
  background: #1E1C1A;
  color: #E8E0D8;
  padding: 10px 16px;
  border-radius: 9px;
  font-size: 12.5px; font-weight: 500;
  z-index: 600;
  transform: translateY(70px);
  opacity: 0;
  transition: transform .3s var(--ease-out), opacity .3s;
  pointer-events: none;
  border: 1px solid #2E2A28;
  box-shadow: var(--shadow-lg);
  max-width: 320px;
}
.toast.show { transform: translateY(0); opacity: 1; }

/* ─── MODAL ──────────────────────────────────────────────────────────── */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(26,23,20,.6);
  backdrop-filter: blur(4px);
  z-index: 400;
  display: none;
  align-items: center; justify-content: center;
}
.modal-overlay.show { display: flex; }

.modal {
  background: var(--white);
  border-radius: 14px;
  padding: 28px;
  width: 400px;
  box-shadow: var(--shadow-lg);
  animation: modalIn .2s var(--ease-out);
}
@keyframes modalIn {
  from { opacity: 0; transform: translateY(12px) scale(.97); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

.modal-title {
  font-family: 'Syne', sans-serif;
  font-size: 17px; font-weight: 700;
  margin-bottom: 20px; letter-spacing: -.3px;
}

.modal-field { margin-bottom: 14px; }
.modal-field-label {
  display: block;
  font-size: 10.5px; font-weight: 700;
  color: var(--muted);
  text-transform: uppercase; letter-spacing: .6px;
  margin-bottom: 6px;
}
.modal-input {
  width: 100%;
  border: 1.5px solid var(--sand-mid);
  border-radius: 8px;
  padding: 9px 12px;
  font-size: 13px; color: var(--dark);
  outline: none;
  transition: border-color .15s, box-shadow .15s;
  background: var(--white);
}
.modal-input:focus {
  border-color: var(--coral);
  box-shadow: 0 0 0 3px var(--coral-glow);
}

.modal-actions {
  display: flex; gap: 8px; justify-content: flex-end;
  margin-top: 22px;
}
.modal-cancel {
  background: none;
  border: 1.5px solid var(--sand-mid);
  border-radius: 8px; padding: 8px 16px;
  font-size: 13px; color: var(--muted);
  transition: all .15s;
}
.modal-cancel:hover { border-color: var(--sand-dark); color: var(--dark); }
.modal-confirm {
  background: var(--coral); color: #fff;
  border: none; border-radius: 8px;
  padding: 8px 18px;
  font-size: 13px; font-weight: 600;
  transition: background .15s, box-shadow .15s;
}
.modal-confirm:hover {
  background: var(--coral-d);
  box-shadow: 0 3px 10px rgba(240,90,58,.3);
}
</style>
</head>
<body>

<!-- ── SIDEBAR ────────────────────────────────────────────────────── -->
<aside class="sidebar">
  <div class="logo">
    <div class="logo-mark">🎙</div>
    <span><span>Speech</span>Blocks</span>
  </div>
  <div class="nav-section-label">Tools</div>
  <div class="nav-item active">
    <span class="nav-icon">🎙</span> Voiceover
  </div>
  <div class="nav-item">
    <span class="nav-icon">📝</span> Transcription
  </div>
  <div class="nav-item">
    <span class="nav-icon">🔄</span> Voice Changer
  </div>
  <div class="nav-item">
    <span class="nav-icon">🎬</span> Audio Dubber
  </div>
  <div class="nav-item">
    <span class="nav-icon">🧬</span> Voice Cloner
  </div>
  <div class="nav-item">
    <span class="nav-icon">🔇</span> Noise Remover
  </div>
  <div class="sb-footer">❓ &nbsp;Help &amp; Support</div>
</aside>

<!-- ── MAIN ───────────────────────────────────────────────────────── -->
<div class="main">

  <!-- Header -->
  <header class="top-hdr">
    <div class="proj-info">
      <div class="proj-title">Good Morning Design CR7</div>
      <span class="proj-badge">● Saved</span>
    </div>
    <div class="hdr-right">
      <button class="hdr-icon-btn" title="Notifications" aria-label="Notifications">🔔</button>
      <div class="user-chip">
        <div class="avatar">OA</div>
        <div class="user-meta">
          <span class="user-name">Ododo A.</span>
          <span class="user-email"><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="19767d767d76786f6e7c6b766a6c76597e74787075377a7674">[email&#160;protected]</a></span>
        </div>
      </div>
    </div>
  </header>

  <!-- Content -->
  <div class="content-area">

    <!-- Scripts panel -->
    <div class="scripts-panel">
      <div class="sp-hdr">
        <span class="sp-title">Scripts</span>
        <button class="add-script-btn" onclick="addScript()" title="Save current script text as a new entry">+ Save Script</button>
      </div>
      <div class="section-label">Saved scripts</div>
      <div class="scripts-list" id="scriptsList"></div>
    </div>

    <!-- Editor + timeline -->
    <div class="editor-panel">

      <!-- Toolbar -->
      <div class="ed-toolbar">
        <div class="tb-control">
          <select aria-label="Language">
            <option>English (SpeechBlocks)</option>
            <option>Spanish</option>
            <option>French</option>
          </select>
          <span style="color:var(--muted);font-size:10px">▾</span>
        </div>

        <div class="tb-control" style="padding:4px 10px;gap:8px">
          <div class="voice-avatar">👩</div>
          <select aria-label="Voice">
            <option>Achernar</option>
            <option>Puck</option>
            <option>Charon</option>
            <option>Kore</option>
            <option>Fenrir</option>
            <option>Aoede</option>
          </select>
          <span style="color:var(--muted);font-size:10px">▾</span>
        </div>

        <div class="tb-separator"></div>

        <div class="tb-control">
          <span class="tb-label">Speed</span>
          <div class="num-spinner">
            <input type="number" id="speedCtrl" value="1" min=".25" max="4" step=".25" aria-label="Speed">
            <div class="spin-col">
              <button class="spin-btn" onclick="stepNum('speedCtrl',.25,.25,4)">▲</button>
              <button class="spin-btn" onclick="stepNum('speedCtrl',-.25,.25,4)">▼</button>
            </div>
          </div>
          <span class="tb-label">x</span>
        </div>

        <div class="tb-control">
          <span class="tb-label">Pitch</span>
          <div class="num-spinner">
            <input type="number" id="pitchCtrl" value="0" min="-20" max="20" step="1" aria-label="Pitch">
            <div class="spin-col">
              <button class="spin-btn" onclick="stepNum('pitchCtrl',1,-20,20)">▲</button>
              <button class="spin-btn" onclick="stepNum('pitchCtrl',-1,-20,20)">▼</button>
            </div>
          </div>
          <span class="tb-label">%</span>
        </div>

        <button class="tb-plain-btn">Say as</button>
        <button class="tb-plain-btn">Pauses</button>

        <div class="ml-auto">
          <button class="generate-btn" onclick="saveVoiceover()">Generate &amp; Save</button>
        </div>
      </div>

      <!-- Script editor -->
      <div class="script-editor">
        <div class="field-label">Preview Your Script</div>
        <textarea class="script-textarea" rows="5" spellcheck="true">Hello my name is Patricia Espiritu I am 24 years old from the Philippines I have a bachelor's degree in business administration major in financial management and I have a TESOL and TEFL certification I am a bank employee for almost four years now where I was at customer service for two years I love teaching kids and I'm very excited to make a great impact in your company</textarea>
      </div>

      <div class="ed-footer">
        <div class="ed-action-group">
          <button class="ed-action-btn" title="Copy">⎘</button>
          <button class="ed-action-btn" title="Download">⬇</button>
          <button class="ed-action-btn" title="Delete">🗑</button>
        </div>
        <span class="ed-duration" id="edDuration">01:30</span>
      </div>

      <!-- Timeline -->
      <div class="tl-section">
        <div class="tl-topbar">

          <button class="tl-btn tl-btn-primary" onclick="showAddModal()">
            <span>＋</span> Add Track
          </button>
          <button class="tl-btn tl-btn-ghost" onclick="document.getElementById('fileInput').click()">
            📁 Upload Audio
          </button>
          <input type="file" id="fileInput" accept="audio/*" multiple>

          <div class="zoom-group">
            <button class="zoom-btn" onclick="zoom(-15)" title="Zoom out">−</button>
            <span class="zoom-label" id="zoomLbl">80px/s</span>
            <button class="zoom-btn" onclick="zoom(15)" title="Zoom in">+</button>
          </div>

          <div class="tl-transport">
            <div class="tl-timecode">
              <span id="tlCur">00:00</span>
              <span class="tl-timecode-sep">|</span>
              <span id="tlTotal">01:30</span>
            </div>
            <button class="tl-play-btn" id="masterPlay" onclick="togglePlay()" title="Play / Pause (Space)" aria-label="Play">
              <svg viewBox="0 0 10 12"><polygon points="0,0 10,6 0,12"/></svg>
            </button>
          </div>

          <div class="tl-action-group">
            <button class="tl-icon-btn" title="Split at playhead" onclick="splitSel()">✂</button>
            <button class="tl-icon-btn" title="Duplicate selected (Ctrl+D)" onclick="dupSel()">⎘</button>
            <button class="tl-icon-btn" title="Delete selected" onclick="delSel()">🗑</button>
          </div>

          <button class="tl-save-btn" onclick="saveVoiceover()">⬇ Export</button>
        </div>

        <div class="tl-body">
          <div class="tl-labels" id="tlLabels">
            <div class="tl-ruler-spacer"></div>
            <div class="tl-label-list" id="tlLabelList"></div>
          </div>
          <div class="tl-scroll" id="tlScroll">
            <div class="tl-inner" id="tlInner">
              <div class="ruler-row" id="rulerRow"><canvas id="rulerCvs"></canvas></div>
              <div id="tracksArea"></div>
              <div class="playhead" id="playhead"></div>
            </div>
          </div>
        </div>
      </div><!-- /tl-section -->

    </div><!-- /editor-panel -->
  </div><!-- /content-area -->
</div><!-- /main -->

<!-- ── CONTEXT MENU ───────────────────────────────────────────────── -->
<div class="ctx-menu" id="ctxMenu" role="menu">
  <div class="ctx-item" role="menuitem" onclick="ctxDo('rename')">✏️  Rename</div>
  <div class="ctx-item" role="menuitem" onclick="ctxDo('duplicate')">⎘  Duplicate</div>
  <div class="ctx-item" role="menuitem" onclick="ctxDo('split')">✂  Split at playhead</div>
  <div class="ctx-sep"></div>
  <div class="ctx-item" role="menuitem" onclick="ctxDo('mute')">🔇  Mute / Unmute</div>
  <div class="ctx-item" role="menuitem" onclick="ctxDo('solo')">🎧  Solo</div>
  <div class="ctx-sep"></div>
  <div class="ctx-item danger" role="menuitem" onclick="ctxDo('delete')">🗑  Delete clip</div>
</div>

<!-- ── ADD TRACK MODAL ────────────────────────────────────────────── -->
<div class="modal-overlay" id="addModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
  <div class="modal">
    <div class="modal-title" id="modalTitle">Add New Track</div>
    <div class="modal-field">
      <label class="modal-field-label" for="mName">Track Name</label>
      <input class="modal-input" id="mName" value="New Track" autocomplete="off">
    </div>
    <div class="modal-field">
      <label class="modal-field-label" for="mType">Track Type</label>
      <select class="modal-input" id="mType">
        <option value="voice">Voice</option>
        <option value="music">Music</option>
        <option value="sfx">SFX</option>
      </select>
    </div>
    <div class="modal-actions">
      <button class="modal-cancel" onclick="closeAddModal()">Cancel</button>
      <button class="modal-confirm" onclick="addTrackFromModal()">Add Track</button>
    </div>
  </div>
</div>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<!-- ── SCRIPT ─────────────────────────────────────────────────────── -->
<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script>
'use strict';

// ══════════════════════════════════════════════════
//  STATE
// ══════════════════════════════════════════════════
const S = {
  pxPerSec: 80,
  totalDur: 90,
  curTime: 0,
  isPlaying: false,
  playTimer: null,
  selClip: null,
  ctxClip: null,
  draggingLblId: null,
  audioCtx: null,
  activeSrc: [],       // Array<{ source: AudioBufferSourceNode, gain: GainNode }>
  selScript: null,

  tracks: [
    { id:'t1', name:'Voice Track',      type:'voice', muted:false, solo:false, volume:1,   audioBuffer:null, blobUrl:null, fileName:null, fileDur:null, mimeType:null },
    { id:'t2', name:'Background Music', type:'music', muted:false, solo:false, volume:0.7, audioBuffer:null, blobUrl:null, fileName:null, fileDur:null, mimeType:null },
  ],

  clips: [
    { id:'c1', trackId:'t1', start:0, duration:90, label:'Voice Over', type:'voice', volume:1,   muted:false, trimStart:0, trimEnd:0 },
    { id:'c2', trackId:'t2', start:0, duration:90, label:'Background', type:'music', volume:0.7, muted:false, trimStart:0, trimEnd:0 },
  ],

  scripts: [
    { id:'s1', name:'Title of Audio', duration:'01:30', audioBuffer:null, blobUrl:null, fileName:null },
  ],

  nextT: 3,
  nextC: 3,
  nextS: 2,
};

// ══════════════════════════════════════════════════
//  UTILITIES
// ══════════════════════════════════════════════════
const fmtTime = secs =>
  `${String(Math.floor(secs / 60)).padStart(2,'0')}:${String(Math.floor(secs % 60)).padStart(2,'0')}`;

let _toastTimer = null;
function toast(msg) {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.classList.add('show');
  clearTimeout(_toastTimer);
  _toastTimer = setTimeout(() => el.classList.remove('show'), 2800);
}

function stepNum(id, delta, min, max) {
  const el = document.getElementById(id);
  el.value = Math.min(max, Math.max(min, Math.round((+el.value + delta) * 100) / 100));
}

function getAudioCtx() {
  if (!S.audioCtx) {
    S.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
  }
  return S.audioCtx;
}

// ══════════════════════════════════════════════════
//  FILE / AUDIO LOADING
// ══════════════════════════════════════════════════
async function decodeAudio(file) {
  const ctx = getAudioCtx();
  const arrayBuffer = await file.arrayBuffer();
  return ctx.decodeAudioData(arrayBuffer);
}

/**
 * handleFileUpload — called by the file input change event.
 * Each dropped/selected file becomes its own new track.
 */
async function handleFileUpload(e) {
  const files = [...e.target.files];
  e.target.value = '';   // allow re-selecting the same file

  for (const file of files) {
    const blobUrl = URL.createObjectURL(file);
    let audioBuffer = null;
    let fileDur = 60;

    try {
      audioBuffer = await decodeAudio(file);
      fileDur = audioBuffer.duration;
    } catch (err) {
      console.warn('Audio decode failed:', err);
    }

    const trackId = 't' + S.nextT++;
    const clipId  = 'c' + S.nextC++;
    const type = /\.(mp3|aac|m4a|ogg)$/i.test(file.name) ? 'music' : 'voice';
    const name = file.name.replace(/\.[^.]+$/, '');

    S.tracks.push({
      id: trackId, name, type,
      muted: false, solo: false, volume: 1,
      audioBuffer, blobUrl, fileName: file.name,
      fileDur, mimeType: file.type,
    });
    S.clips.push({
      id: clipId, trackId, start: 0,
      duration: Math.round(fileDur * 10) / 10,
      label: file.name, type, volume: 1,
      muted: false, trimStart: 0, trimEnd: 0,
    });

    toast(`✓ Loaded: ${file.name} — ${fmtTime(fileDur)}`);
  }

  updateTotalDuration();
  render();
}

/**
 * loadIntoTrack — replace the audio file on an existing track.
 */
function loadIntoTrack(trackId) {
  const inp = document.createElement('input');
  inp.type = 'file';
  inp.accept = 'audio/*';

  inp.onchange = async () => {
    const file = inp.files[0];
    if (!file) return;

    const track = S.tracks.find(t => t.id === trackId);
    if (!track) return;

    if (track.blobUrl) URL.revokeObjectURL(track.blobUrl);

    const blobUrl = URL.createObjectURL(file);
    let audioBuffer = null;
    let fileDur = 60;

    try {
      audioBuffer = await decodeAudio(file);
      fileDur = audioBuffer.duration;
    } catch (err) {
      console.warn('Audio decode failed:', err);
    }

    Object.assign(track, {
      audioBuffer, blobUrl,
      fileName: file.name,
      fileDur, mimeType: file.type,
      name: file.name.replace(/\.[^.]+$/, ''),
    });

    const existing = S.clips.find(c => c.trackId === trackId);
    if (existing) {
      existing.duration = Math.round(fileDur * 10) / 10;
      existing.label = file.name;
    } else {
      S.clips.push({
        id: 'c' + S.nextC++, trackId, start: 0,
        duration: Math.round(fileDur * 10) / 10,
        label: file.name, type: track.type,
        volume: 1, muted: false, trimStart: 0, trimEnd: 0,
      });
    }

    updateTotalDuration();
    render();
    toast(`✓ ${file.name} → ${track.name}`);
  };

  inp.click();
}

// ══════════════════════════════════════════════════
//  PLAYBACK
// ══════════════════════════════════════════════════
function stopSources() {
  for (const { source } of S.activeSrc) {
    try { source.stop(); } catch (_) { /* already stopped */ }
  }
  S.activeSrc = [];
}

function startSources() {
  const ctx = getAudioCtx();
  stopSources();

  const anySolo = S.tracks.some(t => t.solo);

  for (const track of S.tracks) {
    if (track.muted || (anySolo && !track.solo) || !track.audioBuffer) continue;

    const clips = S.clips.filter(c => c.trackId === track.id && !c.muted);
    for (const clip of clips) {
      const offset = S.curTime - clip.start;
      if (offset >= clip.duration) continue;   // clip already passed

      const source = ctx.createBufferSource();
      const gain   = ctx.createGain();

      source.buffer = track.audioBuffer;
      source.connect(gain);
      gain.connect(ctx.destination);
      gain.gain.value = clip.volume * track.volume;

      const bufStart = Math.max(0, offset + clip.trimStart);
      const when     = ctx.currentTime + (offset < 0 ? -offset : 0);
      source.start(when, bufStart);

      S.activeSrc.push({ source, gain, trackId: track.id });
    }
  }

  if (!S.tracks.some(t => t.audioBuffer)) {
    toast('No audio loaded — click 📁 on a track label to load a file');
  }
}

function togglePlay() {
  S.isPlaying = !S.isPlaying;
  const btn = document.getElementById('masterPlay');

  if (S.isPlaying) {
    btn.innerHTML = `<svg viewBox="0 0 10 12" fill="currentColor" style="margin-left:0"><rect x="0" y="0" width="3" height="12"/><rect x="7" y="0" width="3" height="12"/></svg>`;
    btn.setAttribute('aria-label', 'Pause');
    startSources();

    S.playTimer = setInterval(() => {
      S.curTime = Math.round((S.curTime + 0.1) * 100) / 100;
      if (S.curTime >= S.totalDur) {
        S.curTime = 0;
        S.isPlaying = false;
        stopSources();
        clearInterval(S.playTimer);
        btn.innerHTML = `<svg viewBox="0 0 10 12"><polygon points="0,0 10,6 0,12"/></svg>`;
        btn.setAttribute('aria-label', 'Play');
      }
      updatePlayhead();
      updateTimecode();
    }, 100);

  } else {
    stopSources();
    clearInterval(S.playTimer);
    btn.innerHTML = `<svg viewBox="0 0 10 12"><polygon points="0,0 10,6 0,12"/></svg>`;
    btn.setAttribute('aria-label', 'Play');
  }
}

function seek(seconds) {
  const wasPlaying = S.isPlaying;

  if (wasPlaying) {
    stopSources();
    clearInterval(S.playTimer);
    S.isPlaying = false;
    const btn = document.getElementById('masterPlay');
    btn.innerHTML = `<svg viewBox="0 0 10 12"><polygon points="0,0 10,6 0,12"/></svg>`;
  }

  S.curTime = Math.max(0, Math.min(S.totalDur, seconds));
  updatePlayhead();
  updateTimecode();

  if (wasPlaying) {
    S.isPlaying = true;
    togglePlay();
  }
}

function updatePlayhead() {
  document.getElementById('playhead').style.left = (S.curTime * S.pxPerSec) + 'px';
}

function updateTimecode() {
  document.getElementById('tlCur').textContent   = fmtTime(S.curTime);
  document.getElementById('tlTotal').textContent = fmtTime(S.totalDur);
}

function updateTotalDuration() {
  S.totalDur = Math.max(
    S.clips.reduce((max, c) => Math.max(max, c.start + c.duration), 0) + 5,
    30
  );
  updateTimecode();
}

// ══════════════════════════════════════════════════
//  RULER
// ══════════════════════════════════════════════════
function renderRuler() {
  const canvas = document.getElementById('rulerCvs');
  const scroll = document.getElementById('tlScroll');
  const W = Math.max(scroll.clientWidth, S.totalDur * S.pxPerSec + 200);

  canvas.width  = W;
  canvas.height = 32;

  const ctx = canvas.getContext('2d');
  ctx.fillStyle = '#161410';
  ctx.fillRect(0, 0, W, 32);

  const step = S.pxPerSec >= 80 ? 5 : 10;

  // Major ticks
  for (let s = 0; s <= S.totalDur + step; s += step) {
    const x = s * S.pxPerSec;
    ctx.strokeStyle = '#3A3632';
    ctx.lineWidth = 1;
    ctx.beginPath(); ctx.moveTo(x, 18); ctx.lineTo(x, 32); ctx.stroke();
    ctx.fillStyle = '#605855';
    ctx.font = '9.5px DM Sans, sans-serif';
    ctx.fillText(fmtTime(s), x + 4, 12);
  }

  // Minor ticks
  const sub = step / 5;
  for (let s = 0; s <= S.totalDur; s += sub) {
    if (s % step === 0) continue;
    const x = s * S.pxPerSec;
    ctx.strokeStyle = '#2A2622';
    ctx.lineWidth = 1;
    ctx.beginPath(); ctx.moveTo(x, 24); ctx.lineTo(x, 32); ctx.stroke();
  }

  document.getElementById('tlInner').style.width = W + 'px';
  document.getElementById('zoomLbl').textContent = S.pxPerSec + 'px/s';
}

// ══════════════════════════════════════════════════
//  WAVEFORM DRAWING
// ══════════════════════════════════════════════════
function drawWaveform(canvas, track, clip) {
  const W = canvas.width;
  const H = canvas.height;
  const ctx = canvas.getContext('2d');
  ctx.clearRect(0, 0, W, H);

  const color = clip.type === 'voice'
    ? 'rgba(255,195,170,.7)'
    : clip.type === 'music'
      ? 'rgba(140,190,255,.7)'
      : 'rgba(140,255,185,.7)';

  if (track && track.audioBuffer) {
    const buf  = track.audioBuffer;
    const data = buf.getChannelData(0);
    const spp  = buf.sampleRate * (clip.duration / W);

    for (let x = 0; x < W; x++) {
      const start = Math.floor(x * spp);
      const end   = Math.floor((x + 1) * spp);
      let max = 0;
      for (let i = start; i < end && i < data.length; i++) {
        max = Math.max(max, Math.abs(data[i]));
      }
      const h = Math.max(1, max * H);
      ctx.fillStyle = color;
      ctx.fillRect(x, (H - h) / 2, 1, h);
    }
  } else {
    // Placeholder bars (seeded per-clip so they're stable across renders)
    const barCount = Math.floor(W / 3);
    // Simple deterministic seed using clip id
    let seed = clip.id.split('').reduce((a, c) => a + c.charCodeAt(0), 0);
    const rand = () => { seed = (seed * 1664525 + 1013904223) & 0xffffffff; return (seed >>> 0) / 0xffffffff; };
    for (let i = 0; i < barCount; i++) {
      const h = (0.15 + rand() * 0.7) * H;
      ctx.fillStyle = color;
      ctx.fillRect(i * 3, (H - h) / 2, 2, h);
    }
  }
}

// ══════════════════════════════════════════════════
//  LABEL COLUMN (with drag-to-reorder)
// ══════════════════════════════════════════════════
function renderLabels() {
  const list = document.getElementById('tlLabelList');
  list.innerHTML = '';

  for (const track of S.tracks) {
    const row = document.createElement('div');
    row.className = 'tl-lbl-row';
    row.dataset.tid = track.id;
    row.title = track.fileName
      ? `${track.fileName} · ${fmtTime(track.fileDur || 0)}`
      : 'No audio loaded — click 📁 to load';

    // Grip
    const grip = document.createElement('span');
    grip.className = 'drag-grip';
    grip.textContent = '⠿';
    row.appendChild(grip);

    // Name + file info
    const nameWrap = document.createElement('div');
    nameWrap.className = 'lbl-name-wrap';

    const nameEl = document.createElement('span');
    nameEl.className = 'lbl-name' + (track.fileName ? ' has-file' : '');
    nameEl.textContent = track.name;
    nameWrap.appendChild(nameEl);

    if (track.fileName) {
      const fileInfo = document.createElement('span');
      fileInfo.className = 'lbl-file-info';
      fileInfo.textContent = `${track.fileName} · ${fmtTime(track.fileDur || 0)}`;
      nameWrap.appendChild(fileInfo);
    }

    row.appendChild(nameWrap);

    // Buttons
    const btnGroup = document.createElement('div');
    btnGroup.className = 'lbl-btn-group';

    const makeBtn = (text, title, extraClass, handler) => {
      const btn = document.createElement('button');
      btn.className = 'lbl-btn' + (extraClass ? ` ${extraClass}` : '');
      btn.textContent = text;
      btn.title = title;
      btn.addEventListener('click', e => { e.stopPropagation(); handler(); });
      return btn;
    };

    btnGroup.appendChild(makeBtn('📁', 'Load audio file', '', () => loadIntoTrack(track.id)));
    btnGroup.appendChild(makeBtn(
      track.muted ? '🔇' : 'M',
      'Mute',
      'mute-btn' + (track.muted ? ' active' : ''),
      () => { track.muted = !track.muted; render(); }
    ));
    btnGroup.appendChild(makeBtn('S', 'Solo', track.solo ? 'active' : '', () => { track.solo = !track.solo; render(); }));
    btnGroup.appendChild(makeBtn('✕', 'Remove track', '', () => removeTrack(track.id)));

    row.appendChild(btnGroup);

    // Volume strip
    const volStrip = document.createElement('div');
    volStrip.className = 'vol-strip';

    const slider = document.createElement('input');
    slider.type = 'range'; slider.min = 0; slider.max = 1; slider.step = 0.01;
    slider.value = track.volume;
    slider.className = 'vol-slider';
    slider.setAttribute('aria-label', `${track.name} volume`);

    const pctLabel = document.createElement('span');
    pctLabel.className = 'vol-pct';
    pctLabel.textContent = Math.round(track.volume * 100) + '%';

    slider.addEventListener('input', () => {
      track.volume = +slider.value;
      pctLabel.textContent = Math.round(track.volume * 100) + '%';
      // Sync clip volumes in state
      S.clips.filter(c => c.trackId === track.id).forEach(c => c.volume = track.volume);
      // Apply immediately to any live Web Audio gain nodes for this track
      if (S.isPlaying) {
        S.activeSrc.forEach(({ source, gain, trackId }) => {
          if (trackId === track.id && gain) gain.gain.value = track.volume;
        });
      }
    });

    volStrip.appendChild(slider);
    volStrip.appendChild(pctLabel);
    row.appendChild(volStrip);

    // ── Drag-to-reorder ──
    row.draggable = true;

    row.addEventListener('dragstart', e => {
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('lbl-reorder', track.id);
      S.draggingLblId = track.id;
      setTimeout(() => row.classList.add('lbl-dragging'), 0);
    });

    row.addEventListener('dragend', () => {
      row.classList.remove('lbl-dragging');
      list.querySelectorAll('.lbl-drop-above, .lbl-drop-below')
          .forEach(el => el.classList.remove('lbl-drop-above', 'lbl-drop-below'));
      S.draggingLblId = null;
    });

    row.addEventListener('dragover', e => {
      const fromId = S.draggingLblId;
      if (!fromId || fromId === track.id) return;
      e.preventDefault();
      list.querySelectorAll('.lbl-drop-above, .lbl-drop-below')
          .forEach(el => el.classList.remove('lbl-drop-above', 'lbl-drop-below'));
      const fromIdx = S.tracks.findIndex(t => t.id === fromId);
      const toIdx   = S.tracks.findIndex(t => t.id === track.id);
      row.classList.add(fromIdx < toIdx ? 'lbl-drop-below' : 'lbl-drop-above');
    });

    row.addEventListener('dragleave', () =>
      row.classList.remove('lbl-drop-above', 'lbl-drop-below'));

    row.addEventListener('drop', e => {
      e.preventDefault();
      const fromId = e.dataTransfer.getData('lbl-reorder');
      if (!fromId || fromId === track.id) return;
      reorderTrack(fromId, track.id);
    });

    list.appendChild(row);
  }
}

// ══════════════════════════════════════════════════
//  TRACK ROWS + CLIPS
// ══════════════════════════════════════════════════
function renderTracks() {
  const area = document.getElementById('tracksArea');
  area.innerHTML = '';

  for (const track of S.tracks) {
    const row = document.createElement('div');
    row.className = 'track-row' + (track.muted ? ' muted-row' : '');
    row.dataset.tid = track.id;

    const hint = document.createElement('div');
    hint.className = 'empty-hint';
    if (!track.fileName) {
      hint.innerHTML = '<span style="opacity:.4">⬇</span> Drop audio file here or click 📁';
    }
    row.appendChild(hint);

    // File drop onto track row
    row.addEventListener('dragover', e => {
      const types = [...e.dataTransfer.types];
      if (types.includes('Files') || types.includes('text/plain')) {
        e.preventDefault();
        row.classList.add('row-dragover');
      }
    });
    row.addEventListener('dragleave', () => row.classList.remove('row-dragover'));

    row.addEventListener('drop', async e => {
      e.preventDefault();
      row.classList.remove('row-dragover');

      if (e.dataTransfer.files.length) {
        const file = e.dataTransfer.files[0];
        if (!file.type.startsWith('audio/')) return;

        const blobUrl = URL.createObjectURL(file);
        let audioBuffer = null, fileDur = 60;
        try { audioBuffer = await decodeAudio(file); fileDur = audioBuffer.duration; } catch (_) {}

        const t = S.tracks.find(t => t.id === track.id);
        if (t.blobUrl) URL.revokeObjectURL(t.blobUrl);
        Object.assign(t, {
          audioBuffer, blobUrl, fileName: file.name,
          fileDur, mimeType: file.type,
          name: file.name.replace(/\.[^.]+$/, ''),
        });

        const ex = S.clips.find(c => c.trackId === track.id);
        if (ex) { ex.duration = Math.round(fileDur * 10) / 10; ex.label = file.name; }
        else S.clips.push({
          id: 'c' + S.nextC++, trackId: track.id, start: 0,
          duration: Math.round(fileDur * 10) / 10,
          label: file.name, type: track.type,
          volume: 1, muted: false, trimStart: 0, trimEnd: 0,
        });

        updateTotalDuration(); render(); toast(`✓ ${file.name}`);
        return;
      }

      const scriptId = e.dataTransfer.getData('text/plain');
      if (scriptId) addClipFromScript(scriptId, track.id, e);
    });

    // Click on empty space = seek
    row.addEventListener('click', e => {
      if (e.target !== row && e.target !== hint) return;
      const rect   = row.getBoundingClientRect();
      const scroll = document.getElementById('tlScroll');
      seek((e.clientX - rect.left + scroll.scrollLeft) / S.pxPerSec);
    });

    area.appendChild(row);
  }

  // Render all clips on top
  S.clips.forEach(renderClip);
}

// ── Single clip ─────────────────────────────────
function renderClip(clip) {
  const row = document.querySelector(`.track-row[data-tid="${clip.trackId}"]`);
  if (!row) return;

  // Remove stale element if present
  row.querySelector(`[data-cid="${clip.id}"]`)?.remove();

  const track = S.tracks.find(t => t.id === clip.trackId);
  const w = Math.max(20, clip.duration * S.pxPerSec);

  const el = document.createElement('div');
  el.className = `clip ${clip.type}${clip.muted ? ' muted-clip' : ''}${clip.id === S.selClip ? ' sel' : ''}`;
  el.dataset.cid = clip.id;
  el.style.left  = (clip.start * S.pxPerSec) + 'px';
  el.style.width = w + 'px';

  // Waveform canvas
  const cvs = document.createElement('canvas');
  cvs.className = 'clip-wf';
  cvs.width  = Math.max(10, w);
  cvs.height = 50;
  el.appendChild(cvs);

  // Left resize handle
  const rsL = document.createElement('div');
  rsL.className = 'rs-handle rs-l';
  el.appendChild(rsL);

  // Body (icon + label + duration)
  const body = document.createElement('div');
  body.className = 'clip-body';

  const icon = document.createElement('span');
  icon.className = 'clip-icon';
  icon.textContent = track?.fileName ? '🎵' : '🎙';
  body.appendChild(icon);

  const lbl = document.createElement('span');
  lbl.className = 'clip-label';
  lbl.textContent = clip.label;
  body.appendChild(lbl);

  const dur = document.createElement('span');
  dur.className = 'clip-duration';
  dur.textContent = fmtTime(clip.duration);
  body.appendChild(dur);

  el.appendChild(body);

  // Right resize handle
  const rsR = document.createElement('div');
  rsR.className = 'rs-handle rs-r';
  el.appendChild(rsR);

  row.appendChild(el);

  // Draw waveform after layout
  requestAnimationFrame(() => drawWaveform(cvs, track, clip));

  // Events
  el.addEventListener('click', e => { e.stopPropagation(); selectClip(clip.id); });
  el.addEventListener('contextmenu', e => {
    e.preventDefault();
    S.ctxClip = clip.id;
    showContextMenu(e.clientX, e.clientY);
  });
  makeDraggable(el, clip);
  makeResizable(rsL, el, clip, 'l');
  makeResizable(rsR, el, clip, 'r');
}

function selectClip(id) {
  S.selClip = id;
  document.querySelectorAll('.clip').forEach(c => c.classList.toggle('sel', c.dataset.cid === id));
}

// ── Clip drag ───────────────────────────────────
function makeDraggable(el, clip) {
  let startX, startLeft, dragging = false;

  el.addEventListener('mousedown', e => {
    if (e.target.classList.contains('rs-handle')) return;
    e.preventDefault();
    dragging = true;
    startX    = e.clientX;
    startLeft = clip.start * S.pxPerSec;
    el.style.cursor = 'grabbing';
    selectClip(clip.id);

    const onMove = me => {
      if (!dragging) return;
      el.style.left = Math.max(0, startLeft + (me.clientX - startX)) + 'px';
    };
    const onUp = me => {
      dragging = false;
      el.style.cursor = 'grab';
      clip.start = Math.max(0, Math.round(((startLeft + (me.clientX - startX)) / S.pxPerSec) * 10) / 10);
      el.style.left = (clip.start * S.pxPerSec) + 'px';
      updateTotalDuration();
      renderRuler();
      document.removeEventListener('mousemove', onMove);
      document.removeEventListener('mouseup', onUp);
    };
    document.addEventListener('mousemove', onMove);
    document.addEventListener('mouseup', onUp);
  });
}

// ── Clip resize ─────────────────────────────────
function makeResizable(handle, el, clip, side) {
  handle.addEventListener('mousedown', e => {
    e.preventDefault();
    e.stopPropagation();

    const startX   = e.clientX;
    const origStart = clip.start;
    const origDur   = clip.duration;

    const onMove = me => {
      const dx = (me.clientX - startX) / S.pxPerSec;
      if (side === 'r') {
        clip.duration = Math.max(1, Math.round((origDur + dx) * 10) / 10);
        el.style.width = Math.max(20, clip.duration * S.pxPerSec) + 'px';
      } else {
        const shift = Math.min(dx, origDur - 1);
        clip.start    = Math.max(0, Math.round((origStart + shift) * 10) / 10);
        clip.duration = Math.max(1, Math.round((origDur - shift) * 10) / 10);
        el.style.left  = (clip.start * S.pxPerSec) + 'px';
        el.style.width = Math.max(20, clip.duration * S.pxPerSec) + 'px';
      }
      el.querySelector('.clip-duration').textContent = fmtTime(clip.duration);
    };

    const onUp = () => {
      updateTotalDuration();
      renderRuler();
      const cv = el.querySelector('.clip-wf');
      if (cv) {
        cv.width = Math.max(10, clip.duration * S.pxPerSec);
        drawWaveform(cv, S.tracks.find(t => t.id === clip.trackId), clip);
      }
      document.removeEventListener('mousemove', onMove);
      document.removeEventListener('mouseup', onUp);
    };

    document.addEventListener('mousemove', onMove);
    document.addEventListener('mouseup', onUp);
  });
}

// ══════════════════════════════════════════════════
//  TRACK MANAGEMENT
// ══════════════════════════════════════════════════
function reorderTrack(fromId, toId) {
  const fromIdx = S.tracks.findIndex(t => t.id === fromId);
  const toIdx   = S.tracks.findIndex(t => t.id === toId);
  if (fromIdx < 0 || toIdx < 0) return;
  const [moved] = S.tracks.splice(fromIdx, 1);
  S.tracks.splice(toIdx, 0, moved);
  render();
  toast('Track reordered');
}

function removeTrack(id) {
  S.tracks = S.tracks.filter(t => t.id !== id);
  S.clips  = S.clips.filter(c => c.trackId !== id);
  // Stop any sources that belong to this track so deleted audio doesn't keep playing
  S.activeSrc
    .filter(s => s.trackId === id)
    .forEach(({ source }) => { try { source.stop(); } catch (_) {} });
  S.activeSrc = S.activeSrc.filter(s => s.trackId !== id);
  render();
  toast('Track removed');
}

// ══════════════════════════════════════════════════
//  ZOOM
// ══════════════════════════════════════════════════
function zoom(delta) {
  S.pxPerSec = Math.min(300, Math.max(20, S.pxPerSec + delta));
  render();
}

// ══════════════════════════════════════════════════
//  CONTEXT MENU
// ══════════════════════════════════════════════════
function showContextMenu(x, y) {
  const menu = document.getElementById('ctxMenu');
  // Keep menu inside viewport
  const vw = window.innerWidth, vh = window.innerHeight;
  menu.style.left = (x + 180 > vw ? vw - 185 : x) + 'px';
  menu.style.top  = (y + 200 > vh ? vh - 205 : y) + 'px';
  menu.classList.add('show');
}

function hideContextMenu() {
  document.getElementById('ctxMenu').classList.remove('show');
}

function ctxDo(action) {
  const clip = S.clips.find(c => c.id === S.ctxClip);
  hideContextMenu();
  if (!clip) return;

  switch (action) {
    case 'delete':
      S.clips = S.clips.filter(c => c.id !== clip.id);
      render();
      toast('Clip deleted');
      break;

    case 'duplicate':
      S.clips.push({ ...clip, id: 'c' + S.nextC++, start: clip.start + clip.duration });
      updateTotalDuration();
      render();
      toast('Duplicated');
      break;

    case 'mute':
      clip.muted = !clip.muted;
      render();
      toast(clip.muted ? 'Clip muted' : 'Clip unmuted');
      break;

    case 'solo': {
      const track = S.tracks.find(t => t.id === clip.trackId);
      if (track) { track.solo = !track.solo; render(); toast(track.solo ? 'Solo on' : 'Solo off'); }
      break;
    }

    case 'rename': {
      const name = prompt('Rename clip:', clip.label);
      if (name) { clip.label = name; render(); toast('Renamed'); }
      break;
    }

    case 'split': {
      const at = S.curTime;
      if (at <= clip.start || at >= clip.start + clip.duration) {
        toast('Playhead must be within the clip to split');
        return;
      }
      const leftDur  = at - clip.start;
      const rightDur = clip.duration - leftDur;
      clip.duration = leftDur;
      S.clips.push({ ...clip, id: 'c' + S.nextC++, start: at, duration: rightDur });
      render();
      toast(`Split at ${fmtTime(at)}`);
      break;
    }
  }
}

function splitSel()  { S.selClip ? (S.ctxClip = S.selClip, ctxDo('split'))     : toast('Select a clip first'); }
function dupSel()    { S.selClip ? (S.ctxClip = S.selClip, ctxDo('duplicate')) : toast('Select a clip first'); }
function delSel()    { S.selClip ? (S.ctxClip = S.selClip, ctxDo('delete'))    : toast('Select a clip first'); }

// ══════════════════════════════════════════════════
//  ADD TRACK MODAL
// ══════════════════════════════════════════════════
function showAddModal() {
  document.getElementById('addModal').classList.add('show');
  document.getElementById('mName').focus();
}

function closeAddModal() {
  document.getElementById('addModal').classList.remove('show');
}

function addTrackFromModal() {
  const name = document.getElementById('mName').value.trim() || 'Track';
  const type = document.getElementById('mType').value;
  S.tracks.push({
    id: 't' + S.nextT++, name, type,
    muted: false, solo: false, volume: 1,
    audioBuffer: null, blobUrl: null, fileName: null,
    fileDur: null, mimeType: null,
  });
  closeAddModal();
  render();
  toast(`Track added: ${name}`);
}

// ══════════════════════════════════════════════════
//  SCRIPTS PANEL
// ══════════════════════════════════════════════════

// Track which script is currently previewing
let _playingScriptId = null;

function renderScripts() {
  const list = document.getElementById('scriptsList');
  list.innerHTML = '';

  if (!S.scripts.length) {
    list.innerHTML = `<div style="padding:20px 14px;text-align:center;color:var(--muted);font-size:12px;line-height:1.6">
      No scripts yet.<br>Click <strong>+ New Script</strong> to create one,<br>or load an audio file below.
    </div>`;
    return;
  }

  for (const sc of S.scripts) {
    const isPlaying = _playingScriptId === sc.id;
    const card = document.createElement('div');
    card.className = 'sc-card' + (sc.id === S.selScript ? ' active' : '');
    card.draggable = true;

    card.innerHTML = `
      <div class="sc-top">
        <button class="sc-play-btn ${isPlaying ? 'sc-playing' : ''}" data-action="play" aria-label="${isPlaying ? 'Stop' : 'Preview'} ${escapeHtml(sc.name)}">
          ${isPlaying
            ? `<svg viewBox="0 0 10 12" fill="currentColor"><rect x="0" y="0" width="3" height="12"/><rect x="7" y="0" width="3" height="12"/></svg>`
            : `<svg viewBox="0 0 10 12"><polygon points="0,0 10,6 0,12"/></svg>`}
        </button>
        <span class="sc-name" data-action="rename" title="Click to rename">${escapeHtml(sc.name)}</span>
        <span class="sc-dur">${sc.duration}</span>
      </div>
      ${sc.fileName ? `<div class="file-badge">🎵 ${escapeHtml(sc.fileName)}</div>` : '<div class="sc-no-audio">No audio — load a file or generate</div>'}
      <div class="wf-mini"><canvas id="sc_wf_${sc.id}" height="20"></canvas></div>
      <div class="sc-actions">
        <div class="sc-icon-group">
          <button class="sc-icon-btn" data-action="load"     title="Load audio file">📁</button>
          <button class="sc-icon-btn" data-action="download" title="Download audio" ${!sc.blobUrl ? 'disabled' : ''}>⬇</button>
          <button class="sc-icon-btn sc-icon-danger" data-action="delete" title="Delete script">🗑</button>
        </div>
        <button class="add-to-tl-btn" data-action="add-tl">+ Timeline</button>
      </div>`;

    // Single delegated click handler on the card
    card.addEventListener('click', e => {
      const action = e.target.closest('[data-action]')?.dataset.action;
      e.stopPropagation();

      switch (action) {
        case 'play':     toggleScriptPreview(sc.id); break;
        case 'rename':   renameScript(sc.id); break;
        case 'load':     loadScriptAudio(sc.id); break;
        case 'download': downloadScriptAudio(sc.id); break;
        case 'delete':   deleteScript(sc.id); break;
        case 'add-tl':   addScriptToTimeline(sc.id); break;
        default:
          // Click on card body = select
          S.selScript = sc.id;
          renderScripts();
      }
    });

    card.addEventListener('dragstart', e => {
      e.dataTransfer.setData('text/plain', sc.id);
    });

    list.appendChild(card);

    requestAnimationFrame(() => {
      const cv = document.getElementById('sc_wf_' + sc.id);
      if (!cv) return;
      cv.width = cv.parentElement.clientWidth || 240;
      drawWaveform(
        cv,
        sc.audioBuffer ? { audioBuffer: sc.audioBuffer } : null,
        { type: 'voice', duration: sc.audioBuffer ? sc.audioBuffer.duration : 30, trimStart: 0, id: sc.id }
      );
    });
  }
}

function escapeHtml(str) {
  return String(str).replace(/[&<>"']/g, c =>
    ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c])
  );
}

/**
 * addScript — captures the current textarea text and creates a new script entry.
 * If no text is present it still creates a blank script so the user can load audio manually.
 */
function addScript() {
  const textarea = document.querySelector('.script-textarea');
  const text = textarea ? textarea.value.trim() : '';
  const id   = 's' + S.nextS++;
  const name = text
    ? (text.length > 40 ? text.slice(0, 40).trimEnd() + '…' : text)
    : 'Script ' + id;

  S.scripts.push({
    id,
    name,
    text,                 // store the raw text for reference
    duration: '00:30',
    audioBuffer: null,
    blobUrl: null,
    fileName: null,
  });

  S.selScript = id;
  renderScripts();
  toast('Script created — load an audio file with 📁');
}

function renameScript(id) {
  const sc = S.scripts.find(s => s.id === id);
  if (!sc) return;
  const name = prompt('Rename script:', sc.name);
  if (name && name.trim()) {
    sc.name = name.trim();
    renderScripts();
  }
}

async function loadScriptAudio(id) {
  const inp = document.createElement('input');
  inp.type = 'file';
  inp.accept = 'audio/*';
  inp.onchange = async () => {
    const file = inp.files[0];
    if (!file) return;
    const sc = S.scripts.find(s => s.id === id);
    if (!sc) return;
    if (sc.blobUrl) URL.revokeObjectURL(sc.blobUrl);
    sc.blobUrl   = URL.createObjectURL(file);
    sc.fileName  = file.name;
    let buf = null, dur = 30;
    try { buf = await decodeAudio(file); dur = buf.duration; } catch (_) {}
    sc.audioBuffer = buf;
    sc.duration    = fmtTime(dur);
    // Auto-name from filename if the script still has a generic name
    if (!sc.text) sc.name = file.name.replace(/\.[^.]+$/, '');
    renderScripts();
    toast(`✓ Audio loaded: ${file.name} — ${fmtTime(dur)}`);
  };
  inp.click();
}

function downloadScriptAudio(id) {
  const sc = S.scripts.find(s => s.id === id);
  if (!sc || !sc.blobUrl) { toast('No audio to download'); return; }
  const a = document.createElement('a');
  a.href     = sc.blobUrl;
  a.download = sc.fileName || (sc.name + '.audio');
  a.click();
}

/**
 * toggleScriptPreview — play or stop a script's audio preview.
 * Stops timeline playback while previewing.
 */
function toggleScriptPreview(id) {
  const sc = S.scripts.find(s => s.id === id);
  if (!sc) return;

  // If this script is already previewing, stop it
  if (_playingScriptId === id) {
    stopSources();
    _playingScriptId = null;
    renderScripts();
    return;
  }

  if (!sc.audioBuffer) {
    toast('No audio — click 📁 to load an audio file for this script');
    return;
  }

  // Stop any timeline playback first
  if (S.isPlaying) togglePlay();

  const ctx = getAudioCtx();
  stopSources();

  const src = ctx.createBufferSource();
  src.buffer = sc.audioBuffer;
  src.connect(ctx.destination);
  src.onended = () => {
    _playingScriptId = null;
    renderScripts();
  };
  src.start();

  S.activeSrc      = [{ source: src, gain: null, trackId: null }];
  _playingScriptId = id;
  renderScripts();
  toast(`▶ ${sc.name}`);
}

function deleteScript(id) {
  // Stop preview if this script is playing
  if (_playingScriptId === id) {
    stopSources();
    _playingScriptId = null;
  }
  S.scripts = S.scripts.filter(s => s.id !== id);
  if (S.selScript === id) S.selScript = S.scripts[0]?.id ?? null;
  renderScripts();
  toast('Script deleted');
}

function addScriptToTimeline(id) {
  const sc = S.scripts.find(s => s.id === id);
  if (!sc) return;

  const [m, s] = (sc.duration || '00:30').split(':').map(Number);
  const dur = m * 60 + s;

  // Always create a dedicated track for this script so it carries its own audio
  const trackId = 't' + S.nextT++;
  S.tracks.push({
    id: trackId,
    name: sc.name,
    type: 'voice',
    muted: false, solo: false, volume: 1,
    audioBuffer: sc.audioBuffer || null,
    blobUrl: sc.blobUrl || null,
    fileName: sc.fileName || null,
    fileDur: sc.audioBuffer ? sc.audioBuffer.duration : dur,
    mimeType: null,
  });

  // Place clip after the last clip on this new track (i.e. at 0 since it's fresh)
  S.clips.push({
    id: 'c' + S.nextC++,
    trackId,
    start: 0,
    duration: dur,
    label: sc.name,
    type: 'voice',
    volume: 1,
    muted: false,
    trimStart: 0,
    trimEnd: 0,
  });

  updateTotalDuration();
  render();
  toast(`${sc.name} → timeline`);
}

function addClipFromScript(scriptId, targetTrackId, dropEvent) {
  const sc = S.scripts.find(s => s.id === scriptId);
  if (!sc) return;

  const row = document.querySelector(`.track-row[data-tid="${targetTrackId}"]`);
  if (!row) return;

  const rect  = row.getBoundingClientRect();
  const x     = dropEvent.clientX - rect.left + document.getElementById('tlScroll').scrollLeft;
  const start = Math.max(0, Math.round((x / S.pxPerSec) * 10) / 10);
  const [m, s] = (sc.duration || '00:30').split(':').map(Number);
  const dur = m * 60 + s;

  // Create a dedicated track so this script's audio plays back correctly
  const trackId = 't' + S.nextT++;
  S.tracks.push({
    id: trackId,
    name: sc.name,
    type: 'voice',
    muted: false, solo: false, volume: 1,
    audioBuffer: sc.audioBuffer || null,
    blobUrl: sc.blobUrl || null,
    fileName: sc.fileName || null,
    fileDur: sc.audioBuffer ? sc.audioBuffer.duration : dur,
    mimeType: null,
  });

  S.clips.push({
    id: 'c' + S.nextC++,
    trackId,
    start,
    duration: dur,
    label: sc.name,
    type: 'voice',
    volume: 1,
    muted: false,
    trimStart: 0,
    trimEnd: 0,
  });

  updateTotalDuration();
  render();
  toast(`${sc.name} → timeline`);
}

// ══════════════════════════════════════════════════
//  MISC SAVE
// ══════════════════════════════════════════════════
function saveVoiceover() { toast('✓ Voiceover saved!'); }

// ══════════════════════════════════════════════════
//  FULL RENDER
// ══════════════════════════════════════════════════
function render() {
  renderRuler();
  renderLabels();
  renderTracks();
  updatePlayhead();
  updateTimecode();
}

// ══════════════════════════════════════════════════
//  EVENTS & INIT
// ══════════════════════════════════════════════════

// Sync label column scroll with track scroll
document.getElementById('tlScroll').addEventListener('scroll', e => {
  document.getElementById('tlLabelList').style.transform = `translateY(-${e.target.scrollTop}px)`;
});

// Ruler click → seek
document.getElementById('rulerRow').addEventListener('click', e => {
  const rect = document.getElementById('rulerRow').getBoundingClientRect();
  seek((e.clientX - rect.left + document.getElementById('tlScroll').scrollLeft) / S.pxPerSec);
});

// Global file drop → new tracks
document.addEventListener('dragover', e => {
  if ([...e.dataTransfer.types].includes('Files')) e.preventDefault();
});
document.addEventListener('drop', async e => {
  const files = [...e.dataTransfer.files];
  if (!files.length) return;
  if (!files.every(f => f.type.startsWith('audio/'))) return;
  e.preventDefault();
  await handleFileUpload({ target: { files: e.dataTransfer.files, value: '' } });
});

// Close context menu on outside click
document.addEventListener('click', e => {
  if (!e.target.closest('#ctxMenu')) hideContextMenu();
});

// Keyboard shortcuts
document.addEventListener('keydown', e => {
  if (e.target.tagName === 'TEXTAREA' || e.target.tagName === 'INPUT') return;

  if (e.code === 'Space') { e.preventDefault(); togglePlay(); }
  if ((e.code === 'Delete' || e.code === 'Backspace') && S.selClip) delSel();
  if (e.code === 'KeyD' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); dupSel(); }
  if (e.code === 'Equal'   && (e.ctrlKey || e.metaKey)) { e.preventDefault(); zoom(15); }
  if (e.code === 'Minus'   && (e.ctrlKey || e.metaKey)) { e.preventDefault(); zoom(-15); }
  if (e.code === 'Escape') hideContextMenu();
});

// Ctrl+Scroll zoom on timeline
document.getElementById('tlScroll').addEventListener('wheel', e => {
  if (e.ctrlKey || e.metaKey) { e.preventDefault(); zoom(e.deltaY > 0 ? -10 : 10); }
}, { passive: false });

// Modal keyboard accessibility
document.getElementById('addModal').addEventListener('keydown', e => {
  if (e.code === 'Escape') closeAddModal();
  if (e.code === 'Enter' && e.target.tagName !== 'BUTTON') addTrackFromModal();
});

// File input
document.getElementById('fileInput').addEventListener('change', handleFileUpload);

// Window resize
window.addEventListener('resize', render);

// Boot
renderScripts();
render();
</script>
</body>
</html>