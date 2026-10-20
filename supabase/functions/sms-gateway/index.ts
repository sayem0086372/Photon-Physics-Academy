import { createClient } from "https://esm.sh/@supabase/supabase-js@2";

const corsHeaders = {
  "Access-Control-Allow-Origin": Deno.env.get("APP_ORIGIN") ?? "*",
  "Access-Control-Allow-Headers": "authorization, x-client-info, apikey, content-type",
  "Access-Control-Allow-Methods": "POST, OPTIONS",
};

Deno.serve(async (req) => {
  if (req.method === "OPTIONS") return new Response("ok", { headers: corsHeaders });
  const json = (body: unknown, status = 200) => new Response(JSON.stringify(body), {
    status, headers: { ...corsHeaders, "Content-Type": "application/json" },
  });
  if (req.method !== "POST") return json({ error: "Method not allowed" }, 405);

  const authHeader = req.headers.get("Authorization");
  if (!authHeader) return json({ error: "Sign-in required" }, 401);
  const supabaseUrl = Deno.env.get("SUPABASE_URL");
  const anonKey = Deno.env.get("SUPABASE_ANON_KEY");
  const adminId = Deno.env.get("PPA_ADMIN_USER_ID");
  const apiKey = Deno.env.get("SMSBD_API_KEY");
  if (!supabaseUrl || !anonKey || !adminId || !apiKey) return json({ error: "SMS backend is not fully configured. Set Supabase function secrets." }, 503);

  const supabase = createClient(supabaseUrl, anonKey, { global: { headers: { Authorization: authHeader } } });
  const { data: { user }, error: authError } = await supabase.auth.getUser();
  if (authError || !user || user.id !== adminId) return json({ error: "Administrator access required" }, 403);

  let body: { action?: string; to?: string[]; msg?: string; schedule?: string; sender_id?: string; content_id?: string };
  try { body = await req.json(); } catch { return json({ error: "Invalid JSON" }, 400); }
  const action = body.action ?? "send";
  try {
    if (action === "balance") {
      const response = await fetch("https://api.sms.net.bd/user/balance/?" + new URLSearchParams({ api_key: apiKey }), {
        method: "GET",
        headers: { "Accept": "application/json" },
      });
      let result: any;
      try { result = await response.json(); }
      catch { return json({ error: "Invalid response from sms.bd balance endpoint", httpStatus: response.status }, 502); }
      if (!response.ok) return json({ error: result?.error ?? response.status, msg: result?.msg ?? `sms.bd returned HTTP ${response.status}` }, 502);
      // Normalize provider response so the browser can reliably read the balance.
      const providerError = Number(result?.error);
      if (!Number.isFinite(providerError) || providerError !== 0) {
        return json({ error: result?.error ?? "Balance request failed", msg: result?.msg ?? "sms.bd did not return a successful balance response", provider: result }, 502);
      }
      const balance = result?.data?.balance ?? result?.balance ?? result?.data?.credits ?? result?.credits ?? result?.data;
      return json({ error: 0, msg: result?.msg ?? "Balance retrieved", data: { balance }, provider: result }, 200);
    }
    if (action !== "send") return json({ error: "Unknown action" }, 400);
    const msg = (body.msg ?? "").trim();
    const recipients = [...new Set((body.to ?? []).map((n) => n.replace(/[\s()-]/g, "")).filter(Boolean))];
    if (!msg) return json({ error: "Message cannot be empty" }, 400);
    if (!recipients.length) return json({ error: "No valid recipients selected" }, 400);
    if (msg.length > 1000) return json({ error: "Message is too long" }, 400);
    // Send individually to avoid ambiguous bulk recipient results and to support per-recipient errors.
    const results = [];
    for (const to of recipients) {
      const form = new URLSearchParams({ api_key: apiKey, msg, to });
      if (body.schedule) form.set("schedule", body.schedule);
      if (body.sender_id) form.set("sender_id", body.sender_id);
      if (body.content_id) form.set("content_id", body.content_id);
      const response = await fetch("https://api.sms.net.bd/sendsms", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: form });
      let data: unknown;
      try { data = await response.json(); } catch { data = { error: -1, msg: "Invalid gateway response" }; }
      results.push({ to, ...(typeof data === "object" && data !== null ? data : { response: data }) });
    }
    // Deliberately do not persist sent messages or recipient history.
    const failed = results.filter((r) => (r as { error?: number }).error !== 0).length;
    return json({ success: failed === 0, sent: results.length - failed, failed, results });
  } catch (error) {
    return json({ error: error instanceof Error ? error.message : "SMS gateway request failed" }, 502);
  }
});
