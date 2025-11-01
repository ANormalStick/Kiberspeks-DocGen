<!DOCTYPE html>
<html lang="lv" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>KIBERSPĒKS — Demo</title>

  <!-- Tailwind (CDN for dev) -->
  <script>
    tailwind = { config: { darkMode: false } };
  </script>
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Alpine.js -->
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Minimal, safe Markdown-ish renderer (no external libs) -->
  <script>
    // Escape HTML
    function esc(s){ return String(s ?? '')
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }

    // Very small MD renderer: headings (#,##,###), **bold**, - list, and newlines -> <br>
    window.renderMd = function(text){
      let t = esc(text);

      // normalize CRLF
      t = t.replace(/\r\n/g, '\n');

      // headings (start of line)
      t = t.replace(/^###\s+(.*)$/gm, '<h3 class="font-semibold text-base mt-3 mb-1">$1</h3>');
      t = t.replace(/^##\s+(.*)$/gm,  '<h2 class="font-semibold text-lg mt-4 mb-2">$1</h2>');
      t = t.replace(/^#\s+(.*)$/gm,   '<h1 class="font-semibold text-xl mt-4 mb-2">$1</h1>');

      // bold **text**
      t = t.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

      // simple lists: consecutive lines starting with "- "
      t = t.replace(/(^- .+(?:\n- .+)*)/gm, (block)=>{
        const items = block.split('\n').map(l=>'<li>'+l.slice(2)+'</li>').join('');
        return '<ul class="list-disc pl-5 space-y-1">'+items+'</ul>';
      });

      // paragraphs: split by double newline into <p>, but avoid wrapping tags we already produced
      t = t.split(/\n{2,}/).map(chunk=>{
        if (/^\s*<(h1|h2|h3|ul)/.test(chunk)) return chunk;
        return '<p class="mb-2">'+chunk.replace(/\n/g,'<br>')+'</p>';
      }).join('');

      return t;
    };
  </script>
</head>

<body class="h-full bg-slate-50 text-slate-900">
<div class="min-h-screen">

  <!-- Header -->
  <header class="border-b bg-white">
    <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <div class="h-8 w-8 rounded-xl bg-indigo-600"></div>
        <h1 class="text-2xl font-bold tracking-tight">KIBERSPĒKS</h1>
        <span class="ml-2 px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-xs">Demo</span>
      </div>
      <div class="text-sm text-slate-500">Laravel · Tailwind · Alpine</div>
    </div>
  </header>

  <main class="max-w-6xl mx-auto px-4 py-8" x-data="app()" x-init="init()">

    <!-- Controls -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
      <div class="md:col-span-3">
        <label class="text-sm">Sektors</label>
        <input x-model="sector" class="w-full rounded-lg border-slate-300" placeholder="FinTech">
      </div>
      <div class="md:col-span-3">
        <label class="text-sm">Izmērs</label>
        <input x-model="size" class="w-full rounded-lg border-slate-300" placeholder="mazs">
      </div>
      <div class="md:col-span-6">
        <label class="text-sm">Stack</label>
        <input x-model="stack" class="w-full rounded-lg border-slate-300" placeholder="AWS, Laravel">
      </div>
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-4">
      <div class="md:col-span-4">
        <label class="text-sm">Politikas tips</label>
        <input x-model="policyType" class="w-full rounded-lg border-slate-300" placeholder="Informācijas drošības politika">
      </div>

      <div class="md:col-span-4">
        <label class="text-sm">AI Provider</label>
        <select x-model="provider" class="w-full rounded-lg border-slate-300">
          <option value="gemini">Gemini</option>
        </select>
      </div>
    </div>

    <!-- Action buttons -->
    <div class="mt-6 flex flex-wrap gap-3">
      <button :disabled="busy" @click="withBusy(runCompliance)" class="px-4 py-2 rounded-xl bg-slate-900 text-white disabled:opacity-50">
        Compliance AI (JSON)
      </button>

      <button :disabled="busy" @click="withBusy(runRisks)" class="px-4 py-2 rounded-xl bg-slate-900 text-white disabled:opacity-50">
        Risku reģistrs (JSON)
      </button>

      <button :disabled="busy" @click="withBusy(runPolicyText)" class="px-4 py-2 rounded-xl bg-indigo-600 text-white disabled:opacity-50">
        Politika (teksts)
      </button>

      <button :disabled="busy" @click="withBusy(runPolicyPdf)" class="px-4 py-2 rounded-xl bg-emerald-600 text-white disabled:opacity-50">
        Politika → PDF
      </button>
    </div>

    <!-- Output -->
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-6">

      <!-- Result -->
      <div class="lg:col-span-8 space-y-3">
        <div class="rounded-2xl bg-white border shadow-sm">
          <div class="px-5 py-3 border-b flex items-center justify-between">
            <h3 class="font-semibold">Rezultāts</h3>
            <div class="text-xs text-slate-500" x-text="statusText"></div>
          </div>
          <div class="p-5">
            <div class="prose max-w-none" x-html="outputHtml"></div>
          </div>
        </div>

        <div class="rounded-2xl bg-white border shadow-sm p-5 space-y-3">
          <h3 class="font-semibold">Lejupielādes</h3>
          <template x-if="pdfUrl">
            <a :href="pdfUrl" download="policy_onepager.pdf"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white">
              Lejupielādēt PDF
            </a>
          </template>
        </div>
      </div>

      <!-- Chat -->
      <div class="lg:col-span-4">
        <div class="rounded-2xl bg-white border shadow-sm flex flex-col h-[550px]">
          <div class="px-5 py-3 border-b flex items-center justify-between">
            <h3 class="font-semibold">LV čats</h3>
          </div>

          <div class="flex-1 overflow-auto p-4 space-y-3" id="chatScroll">
            <template x-for="(m, i) in chat" :key="i">
              <div>
                <div class="text-[10px] text-slate-500 mb-1" x-text="m.role==='user' ? 'Jūs' : 'AI'"></div>
                <div class="rounded-lg px-3 py-2 bg-slate-100">
                  <div class="prose max-w-none text-sm" x-html="renderMd(m.content)"></div>
                </div>
              </div>
            </template>
          </div>

          <div class="p-3 border-t flex items-center gap-2">
            <input x-model="chatQ" @keydown.enter.prevent="withBusy(sendChat)"
                   class="flex-1 rounded-lg border-slate-300"
                   placeholder="Uzdod jautājumu…">
            <button :disabled="busy" @click="withBusy(sendChat)"
                    class="px-4 py-2 rounded-lg bg-indigo-600 text-white">
              Sūtīt
            </button>
          </div>
        </div>
      </div>
    </div>
  </main>
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
    if(!res.ok){
      let txt; try { txt = await res.text(); } catch { txt = res.status+' '+res.statusText; }
      throw new Error(txt);
    }
    return expectBlob ? res.blob() : res.json();
  };

  return {
    // UI state
    busy:false,
    statusText:'',
    pdfUrl:'',
    outputRaw:'',
    outputHtml:'',

    // Inputs
    sector:'FinTech',
    size:'mazs',
    stack:'AWS, Laravel',
    policyType:'Informācijas drošības politika',
    provider:'gemini',
    model:'',
    chatQ:'',
    chat:[],

    // Render helpers
    formatOutput(text){
      this.outputRaw = String(text ?? '');
      this.outputHtml = renderMd(this.outputRaw);
    },

    withBusy(fn){
      if(this.busy) return;
      this.busy = true;
      Promise.resolve(fn.call(this)).finally(()=> this.busy=false);
    },

    async runCompliance(){
      this.statusText='Strādā…';
      try{
        const data = await api('/api/ai/compliance', {
          sector:this.sector, size:this.size, stack:this.stack, model:this.model
        });
        let items = data.items ?? data;

        if (Array.isArray(items)) {
          const mdText = '### Compliance ieteikumi\n\n' + items.map(x=>`- ${x}`).join('\n');
          this.formatOutput(mdText);
        } else if (typeof items === 'string') {
          this.formatOutput(items);
        } else {
          this.formatOutput(JSON.stringify(items, null, 2));
        }

        this.statusText='OK';
      }catch(e){
        this.formatOutput(String(e));
        this.statusText='Kļūda';
      }
    },

    async runRisks(){
      this.statusText='Strādā…';
      try{
        const raw = await api('/api/ai/risks', {
          context:`${this.size} uzņēmums; sektors ${this.sector}; stack ${this.stack}`,
          model:this.model
        });

        let data = raw;
        if (typeof raw === 'string') {
          let t = raw.replace(/```json|```/g,'').trim();
          try { data = JSON.parse(t); } catch { data = raw; }
        }

        if (Array.isArray(data)) {
          const md = ['### Risku reģistrs','',
            ...data.map(r => `- **${r.risk}** (L:${r.likelihood}/I:${r.impact}) — ${r.mitigation}`)
          ].join('\n');
          this.formatOutput(md);
        } else {
          this.formatOutput(typeof data === 'string' ? data : JSON.stringify(data, null, 2));
        }

        this.statusText='OK';
      }catch(e){
        this.formatOutput(String(e));
        this.statusText='Kļūda';
      }
    },

    async runPolicyText(){
      this.statusText='Strādā…';
      try{
        const data = await api('/api/policy/generate', {
          type:this.policyType, sector:this.sector, size:this.size, model:this.model
        });
        this.formatOutput(String(data.content ?? data));
        this.statusText='OK';
      }catch(e){
        this.formatOutput(String(e));
        this.statusText='Kļūda';
      }
    },

    async runPolicyPdf(){
      this.statusText='Strādā…';
      try{
        const blob = await api('/api/policy/generate-pdf', {
          type:this.policyType, sector:this.sector, size:this.size, model:this.model
        }, true);
        this.pdfUrl = URL.createObjectURL(blob);
        this.formatOutput('**PDF sagatavots.** Lejupielādē zemāk.');
        this.statusText='OK';
      }catch(e){
        this.formatOutput(String(e));
        this.statusText='Kļūda';
      }
    },

    async sendChat(){
      const q = (this.chatQ || '').trim();
      if(!q) return;

      this.chat.push({ role:'user', content:q });
      this.chatQ = '';
      this.$nextTick(()=> {
        const el = document.getElementById('chatScroll');
        if (el) el.scrollTop = el.scrollHeight;
      });

      try{
        const data = await api('/api/ai/chat', { q, model:this.model });
        // Accept either {reply: "..."} or plain string
        const reply = (typeof data === 'string' ? data : (data.reply ?? data.content ?? '')) || '';
        this.chat.push({ role:'assistant', content:String(reply) });
      }catch(e){
        this.chat.push({ role:'assistant', content:'Kļūda: ' + String(e) });
      }

      this.$nextTick(()=> {
        const el = document.getElementById('chatScroll');
        if (el) el.scrollTop = el.scrollHeight;
      });
    },

    init(){ /* place for persistence/health later */ }
  }
}
</script>

</body>
</html>
