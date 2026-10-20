# Deploy to Netlify (Photon v1.0.1)

Option A — Git (recommended): push this folder to GitHub, then Netlify > Add new site > Import from Git.
Build settings are read from netlify.toml (build: npm run build, publish: dist). Supabase env vars are already set there.

Option B — Drag & drop: run `npm install && npm run build` locally, then drag the generated `dist` folder into Netlify > Sites > Deploy manually.
(Dragging this source zip directly will NOT work; Netlify drag-and-drop does not run builds.)

Supabase: Authentication > URL Configuration > add your Netlify URL as Site URL.

v1.0.7: no database change needed. If you skipped v1.0.5, run src/db/update-v1.0.5.sql once in the Supabase SQL Editor.
