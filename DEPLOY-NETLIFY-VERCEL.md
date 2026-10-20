# Deploy Photon Physics Academy (v1.3.8.4) — Netlify + Vercel

This is a Vite + React static site. The same source can deploy to **Netlify and Vercel**. The `netlify.toml` config is for Netlify; `vercel.json` provides the Vite build/output settings and SPA route fallback for Vercel.

## Required environment variables

Set these in the hosting provider's project settings for **Production and Preview/Deploy Preview** environments:

- `VITE_SUPABASE_URL` — your Supabase project URL
- `VITE_SUPABASE_ANON_KEY` — your Supabase publishable/anon client key (never a `service_role` key)

The current Netlify configuration already includes the public project URL and publishable key. For Vercel, add the same two values in **Project → Settings → Environment Variables**, then redeploy. `VITE_` variables are bundled into the browser, so only public/publishable values belong here. Keep SMS provider keys and all other secrets in Supabase Edge Function secrets, never in either hosting provider's frontend variables.

## Netlify

1. Import the repository or upload this project.
2. Build command: `npm run build`
3. Publish directory: `dist`
4. Node.js: 22 (configured in `netlify.toml`).
5. Confirm the two `VITE_` variables exist for deploy previews too, if preview builds need database access.

`netlify.toml` includes the SPA fallback so direct navigation to nested routes works.

## Vercel

1. Import the repository in Vercel and select the project root containing `package.json` and `vercel.json` (the `ppa133_src` folder in this ZIP).
2. Framework preset: Vite (auto-detected); build command `npm run build`; output directory `dist`. `vercel.json` also specifies these settings.
3. Add `VITE_SUPABASE_URL` and `VITE_SUPABASE_ANON_KEY` under Project Settings → Environment Variables for the environments you will use.
4. Deploy, then redeploy after changing environment variables.

The Vercel rewrite sends client-side routes to `index.html`, equivalent to the Netlify SPA fallback.

## Local verification

Use Node.js 22 (or Node.js >=20.19 as required by Vite):

```bash
npm install
npm run build
npm run lint
npm run preview
```

## Important testing note

Deploying a second frontend does **not** create a separate database. Both deployments will use whichever Supabase project is configured in their environment. Use a separate Supabase test project/branch and test payment credentials for isolated testing. Never put a Supabase `service_role` key or SMS gateway API key in client-side variables.
