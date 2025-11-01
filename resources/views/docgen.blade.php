<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>KIBERSPĒKS — Dokumentu ģenerators</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.7/dist/purify.min.js"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-slate-50 text-slate-900">
<div class="max-w-6xl mx-auto px-4 py-8" x-data="app()" x-init="init()">

  <h1 class="text-2xl font-bold mb-4">Dokumentu ģenerators</h1>

  <!-- Organizācijas profils -->
  <div class="bg-white rounded-xl border shadow-sm p-5 mb-6">
    <h2 class="font-semibold mb-3">Organizācijas profils</h2>
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
      <div class="md:col-span-4">
        <label class="text-sm">Uzņēmums</label>
        <input x-model="profile.company" class="w-full rounded-lg border-slate-300" placeholder="SIA Piemērs">
      </div>
      <div class="md:col-span-4">
        <label class="text-sm">Reģ. Nr.</label>
        <input x-model="profile.reg_nr" class="w-full rounded-lg border-slate-300" placeholder="4000xxxxx">
      </div>
      <div class="md:col-span-4">
        <label class="text-sm">Sektors</label>
        <input x-model="profile.sector" class="w-full rounded-lg border-slate-300" placeholder="FinTech">
      </div>

      <div class="md:col-span-4">
        <label class="text-sm">Izmērs</label>
        <input x-model="profile.size" class="w-full rounded-lg border-slate-300" placeholder="mazs / vidējs / liels">
      </div>
      <div class="md:col-span-8">
        <label class="text-sm">Adrese</label>
        <input x-model="profile.address" class="w-full rounded-lg border-slate-300" placeholder="Brīvības iela 1, Rīga">
      </div>

      <div class="md:col-span-4">
        <label class="text-sm">Kontaktpersona - vārds</label>
        <input x-model="profile.contact.name" class="w-full rounded-lg border-slate-300" placeholder="Jānis Bērziņš">
      </div>
      <div class="md:col-span-4">
        <label class="text-sm">Kontaktpersona - e-pasts</label>
        <input x-model="profile.contact.email" class="w-full rounded-lg border-slate-300" placeholder="janis@example.com">
      </div>
      <div class="md:col-span-4">
        <label class="text-sm">Kontaktpersona - tālrunis</label>
        <input x-model="profile.contact.phone" class="w-full rounded-lg border-slate-300" placeholder="+371 2xxxxxxx">
      </div>
    </div>
  </div>

  <!-- Dokumenta tips + dinamiskie lauki -->
  <div class="bg-white rounded-xl border shadow-sm p-5 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
      <div class="md:col-span-4">
        <label class="text-sm">Dokumenta tips</label>
        <select x-model="type" class="w-full rounded-lg border-slate-300">
          <option value="self_assessment">Pašvērtējuma ziņojums (43. pants)</option>
          <option value="isms_policy">Kiberdrošības pārvaldības politika</option>
          <option value="crypto_policy">Šifrēšanas politika</option>
          <option value="bcp_drp">Biznesa nepārtrauktības un DR plāns</option>
          <option value="asset_catalog">IKT resursu katalogs</option>
        </select>
      </div>
      <div class="md:col-span-4">
        <label class="text-sm">Modelis (neobligāti)</label>
        <input x-model="model" class="w-full rounded-lg border-slate-300" placeholder="auto (Gemini)">
      </div>
    </div>

    <!-- SELF ASSESSMENT -->
    <div x-show="type==='self_assessment'" class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-4">
      <div class="md:col-span-4">
        <label class="text-sm">Novērtējuma periods</label>
        <input x-model="meta.period" class="w-full rounded-lg border-slate-300" placeholder="2025-01-01 – 2025-12-31">
      </div>
      <div class="md:col-span-4">
        <label class="text-sm">Risks</label>
        <input x-model="meta.risk_appetite" class="w-full rounded-lg border-slate-300" placeholder="zema / mērena / augsta">
      </div>
      <div class="md:col-span-12">
        <label class="text-sm">Sistēmas (katra jaunā rindā)</label>
        <textarea x-model="meta.systems" rows="3" class="w-full rounded-lg border-slate-300" placeholder="ERP\nCRM\nProdukcijas sistēma"></textarea>
      </div>
      <div class="md:col-span-12">
        <label class="text-sm">Tiesiskie pamati (katrs jaunā rindā)</label>
        <textarea x-model="meta.legal" rows="3" class="w-full rounded-lg border-slate-300" placeholder="GDPR\nNacionālais kiberdrošības likums\nNozares regula"></textarea>
      </div>
    </div>

    <!-- ISMS POLICY -->
    <div x-show="type==='isms_policy'" class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-4">
      <div class="md:col-span-12">
        <label class="text-sm">Mērķi (katrs jaunā rindā)</label>
        <textarea x-model="meta.objectives" rows="3" class="w-full rounded-lg border-slate-300" placeholder="Samazināt incidentus...\nPaaugstināt apzinātību..."></textarea>
      </div>
      <div class="md:col-span-6">
        <label class="text-sm">Lomas (JSON)</label>
        <textarea x-model="meta.roles" rows="4" class="w-full rounded-lg border-slate-300" placeholder='{"ISM Owner":"Vārds","IT Ops":"Vārds"}'></textarea>
      </div>
      <div class="md:col-span-6">
        <label class="text-sm">Pārskatīšanas biežums</label>
        <input x-model="meta.review_cadence" class="w-full rounded-lg border-slate-300" placeholder="reizi gadā">
      </div>
    </div>

    <!-- CRYPTO POLICY -->
    <div x-show="type==='crypto_policy'" class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-4">
      <div class="md:col-span-3">
        <label class="text-sm">Min. TLS versija</label>
        <input x-model="meta.min_tls" class="w-full rounded-lg border-slate-300" placeholder="1.2+">
      </div>
      <div class="md:col-span-3">
        <label class="text-sm">Šifrēšana at rest</label>
        <input x-model="meta.at_rest" class="w-full rounded-lg border-slate-300" placeholder="IESLĒGTA">
      </div>
      <div class="md:col-span-3">
        <label class="text-sm">Atslēgu pārvaldība</label>
        <input x-model="meta.kms" class="w-full rounded-lg border-slate-300" placeholder="AWS KMS / cits">
      </div>
      <div class="md:col-span-3">
        <label class="text-sm">Rotācija (dienās)</label>
        <input x-model.number="meta.key_rotation_days" class="w-full rounded-lg border-slate-300" placeholder="365">
      </div>
    </div>

    <!-- BCP/DRP -->
    <div x-show="type==='bcp_drp'" class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-4">
      <div class="md:col-span-6">
        <label class="text-sm">Kritiskās funkcijas (katra rindā)</label>
        <textarea x-model="meta.critical_functions" rows="3" class="w-full rounded-lg border-slate-300" placeholder="Maksājumu apstrāde\nKlientu atbalsts"></textarea>
      </div>
      <div class="md:col-span-3">
        <label class="text-sm">RTO</label>
        <input x-model="meta.rto" class="w-full rounded-lg border-slate-300" placeholder="4h">
      </div>
      <div class="md:col-span-3">
        <label class="text-sm">RPO</label>
        <input x-model="meta.rpo" class="w-full rounded-lg border-slate-300" placeholder="1h">
      </div>
      <div class="md:col-span-6">
        <label class="text-sm">Rezerves kopijas</label>
        <input x-model="meta.backups" class="w-full rounded-lg border-slate-300" placeholder="Dienas/iespēja atjaunot 30 dienas">
      </div>
      <div class="md:col-span-6">
        <label class="text-sm">DR lokācija</label>
        <input x-model="meta.dr_site" class="w-full rounded-lg border-slate-300" placeholder="AWS eu-central-1">
      </div>
    </div>

    <!-- ASSET CATALOG -->
    <div x-show="type==='asset_catalog'" class="mt-4">
      <label class="text-sm mb-2 block">IKT aktīvi</label>
      <div class="overflow-auto border rounded-lg">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-100">
          <tr>
            <th class="text-left p-2">Aktīvs</th>
            <th class="text-left p-2">Tips</th>
            <th class="text-left p-2">Īpašnieks</th>
            <th class="text-left p-2">Kritiskums</th>
            <th class="text-left p-2">Vieta</th>
            <th class="text-left p-2">Piezīmes</th>
            <th class="p-2"></th>
          </tr>
          </thead>
          <tbody>
          <template x-for="(row,i) in meta.assets" :key="i">
            <tr class="border-t">
              <td class="p-2"><input x-model="row.name" class="w-full border-slate-300 rounded"></td>
              <td class="p-2"><input x-model="row.type" class="w-full border-slate-300 rounded"></td>
              <td class="p-2"><input x-model="row.owner" class="w-full border-slate-300 rounded"></td>
              <td class="p-2"><input x-model="row.criticality" class="w-full border-slate-300 rounded" placeholder="LOW/MEDIUM/HIGH"></td>
              <td class="p-2"><input x-model="row.location" class="w-full border-slate-300 rounded"></td>
              <td class="p-2"><input x-model="row.notes" class="w-full border-slate-300 rounded"></td>
              <td class="p-2 text-right">
                <button @click="meta.assets.splice(i,1)" class="px-2 py-1 text-rose-600">Dzēst</button>
              </td>
            </tr>
          </template>
          </tbody>
        </table>
      </div>
      <button @click="meta.assets.push({name:'',type:'',owner:'',criticality:'',location:'',notes:''})"
              class="mt-2 px-3 py-1.5 rounded bg-slate-900 text-white">
        Pievienot rindu
      </button>
    </div>
  </div>

  <!-- Pogas -->
  <div class="flex flex-wrap gap-3">
    <button :disabled="busy" @click="withBusy(generateText)" class="px-4 py-2 rounded-xl bg-indigo-600 text-white disabled:opacity-50">Ģenerēt tekstu</button>
    <button :disabled="busy" @click="withBusy(generatePdf)"  class="px-4 py-2 rounded-xl bg-emerald-600 text-white disabled:opacity-50">Ģenerēt PDF</button>
  </div>

  <!-- Rezultāts -->
  <div class="mt-6 bg-white rounded-xl border shadow-sm">
    <div class="px-5 py-3 border-b flex items-center justify-between">
      <h3 class="font-semibold">Rezultāts</h3>
      <div class="text-xs text-slate-500" x-text="status"></div>
    </div>
    <div class="p-5 prose max-w-none">
      <div x-html="html"></div>
    </div>
    <div class="p-5 border-t flex items-center gap-3">
      <button @click="downloadMd()" class="px-3 py-1.5 rounded bg-slate-100">Lejupielādēt .md</button>
      <template x-if="pdfUrl">
        <a :href="pdfUrl" download="dokuments.pdf" class="px-3 py-1.5 rounded bg-slate-100">Lejupielādēt PDF</a>
      </template>
    </div>
  </div>

</div>

<script>
function app(){
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const api = async (url, payload={}, expectBlob=false) => {
    const res = await fetch(url, {
      method:'POST',
      headers:{'Content-Type':'application/json', ...(csrf? {'X-CSRF-TOKEN':csrf}:{})},
      body: JSON.stringify(payload)
    });
    if(!res.ok){ throw new Error(await res.text()); }
    return expectBlob ? res.blob() : res.json();
  };
  const md = (txt)=> DOMPurify.sanitize(marked.parse(txt||''));

  return {
    busy:false, status:'', html:'', raw:'', pdfUrl:'',
    type:'self_assessment', model:'',
    profile:{
      company:'SIA Piemērs',
      reg_nr:'4000xxxxx',
      sector:'FinTech',
      size:'mazs',
      address:'Brīvības iela 1, Rīga',
      contact:{ name:'Jānis Bērziņš', email:'janis@example.com', phone:'+3712xxxxxxx' }
    },
    meta:{ assets: [] },

    withBusy(fn){ if(this.busy) return; this.busy=true; Promise.resolve(fn.call(this)).finally(()=>this.busy=false); },
    setStatus(s){ this.status = s; },
    format(text){ this.raw = text; this.html = md(text); },

    async generateText(){
      this.setStatus('Strādā…'); this.pdfUrl='';
      try{
        const payload = this.buildPayload();
        const data = await api('/api/documents/generate', payload);
        this.format(String(data.content||'')); this.setStatus('OK');
      }catch(e){ this.format(String(e)); this.setStatus('Kļūda'); }
    },

    async generatePdf(){
      this.setStatus('Strādā…'); this.pdfUrl='';
      try{
        const payload = this.buildPayload();
        const blob = await api('/api/documents/generate-pdf', payload, true);
        this.pdfUrl = URL.createObjectURL(blob);
        this.setStatus('OK');
      }catch(e){ this.format(String(e)); this.setStatus('Kļūda'); }
    },

    buildPayload(){
      // notīrām ISMS roles (ja ievadīts kā teksts, mēģinām parse)
      if(this.type==='isms_policy' && typeof this.meta.roles === 'string'){
        try { this.meta.roles = JSON.parse(this.meta.roles); } catch {}
      }
      // “katra rindā” laukus pārvēršam masīvā:
      const splitLines = (v)=> (typeof v==='string' ? v.split(/\r?\n/).map(s=>s.trim()).filter(Boolean) : v);
      if(this.type==='self_assessment'){
        this.meta.systems = splitLines(this.meta.systems||'');
        this.meta.legal   = splitLines(this.meta.legal||'');
      }
      if(this.type==='isms_policy'){
        this.meta.objectives = splitLines(this.meta.objectives||'');
      }
      if(this.type==='bcp_drp'){
        this.meta.critical_functions = splitLines(this.meta.critical_functions||'');
      }
      return {
        type: this.type,
        profile: this.profile,
        meta: this.meta,
        model: this.model || undefined
      };
    },

    downloadMd(){
      const a = document.createElement('a');
      a.href = URL.createObjectURL(new Blob([this.raw||''], {type:'text/markdown;charset=utf-8'}));
      a.download = 'dokuments.md'; a.click(); URL.revokeObjectURL(a.href);
    },

    init(){}
  }
}
</script>
</body>
</html>
