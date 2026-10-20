# Deployment: Netlify and Vercel

This repository is a Vite + React single-page application and supports deployment on either Netlify or Vercel.

## Build settings

- Framework: Vite
- Build command: `npm run build`
- Output directory: `dist`
- Node.js: 22 (the project requires Node.js 20.19 or newer)

The existing `netlify.toml` configures Netlify's build, SPA fallback, and public Supabase settings. The existing `vercel.json` configures Vercel's build output and SPA fallback.

## Vercel environment variables

In Vercel, open **Project → Settings → Environment Variables** and add these variables for the environments you use (Preview and Production):

- `VITE_SUPABASE_URL`: your Supabase project URL
- `VITE_SUPABASE_ANON_KEY`: your Supabase publishable key (or legacy anon/public key)

Then redeploy so the variables are included in the client build.

Only public/publishable Supabase keys belong in `VITE_*` browser variables. **Never add a Supabase service-role key, SMS provider API key, or other secret to a `VITE_*` variable or commit it to this repository.** Keep server-side secrets in Supabase Edge Function secrets or the relevant server-side environment settings.

## Update the existing Vercel project

1. Import this existing GitHub repository into Vercel, or ensure the existing Vercel project is connected to it.
2. Set the Production Branch to the intended production branch (usually `main`).
3. Configure the environment variables above.
4. Push changes to the connected branch. Vercel creates a deployment automatically.
5. Check the deployment build logs and test the preview before promoting changes to production.

## Update the existing Netlify project

Keep the existing Netlify site connected to this repository. Its build configuration is in `netlify.toml`; push changes to the connected branch to trigger a deployment.

## Important

A separate Preview URL does **not** automatically mean a separate database. Both hosting platforms can connect to the same Supabase project if configured with the same variables. Use a separate Supabase development project for isolated test data.
