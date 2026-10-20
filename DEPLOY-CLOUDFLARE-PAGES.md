# Cloudflare Pages deployment

This repository contains a React + TypeScript + Vite single-page application. Deploy it with **Cloudflare Pages** using Git integration.

## Build settings

- Production branch: `main`
- Framework preset: `Vite` (or `None`)
- Build command: `npm run build`
- Build output directory: `dist`
- Node.js version: `22` (set `NODE_VERSION=22` if the dashboard needs an explicit environment variable)

Cloudflare Pages builds from the repository root, so no root directory override is required. This is a single-page app and does not need a custom `public/_redirects` rule: Cloudflare Pages automatically serves `index.html` for routes that do not match a static file when there is no top-level `404.html`. Avoid adding a catch-all redirect to `/index.html`, which can trigger an infinite-loop validation error in some deployment paths. The `public/_headers` file applies long-lived caching to Vite's fingerprinted assets.

## Environment variables

In **Settings → Environment variables**, configure the public browser-side Supabase values if they are not already supplied by the project configuration:

- `VITE_SUPABASE_URL`
- `VITE_SUPABASE_ANON_KEY` (or the Supabase publishable key)

These are public client values, not service-role secrets. Never put a Supabase service-role key or other private credentials in a `VITE_*` variable or commit them to Git.

## Deploy

1. Open the Cloudflare dashboard → **Workers & Pages** → **Create application** → **Pages**.
2. Import `sayem0086372/Photon-Physics-Academy`.
3. Apply the build settings above and add any required environment variables for both Production and Preview.
4. Start the first deployment. Subsequent pushes to `main` trigger new deployments.

## Build locally

Use Node.js 22, then run `npm install` and `npm run build`. The generated static site is in `dist/`.