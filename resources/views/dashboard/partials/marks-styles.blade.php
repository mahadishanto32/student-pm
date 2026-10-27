@once
<style>
    .dxm-project{border:1px solid #e3e8ef;border-radius:14px;overflow:hidden;margin-bottom:14px;background:#fff}
    .dxm-hero{display:flex;align-items:center;gap:18px;padding:16px 18px;color:#fff;background:linear-gradient(135deg,#1f3a5f,#2c6db5)}
    .dxm-hero.t-a{background:linear-gradient(135deg,#14684a,#1f8a63)}
    .dxm-hero.t-b{background:linear-gradient(135deg,#1f3a5f,#2c6db5)}
    .dxm-hero.t-c{background:linear-gradient(135deg,#8a5a08,#c98a1b)}
    .dxm-hero.t-f{background:linear-gradient(135deg,#7d2a3d,#a53c52)}
    .dxm-hero.t-i{background:linear-gradient(135deg,#566274,#7b8798)}
    .dxm-grade{flex:0 0 auto;width:68px;height:68px;border-radius:50%;background:rgba(255,255,255,.18);border:2px solid rgba(255,255,255,.6);display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:700}
    .dxm-hero__main{flex:1;min-width:0}
    .dxm-hero__name{font-weight:600;font-size:15px;line-height:1.3}
    .dxm-hero__sub{font-size:12px;opacity:.85}
    .dxm-figures{display:flex;gap:22px;text-align:center}
    .dxm-figures b{display:block;font-size:22px;line-height:1.1}
    .dxm-figures span{font-size:11px;text-transform:uppercase;letter-spacing:.04em;opacity:.85}
    .dxm-topics{padding:12px 18px}
    .dxm-topic{display:grid;grid-template-columns:110px 1fr 54px;gap:10px;align-items:center;font-size:13px;margin:6px 0}
    .dxm-topic__bar{height:8px;border-radius:4px;background:#eef1f5;overflow:hidden}
    .dxm-topic__bar i{display:block;height:100%;background:#2c6db5;border-radius:4px}
    .dxm-topic b{text-align:right}
    .dxm-note{padding:0 18px 12px;font-size:12px;color:#6b7686}
    .dxm-pill{display:inline-block;min-width:34px;padding:2px 9px;border-radius:12px;font-weight:700;font-size:12px;text-align:center;color:#fff}
    .dxm-pill.t-a{background:#1f8a63}.dxm-pill.t-b{background:#2c6db5}.dxm-pill.t-c{background:#c98a1b}.dxm-pill.t-f{background:#a53c52}.dxm-pill.t-i{background:#7b8798}
    .dxm-head{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:12px 18px;background:#f6f8fb;border-bottom:1px solid #e3e8ef}
    .dxm-head h4{margin:0;font-size:15px}
    .dxm-table{width:100%;font-size:13px}
    .dxm-table th{font-weight:600;color:#6b7686;font-size:11px;text-transform:uppercase;padding:8px 12px;border-bottom:1px solid #eef1f5;white-space:nowrap}
    .dxm-table td{padding:8px 12px;border-bottom:1px solid #f1f3f7;vertical-align:middle}
    .dxm-table tr:last-child td{border-bottom:0}
    .dxm-dist{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px}
    .dxm-dist span{display:flex;align-items:center;gap:6px;border:1px solid #e3e8ef;border-radius:10px;padding:4px 10px;font-size:13px}
    @media(max-width:576px){.dxm-hero{flex-wrap:wrap}.dxm-topic{grid-template-columns:90px 1fr 48px}}
</style>
@endonce