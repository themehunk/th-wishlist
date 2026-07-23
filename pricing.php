<?php

function th_pricing_html_shortcode($plugin=array()) {

  $plugin_map = [
    'lead-form'          => 'Lead Form Builder Pro',
    'popup-builder'      => 'WP Popup Builder Pro',
    'product-compare'    => 'Product Compare Pro',
    'advance-search'     => 'Advance Search Pro',
    'woo-cart'           => 'All in One Woo Cart Pro',
    'variation-swatches' => 'Variation Swatches Pro',
    'elemento-addons'    => 'Elemento Addons Pro',
    'wishlist'           => 'Wishlist for Woo',
    'store-one'          => 'Store One',
    'vayu-blocks'        => 'Vayu Blocks',
  ];

  $default_plugin =  '';

  $yearly   = "yearly";
  $pro      = "pro";
  $ultimate = "ultimat";

  if ( ! empty( $plugin ) ) {
    $default_plugin = $plugin_map[ $plugin ];
    $plan     = pricing_plugin_url( $plugin );
    $link     = 'https://members.themehunk.com/signup/';
    $yearly   = $link . $plan['theme']['annual'];
    $pro      = $link . $plan['developer']['lifetime'];
    $ultimate = $link . $plan['super']['lifetime'];
  }



  static $loaded = false;

  ob_start();

  if ( ! $loaded ) {
    $loaded = true;
    ?>
    <style>
    *{
    --thp-paper:#FBFAF7;
    --thp-ink:#16142B;
    --thp-ink-soft:#3A3753;
    --thp-muted:#6F6C84;
    --thp-line:#E8E5F1;
    --thp-primary:#5B49F0;
    --thp-primary-deep:#4435CB;
    --thp-primary-tint:#EFEBFF;
    --thp-accent:#FF6B5C;
    --thp-good:#1FA97A;
    --thp-shadow:0 18px 50px -22px rgba(40,30,120,.45);
    --thp-radius:18px;
  }


  .thp-wrap{max-width:1140px;margin:0 auto;padding:56px 22px 80px}

  /* ---- header ---- */
  .thp-eyebrow{
    font-family:'Space Grotesk',sans-serif;
    font-weight:600;letter-spacing:.16em;text-transform:uppercase;
    font-size:12px;color:var(--thp-primary);margin:0 0 14px;
    display:inline-flex;align-items:center;gap:9px;
  }
  .thp-eyebrow::before{content:"";width:26px;height:2px;background:var(--thp-primary);border-radius:2px}
/*  h2{
    font-family:'Space Grotesk',sans-serif;
    font-size:clamp(30px,4.4vw,46px);line-height:1.05;
    font-weight:700;letter-spacing:-.02em;margin:0 0 14px;max-width:16ch;
  }
  h2 .thp-h2{color:var(--thp-primary)}*/
  .thp-sub{font-size:17px;color:var(--thp-muted);max-width:54ch;margin:0}

  /* ---- pricing grid ---- */
  .thp-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;align-items:start;margin-top:44px}
  .thp-card{
    background:#fff;border:1px solid var(--thp-line);border-radius:var(--thp-radius);
    padding:26px 24px 28px;position:relative;box-shadow:0 10px 30px -24px rgba(40,30,120,.5);
    transition:transform .18s, box-shadow .18s, border-color .18s;
  }
  .thp-card.thp-pop{border-color:var(--thp-primary);box-shadow:var(--thp-shadow);transform:translateY(-6px)}
  .thp-card.thp-rec{border-color:var(--thp-good);box-shadow:0 18px 50px -20px rgba(31,169,122,.5);animation:thpRecPulse 1.6s ease-out 1}
  @keyframes thpRecPulse{
    0%{box-shadow:0 0 0 0 rgba(31,169,122,.45), 0 18px 50px -20px rgba(31,169,122,.5)}
    70%{box-shadow:0 0 0 16px rgba(31,169,122,0), 0 18px 50px -20px rgba(31,169,122,.5)}
    100%{box-shadow:0 0 0 0 rgba(31,169,122,0), 0 18px 50px -20px rgba(31,169,122,.5)}
  }
  @media(prefers-reduced-motion:reduce){.thp-card.thp-rec{animation:none}}
  .thp-tag{
    position:absolute;top:-13px;left:24px;font-family:'Space Grotesk',sans-serif;
    font-weight:600;font-size:11.5px;letter-spacing:.06em;text-transform:uppercase;
    color:#fff;background:var(--thp-primary);border-radius:99px;padding:5px 13px;
  }
  .thp-card.thp-rec .thp-tag{background:var(--thp-good)}
  .thp-plan{font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:14px;color:var(--thp-muted);letter-spacing:.04em;text-transform:uppercase;margin:0}
  .thp-price{font-family:'Space Grotesk',sans-serif;font-weight:700;letter-spacing:-.02em;margin:8px 0 2px;display:flex;align-items:baseline;gap:6px}
  .thp-price .thp-amt{font-size:42px;line-height:1}
  .thp-price .thp-per{font-size:15px;color:var(--thp-muted);font-weight:500}
  .thp-tagline{font-size:14px;color:var(--thp-muted);margin:0 0 18px;min-height:20px}
  .thp-cta{
    display:block;text-align:center;text-decoration:none;font-family:'Space Grotesk',sans-serif;
    font-weight:600;font-size:15px;border-radius:12px;padding:13px;margin-bottom:22px;
    border:1.5px solid var(--thp-primary);color:var(--thp-primary);background:#fff;transition:all .15s;
  }
  .thp-cta:hover{background:var(--thp-primary-tint)}
  .thp-card.thp-pop .thp-cta,.thp-card.thp-rec .thp-cta{color:#fff;background:linear-gradient(135deg,var(--thp-primary),var(--thp-primary-deep));border-color:transparent;box-shadow:0 10px 22px -12px rgba(68,53,203,.9)}
  .thp-card.thp-rec .thp-cta{background:linear-gradient(135deg,var(--thp-good),#138A62)}
  .thp-feat{list-style:none;margin:0;padding:0;display:grid;gap:11px}
  .thp-feat li{display:flex;gap:10px;font-size:14.5px;color:var(--thp-ink-soft);align-items:flex-start}
  .thp-feat .thp-ic{flex:0 0 auto;margin-top:1px;color:var(--thp-good)}
  .thp-feat .thp-ic.thp-off{color:#C9C6D6}
  .thp-feat li.thp-off{color:#AEABBE}
  .thp-feat li b{color:var(--thp-ink);font-weight:600}

  /* ---- single-plugin picker ---- */
  .thp-picker{margin-top:18px;padding-top:16px;border-top:1px dashed var(--thp-line)}
  .thp-picker label{
    display:block;font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:12px;
    letter-spacing:.04em;text-transform:uppercase;color:var(--thp-muted);margin-bottom:7px;
  }
  .thp-plugin-select{
    width:100%;font:inherit;font-size:14px;color:var(--thp-ink);cursor:pointer;
    border:1.5px solid var(--thp-line);border-radius:11px;padding:11px 38px 11px 13px;background:#FCFCFF;
    -webkit-appearance:none;appearance:none;transition:border-color .15s,box-shadow .15s;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none'%3E%3Cpath d='M6 9l6 6 6-6' stroke='%235B49F0' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat:no-repeat;background-position:right 13px center;
  }
  .thp-plugin-select:focus{outline:none;border-color:var(--thp-primary);box-shadow:0 0 0 4px var(--thp-primary-tint)}

  /* ---- included plugins ---- */
  .thp-included{margin-top:42px;background:#fff;border:1px solid var(--thp-line);border-radius:var(--thp-radius);padding:28px 26px;box-shadow:0 10px 30px -24px rgba(40,30,120,.5)}
  .thp-included h3{font-family:'Space Grotesk',sans-serif;font-size:19px;margin:0 0 4px}
  .thp-included p.thp-lead{margin:0 0 18px;color:var(--thp-muted);font-size:14.5px}
  .thp-savings{
    display:flex;flex-wrap:wrap;align-items:center;gap:14px 24px;margin:0 0 22px;
    padding:18px 22px;border-radius:14px;
    background:linear-gradient(120deg,#F2EFFF,#EAF7F1);border:1px solid #E4DEF7;
  }
  .thp-savings .thp-sep-blk{display:flex;flex-direction:column;gap:2px}
  .thp-savings .thp-lbl{font-size:11.5px;letter-spacing:.05em;text-transform:uppercase;color:var(--thp-muted);font-family:'Space Grotesk',sans-serif;font-weight:600}
  .thp-savings .thp-sep-val{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:26px;color:var(--thp-muted);text-decoration:line-through;text-decoration-color:#C2435C;text-decoration-thickness:2px}
  .thp-savings .thp-arrow{color:var(--thp-primary);flex:0 0 auto}
  .thp-savings .thp-bundle-rows{display:flex;flex-direction:column;gap:10px;flex:1;min-width:300px}
  .thp-savings .thp-bundle-row{
    display:flex;align-items:center;gap:14px;background:#fff;border:1px solid var(--thp-line);
    border-radius:11px;padding:10px 14px;
  }
  .thp-savings .thp-blk{display:flex;flex-direction:column;gap:1px;flex:1}
  .thp-savings .thp-bundle-val{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:22px;letter-spacing:-.02em;color:var(--thp-ink);display:flex;align-items:baseline;gap:5px}
  .thp-savings .thp-bundle-val small{font-size:13px;color:var(--thp-muted);font-weight:500}
  .thp-savings .thp-bundle-val em{font-style:normal;color:var(--thp-good);font-weight:600}
  .thp-savings .thp-save-pill{
    font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:13.5px;
    color:#fff;background:linear-gradient(135deg,var(--thp-good),#0E9B6E);border-radius:99px;
    padding:8px 15px;box-shadow:0 8px 18px -8px rgba(31,169,122,.8);white-space:nowrap;
  }
  .thp-savings .thp-save-pill.thp-gold{background:linear-gradient(135deg,#E0962A,#C97A12);box-shadow:0 8px 18px -8px rgba(201,122,18,.8)}
  @media(max-width:560px){.thp-savings .thp-arrow{display:none}.thp-savings .thp-bundle-rows{min-width:0}}

  @media(max-width:480px){
    .thp-savings{padding:14px 14px;gap:10px 14px; flex-direction: column-reverse;}
    .thp-savings .thp-bundle-row{flex-wrap:wrap;gap:8px;padding:10px 12px; justify-content: end;}
    .thp-savings .thp-bundle-val{font-size:18px}
    .thp-savings .thp-sep-val{font-size:20px}
    .thp-savings .thp-save-pill{font-size:12px;padding:6px 11px;flex-shrink:0}
    .thp-savings .thp-blk{min-width:0;flex:1 1 auto}
  }

  /* ---- plugin comparison table ---- */
  .thp-plug-table-wrap{overflow-x:auto;border:1px solid var(--thp-line);border-radius:14px}
  .thp-plug-table{width:100%;border-collapse:collapse;min-width:540px;background:#fff}
  .thp-plug-table th,.thp-plug-table td{padding:13px 14px;border-bottom:1px solid var(--thp-line);text-align:center;vertical-align:middle}
  .thp-plug-table thead th{
    background:#FAF9FF;position:sticky;top:0;
    font-family:'Space Grotesk',sans-serif;font-weight:600;
  }
  .thp-plug-table .thp-th-plug{text-align:left;font-size:13px;color:var(--thp-muted);letter-spacing:.04em;text-transform:uppercase}
  .thp-th-plan{font-size:13px;min-width:96px}
  .thp-th-plan .thp-tp-name{display:block;font-size:14px;color:var(--thp-ink)}
  .thp-th-plan .thp-tp-price{display:block;font-size:11.5px;color:var(--thp-muted);font-weight:500;margin-top:1px}
  .thp-th-plan.thp-hot .thp-tp-name{color:var(--thp-primary-deep)}
  .thp-th-plan.thp-hot{background:var(--thp-primary-tint)}
  .thp-plug-table tbody tr:last-child td{border-bottom:none}
  .thp-plug-table tbody tr:hover{background:#FCFCFF}
  .thp-feat-row .thp-feat-cell{background:var(--thp-primary-tint)}
  .thp-feat-row:hover .thp-feat-cell{background:#E5E0FF}
  .thp-dot-feat{background:var(--thp-primary);color:#fff}
  .thp-plug-table td.thp-td-plug{text-align:left;display:flex;align-items:flex-start;gap:11px}
  .thp-td-plug .thp-dot{width:32px;height:32px;border-radius:8px;flex:0 0 auto;display:grid;place-items:center}
  .thp-td-info{display:flex;flex-direction:column;gap:1px;text-align:left}
  .thp-td-info b{font-size:14px;font-family:'Space Grotesk',sans-serif}
  .thp-td-info small{color:var(--thp-muted);font-size:12.5px}
  .thp-cell svg{display:inline-block}
  .thp-cell.thp-yes{color:var(--thp-good)}
  .thp-cell.thp-no{color:#D2A0B6}
  .thp-cell.thp-hot{background:rgba(91,73,240,.05)}
  .thp-plug-table tfoot td{border-bottom:none;border-top:1px solid var(--thp-line);padding:16px 14px;background:#FAF9FF}
  .thp-td-foot-label{text-align:left;font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:13px;color:var(--thp-muted)}
  .thp-td-buy.thp-hot{background:var(--thp-primary-tint)}
  .thp-buy-now{
    display:inline-block;text-decoration:none;font-family:'Space Grotesk',sans-serif;font-weight:600;
    font-size:14px;color:var(--thp-primary);border:1.5px solid var(--thp-primary);border-radius:10px;
    padding:9px 18px;transition:all .15s;white-space:nowrap;
  }
  .thp-buy-now:hover{background:var(--thp-primary-tint)}
  .thp-buy-now.thp-filled{color:#fff;background:linear-gradient(135deg,var(--thp-primary),var(--thp-primary-deep));border-color:transparent;box-shadow:0 10px 22px -12px rgba(68,53,203,.9)}
  .thp-buy-now.thp-filled:hover{filter:brightness(1.05)}

  .thp-foot{margin-top:26px;text-align:center;color:var(--thp-muted);font-size:13px}

  /* ---- view modules link + modal ---- */
  .thp-view-mods{
    display:inline-flex;align-items:center;gap:3px;margin-top:8px;cursor:pointer;
    font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:12.5px;color:var(--thp-primary);
    background:none;border:none;padding:0;
  }
  .thp-view-mods:hover{color:var(--thp-primary-deep);text-decoration:underline}
  .thp-modal-back{
    position:fixed;inset:0;background:rgba(20,16,40,.55);backdrop-filter:blur(3px);
    display:none;align-items:center;justify-content:center;padding:22px;z-index:50;
  }
  .thp-modal-back.thp-open{display:flex;animation:thpFade .2s ease}
  @keyframes thpFade{from{opacity:0}to{opacity:1}}
  .thp-modal{
    background:#fff;border-radius:20px;max-width:620px;width:100%;max-height:84vh;overflow:auto;
    padding:28px 28px 30px;position:relative;box-shadow:0 30px 80px -20px rgba(20,16,40,.6);
    animation:thpRise .28s cubic-bezier(.2,.9,.3,1.15);
    display:block;
  }
  @keyframes thpRise{from{opacity:0;transform:translateY(18px) scale(.98)}to{opacity:1;transform:none}}
  .thp-modal-x{
    position:absolute;top:16px;right:16px;width:34px;height:34px;border-radius:9px;border:none;
    background:#F4F2FC;color:var(--thp-ink-soft);cursor:pointer;display:grid;place-items:center;transition:background .15s;
  }
  .thp-modal-x:hover{background:var(--thp-primary-tint)}
  .thp-modal h3{font-family:'Space Grotesk',sans-serif;font-size:22px;margin:0 0 4px}
  .thp-modal p{margin:0 0 18px;color:var(--thp-muted);font-size:14px;max-width:46ch}
  .thp-mod-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:9px}
  .thp-mod{
    display:flex;align-items:center;gap:12px;border:1px solid var(--thp-line);border-radius:10px;
    padding:12px 14px;font-size:13.5px;color:var(--thp-ink-soft);background:#fff;
    transition:border-color .15s,box-shadow .15s;
  }
  .thp-mod:hover{border-color:var(--thp-primary);box-shadow:0 4px 14px -6px rgba(91,73,240,.18)}
  .thp-mn-svg{flex:0 0 auto;display:block;overflow:visible}
  .thp-mod-icon{width:32px;height:32px;border-radius:8px;flex:0 0 auto;display:grid;place-items:center}
  .thp-mod-icon svg{width:16px;height:16px;display:block}

  @media(max-width:860px){
    .thp-grid{grid-template-columns:1fr;gap:26px}
    .thp-card.thp-pop{transform:none}
  }

  @media(max-width:600px){
    .thp-wrap{padding:36px 14px 56px}
    .thp-included{padding:20px 14px}
    .thp-plug-table th,.thp-plug-table td{padding:10px 10px}
    .thp-plug-table{min-width:420px}
    .thp-td-info b{font-size:13px}
    .thp-td-info small{font-size:11.5px}
    .thp-th-plan{min-width:76px}
    .thp-buy-now{font-size:12px;padding:8px 12px}
  }
    </style>

    <?php
    $all_icons        = include plugin_dir_path( __FILE__ ) . 'svg/addon-icons.php';
    $icons_json       = wp_json_encode( $all_icons );
    $addon_icons_json = $icons_json;
    ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

    /* ====================================================================
   CONFIG  —  edit everything about your plans & products here
   ==================================================================== */
const CONFIG = {
  plans: [
    {
      id:'personal',
      name:'Starter',
      sub:'Single Plugin',
      price:'$29',
      per:'/year',
      tagline:'Perfect for one site, one plugin.',
      cta:'<?php echo $default_plugin; ?>',
      url:'<?php echo $yearly; ?>',
      popular:false,
      features:[
        {t:'1 website', on:true},
        {t:'1 year of updates', on:true},
        {t:'1 year of support', on:true},
        {t:'All 10 premium plugins', on:false},
        {t:'One to One Premium Support', on:true},
        {t:'One Time Payment', on:false},
        {t:'14-days Money Back Guarantee*', on:true},
      ],
      sites:1, allPlugins:false, lifetime:false, priority:false
    },
    {
      id:'complete',
      name:'Pro',
      sub:'Complete Suite',
      price:'$59',
      per:'/year',
      tagline:'Every premium plugin for one site.',
      cta:'Get Complete Suite',
      url:'<?php echo $pro; ?>',
      popular:true,
      features:[
        {t:'1 website', on:true},
        {t:'1 year of updates', on:true},
        {t:'1 year of support', on:true},
        {t:'All 10 premium plugins included', on:true},
        {t:'One To One Premium VIP Support', on:true},
        {t:'One Time Payment', on:false},
        {t:'14-days Money Back Guarantee*', on:true},
      ],
      sites:1, allPlugins:true, lifetime:false, priority:false
    },
    {
      id:'wow',
      name:'Ultimate',
      sub:'Agency & Lifetime',
      price:'$149',
      per:'one-time',
      tagline:'Everything, forever, on up to 3 sites.',
      cta:'Get Agency & Lifetime',
      url:'<?php echo $ultimate; ?>',
      popular:false,
      features:[
        {t:'3 websites', on:true},
        {t:'Lifetime updates', on:true},
        {t:'1 year of support', on:true},
        {t:'All 10 premium plugins included', on:true},
        {t:'One To One Premium VIP Support', on:true},
        {t:'One Time Payment', on:true},
        {t:'14-days Money Back Guarantee*', on:true},
      ],
      sites:3, allPlugins:true, lifetime:true, priority:true
    }
  ],

  plugins:[
    {name:'Advance Search Pro',     desc:'AJAX + voice product search',         price:49, clr:'#7B68EE'},
    {name:'Store One',              desc:'All-in-one WooCommerce toolkit',       price:99, clr:'#F4712E',
      addons:[
        'Product Bundle','Frequently Bought Together','Product Features / List',
        'Quick Social Share','Trust Badges','Product Video Gallery','Sale Notification',
        'Sticky Cart Bar','Buy Now Button','Inactive Tab Message','Stock Scarcity',
        'Sale Countdown','Recently Viewed','Smart Offers (BOGO)','Visitor Count',
        'Badge Management','Pre Order'
      ]},
    {name:'Vayu Blocks',            desc:'Custom Gutenberg blocks & addons',    price:89, clr:'#5B49F0',
      addons:[
        'Container','Button','Heading','Spacer','Product','Postgrid',
        'FlipBox','Image','Video','Icon','AdvanceSlider','AdvanceQueryLoop',
        'ImageHotspot','AdvanceTimeline','Blurb','Unfold','PostPagination','Lottie','Faq','TableOfContents'
      ]},
    {name:'Elemento Addons Pro',    desc:'Premium Elementor widgets',            price:69, clr:'#9C6FE4',
      addons:[
        'Header & Footer Builder','Image Animation','Advance Heading','Content Switcher',
        'Advance Tabs','Image Compare','Image Pointer','Countdown','Elemento Counter',
        'Icon List','Testimonials','Advance Slider','Blog Posts','Price Box',
        'Advance Product Slider','Elemento Products','Woo Coupon','Add to Cart',
        'Product Category Slider','Product Slider','Big Products',
        'Vertical Product List','Grid Product','Product Slider List'
      ]},
    {name:'All in One Woo Cart Pro',desc:'Floating side cart & upsells',         price:49, clr:'#1FA97A'},
    {name:'Product Compare Pro',    desc:'Side-by-side comparison tables',       price:39, clr:'#2196F3'},
    {name:'Lead Form Builder Pro',  desc:'Unlimited forms & lead capture',        price:49, clr:'#FF6B5C'},
    {name:'WP Popup Builder Pro',   desc:'50+ module drag-and-drop popups',       price:49, clr:'#8B5CF6'},
    {name:'Variation Swatches Pro', desc:'Color, image & size swatches',          price:39, clr:'#0EA5E9'},
    {name:'Wishlist for Woo',       desc:'Save-for-later & shareable lists',      price:39, clr:'#5B49F0'},
  ]
};

/* ---------- set featured plugin dynamically from shortcode param ---------- */
const featuredPlugin = <?php echo wp_json_encode( $default_plugin ); ?>;
CONFIG.plugins.forEach(pl => { pl.featured = (pl.name === featuredPlugin); });

/* ---------- render pricing cards ---------- */
const grid = document.getElementById('thp-grid');
const check = '<svg class="thp-ic" width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
const cross = '<svg class="thp-ic thp-off" width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>';

CONFIG.plans.forEach(p=>{
  const card=document.createElement('div');
  card.className='thp-card'+(p.popular?' thp-pop':'');
  card.id='thp-card-'+p.id;
  const pickerHTML = p.picker ? `
    <div class="thp-picker">
      <label for="thp-pick-${p.id}">Choose your plugin</label>
      <select id="thp-pick-${p.id}" class="thp-plugin-select">
        ${CONFIG.plugins.map(pl=>`<option>${pl.name}</option>`).join('')}
      </select>
    </div>` : '';
  card.innerHTML=`
    ${p.popular?'<span class="thp-tag">Most popular</span>':''}
    <p class="thp-plan">${p.name} · ${p.sub}</p>
    <div class="thp-price"><span class="thp-amt">${p.price}</span><span class="thp-per">${p.per}</span></div>
    <p class="thp-tagline">${p.tagline}</p>
    <a class="thp-cta" href="${p.url}">${p.cta}</a>
    <ul class="thp-feat">
      ${p.features.map(f=>`<li class="${f.on?'':'thp-off'}">${f.on?check:cross}<span>${f.t}</span></li>`).join('')}
    </ul>
    ${pickerHTML}`;
  grid.appendChild(card);
});

/* ---------- plugin picker: pass chosen plugin into the CTA ---------- */
CONFIG.plans.filter(p=>p.picker).forEach(p=>{
  const sel=document.getElementById('thp-pick-'+p.id);
  const cta=document.querySelector('#thp-card-'+p.id+' .thp-cta');
  if(sel&&cta){
    const update=()=>{
      cta.textContent=`Get ${sel.value}`;
      const u=new URL(p.url, location.href);
      u.searchParams.set('plugin', sel.value);
      cta.setAttribute('href', p.url==='#' ? '#' : u.toString());
    };
    sel.addEventListener('change', update);
    update();
  }
});

const tbl=document.getElementById('thp-plug-table');
const tickSm='<svg viewBox="0 0 24 24" width="18" height="18" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
const crossSm='<svg viewBox="0 0 24 24" width="16" height="16" fill="none"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>';

// header: Plugin + one column per plan
const head = `<thead><tr>
    <th class="thp-th-plug">Plugin</th>
    ${CONFIG.plans.map(p=>`<th class="thp-th-plan${p.popular?' thp-hot':''}">
        <span class="thp-tp-name">${p.name.split(' ')[0]}</span>
        <span class="thp-tp-price">${p.price}${p.per.startsWith('/')?p.per:' '+p.per}</span>
      </th>`).join('')}
  </tr></thead>`;

// SVG icons loaded from assets/svg/ via PHP
const pluginIcons  = <?php echo $icons_json; ?>;
const addonIcons   = <?php echo $addon_icons_json; ?>;

// body: one row per plugin
const body = '<tbody>' + CONFIG.plugins.map((pl,i)=>{
  const icon = pluginIcons[pl.name] || pl.name.split(' ').slice(0,2).map(w=>w[0]).join('');
  const mods = pl.addons && pl.addons.length
    ? `<button class="thp-view-mods" data-i="${i}">View ${pl.addons.length} modules
         <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>` : '';
  const cells = CONFIG.plans.map(p=>{
    const inc = !!p.allPlugins || !!pl.featured;
    return `<td class="thp-cell ${inc?'thp-yes':'thp-no'}${p.popular?' thp-hot':''}${pl.featured?' thp-feat-cell':''}">${inc?tickSm:crossSm}</td>`;
  }).join('');
  const clr = pl.clr || 'var(--thp-primary)';
  const dotStyle = pl.featured ? '' : `background:${clr}18;color:${clr}`;
  const dotClass = 'thp-dot' + (pl.featured ? ' thp-dot-feat' : '');
  return `<tr${pl.featured?' class="thp-feat-row"':''}>
      <td class="thp-td-plug${pl.featured?' thp-feat-cell':''}">
        <span class="${dotClass}" style="${dotStyle}">${icon}</span>
        <span class="thp-td-info"><b>${pl.name}</b><small>${pl.desc}</small>${mods}</span>
      </td>${cells}
    </tr>`;
}).join('') + '</tbody>';

// footer: a Buy Now button per plan column
const foot = `<tfoot><tr>
    <td class="thp-td-foot-label">Choose your plan</td>
    ${CONFIG.plans.map(p=>`<td class="thp-td-buy${p.popular?' thp-hot':''}">
        <a class="thp-buy-now${p.popular?' thp-filled':''}" href="${p.url}">Buy Now</a>
      </td>`).join('')}
  </tr></tfoot>`;

tbl.innerHTML = head + body + foot;

/* ---------- savings banner: total-if-separate vs each bundle ---------- */
(function(){
  const sep = CONFIG.plugins.reduce((s,p)=>s+(p.price||0),0);
  if(!sep){ return; }
  const bundles = CONFIG.plans.filter(p=>p.allPlugins);
  if(!bundles.length){ return; }

  const rows = bundles.map(b=>{
    const num = parseInt((b.price||'').replace(/[^0-9]/g,''),10) || 0;
    const save = sep - num;
    const pct = Math.round(save/sep*100);
    return `
      <div class="thp-bundle-row">
        <div class="thp-blk">
          <span class="thp-lbl">With ${b.name}${b.lifetime?' · pay once':''}</span>
          <span class="thp-bundle-val">${b.price}<small>${b.per}</small></span>
        </div>
        <span class="thp-save-pill ${b.lifetime?'thp-gold':''}">Save $${save} (${pct}%)</span>
      </div>`;
  }).join('');

  document.getElementById('thp-savings').innerHTML = `
    <div class="thp-sep-blk">
      <span class="thp-lbl">All 10 bought separately</span>
      <span class="thp-sep-val">$${sep}/yr</span>
    </div>
    <svg class="thp-arrow" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <div class="thp-bundle-rows">${rows}</div>`;
})();

/* ---------- addon modal ---------- */
document.addEventListener('click', e=>{
  const btn=e.target.closest('.thp-view-mods');
  if(btn){ openMods(CONFIG.plugins[btn.dataset.i]); }
  if(e.target.classList.contains('thp-modal-back')||e.target.closest('.thp-modal-x')) closeMods();
});
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeMods(); });

function openMods(pl){
  const back=document.getElementById('thp-modal');
  document.getElementById('thp-modal-title').textContent=pl.name;
  document.getElementById('thp-modal-sub').textContent=`${pl.addons.length} modules included — all unlocked with Complete Suite & Lifetime Agency.`;
  const clr = pl.clr || 'var(--thp-primary)';
  document.getElementById('thp-modal-grid').innerHTML=pl.addons.map((a,n)=>{
    const svgIcon = addonIcons[a];
    const badge = svgIcon
      ? `<span class="thp-mod-icon" style="background:${clr}18;color:${clr}">${svgIcon}</span>`
      : `<span class="thp-mod-icon" style="background:${clr}18;color:${clr}"><svg viewBox="0 0 24 24" fill="none" width="16" height="16"><text x="12" y="17" text-anchor="middle" font-size="11" font-weight="700" fill="currentColor" font-family="Space Grotesk,sans-serif">${String(n+1).padStart(2,'0')}</text></svg></span>`;
    return `<div class="thp-mod">${badge}${a}</div>`;
  }).join('');
  back.classList.add('thp-open');
}
function closeMods(){ document.getElementById('thp-modal').classList.remove('thp-open'); }

    });
    </script>
    <?php
  }
  ?>

  <div class="gkit-pricing-wrapper">

  <div class="thp-wrap">

  <p class="thp-eyebrow">Simple Pricing Plan</p>
  <h2>One license. <span class="thp-h2">Every premium plugin</span> you'll ever need.</h2>
  <p class="thp-sub">Pick the plan that fits — one plugin, the full suite, or everything for life.</p>

  <!-- ===================== PRICING GRID ===================== -->
  <div class="thp-grid" id="thp-grid"><!-- cards injected --></div>

  <!-- ===================== INCLUDED PLUGINS ===================== -->
  <section class="thp-included">
    <h3>Included in Complete &amp; Lifetime Agency</h3>
    <p class="thp-lead">All 10 premium plugins below are unlocked — no add-ons to buy separately.</p>
    <div class="thp-savings" id="thp-savings"></div>
    <div class="thp-plug-table-wrap">
      <table class="thp-plug-table" id="thp-plug-table"><!-- rows injected --></table>
    </div>
  </section>

  <p class="thp-foot">Prices in USD. Renewals optional — your plugins keep working after expiry.</p>
</div>

<!-- ===================== ADDON MODAL ===================== -->
<div class="thp-modal-back" id="thp-modal">
  <div class="thp-modal" role="dialog" aria-modal="true" aria-labelledby="thp-modal-title">
    <button class="thp-modal-x" aria-label="Close">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
    </button>
    <h3 id="thp-modal-title"></h3>
    <p id="thp-modal-sub"></p>
    <div class="thp-mod-grid" id="thp-modal-grid"></div>
  </div>
</div>

  </div>

  <?php
  return ob_get_clean();
}




add_shortcode( 'th_pricing_html', 'th_pricing_html_shortcode' );


function th_vayu_blocks_logo_shortcode( $atts ) {
  $atts = shortcode_atts( [ 'size' => '40', 'color' => '#5B49F0' ], $atts );
  $s    = esc_attr( $atts['size'] );
  $c    = esc_attr( $atts['color'] );
  return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="' . $s . '" height="' . $s . '" viewBox="0 0 30 30" preserveAspectRatio="xMidYMid meet" style="display:inline-block;vertical-align:middle"><path fill="' . $c . '" d="M 15.863281 12.226562 L 11.957031 18.996094 L 10.207031 15.960938 C 8.945312 16.621094 7.75 17.066406 6.675781 17.140625 L 11.957031 26.285156 L 22.59375 7.851562 C 20.605469 8.734375 18.253906 10.484375 15.863281 12.226562"/><path fill="' . $c . '" d="M 8.578125 13.140625 L 6.367188 9.316406 L 2.160156 9.316406 L 5.238281 14.648438 C 6.203125 14.460938 7.339844 13.902344 8.578125 13.140625"/></svg>';
}
add_shortcode( 'vayu_blocks_logo', 'th_vayu_blocks_logo_shortcode' );
