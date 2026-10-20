# Netlify deployment — v1.3.8

1. Extract this archive.
2. Set the Netlify base directory to the folder containing `package.json`.
3. Build command: `npm run build`
4. Publish directory: `dist`
5. Configure the existing `VITE_SUPABASE_URL` and `VITE_SUPABASE_ANON_KEY` environment variables.
6. Build and test the deploy preview before publishing to the production domain.

This package does not deploy itself or apply any database migration. Review `CHANGELOG-v1.3.8.md` for features and remaining backend requirements.
