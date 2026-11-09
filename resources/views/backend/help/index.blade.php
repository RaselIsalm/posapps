@extends('backend.layout.main')

@section('title', 'Help Guide & FAQ')

@push('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Baloo+Da+2:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .help-guide-page {
        --help-en-font: "Plus Jakarta Sans", system-ui, sans-serif;
        --help-bn-font: "Baloo Da 2", "Noto Sans Bengali", sans-serif;
        font-size: 16px;
        line-height: 1.65;
    }
    /* Beat global layout font-family !important on h1–h6, p, li, a, button, input */
    .help-guide-page[data-lang="en"],
    .help-guide-page[data-lang="en"] *:not(i):not(svg):not(use) {
        font-family: var(--help-en-font) !important;
    }
    .help-guide-page[data-lang="bn"],
    .help-guide-page[data-lang="bn"] *:not(i):not(svg):not(use) {
        font-family: var(--help-bn-font) !important;
    }
    .help-guide-page[data-lang="en"] {
        font-size: 16.5px;
    }
    .help-guide-page[data-lang="bn"] {
        font-size: 18px;
        line-height: 1.7;
    }
    .help-guide-page [data-lang-panel="en"] {
        font-size: 1em;
    }
    .help-guide-page [data-lang-panel="bn"] {
        font-size: 1.06em;
        line-height: 1.7;
    }
    .help-guide-page h3 {
        font-size: 1.75rem;
        font-weight: 700;
        letter-spacing: 0.01em;
    }
    .help-guide-page h5 {
        font-size: 1.2rem;
        font-weight: 700;
    }
    .help-guide-page h6 {
        font-size: 1.05rem;
        font-weight: 700;
    }
    .help-guide-page .help-hero p,
    .help-guide-page [data-i18n="heroSub"] {
        font-size: 1.05rem;
    }
    .help-guide-page .help-module-card p,
    .help-guide-page .help-step-list li,
    .help-guide-page .accordion-body {
        font-size: 1.02rem;
    }
    .help-guide-page .faq-item .accordion-button {
        font-size: 1.08rem;
        line-height: 1.5;
    }
    .help-guide-page .help-search {
        font-size: 15px;
    }

    .help-hero {
        background: linear-gradient(135deg, var(--primary-color) 0%, color-mix(in srgb, var(--primary-color) 75%, #000) 100%);
        border-radius: 16px;
        color: #fff;
        padding: 28px 32px;
    }
    .help-lang {
        background: #fff;
        border-radius: 8px;
        padding: 2px;
        box-shadow: 0 1px 4px rgba(0,0,0,.12);
    }
    .help-lang .btn {
        color: #1f2937 !important;
        background: transparent !important;
        border: 0 !important;
        font-weight: 600;
        font-size: 14px !important;
        box-shadow: none !important;
    }
    .help-lang .btn:hover {
        color: var(--primary-color) !important;
        background: color-mix(in srgb, var(--primary-color) 10%, #fff) !important;
    }
    .help-lang .btn.active {
        background: var(--primary-color) !important;
        color: #fff !important;
    }
    .help-module-card {
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 18px;
        height: 100%;
        background: #fff;
        transition: box-shadow .2s ease, transform .2s ease;
    }
    .help-module-card:hover {
        box-shadow: 0 10px 24px rgba(0,0,0,.06);
        transform: translateY(-2px);
    }
    .help-module-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: color-mix(in srgb, var(--primary-color) 12%, #fff);
        color: var(--primary-color);
    }
    .help-step-list {
        counter-reset: helpstep;
        list-style: none;
        padding-left: 0;
        margin: 0;
    }
    .help-step-list li {
        counter-increment: helpstep;
        position: relative;
        padding: 12px 12px 12px 48px;
        border-bottom: 1px solid #f1f1f1;
    }
    .help-step-list li:last-child { border-bottom: 0; }
    .help-step-list li::before {
        content: counter(helpstep);
        position: absolute;
        left: 8px;
        top: 12px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--primary-color);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--help-en-font);
    }
    .faq-item {
        border: 1px solid #eee;
        border-radius: 10px;
        margin-bottom: 10px;
        overflow: hidden;
        background: #fff;
    }
    .faq-item .accordion-button {
        font-weight: 600;
        box-shadow: none !important;
        background: #fff;
    }
    .faq-item .accordion-button:not(.collapsed) {
        color: var(--primary-color);
        background: color-mix(in srgb, var(--primary-color) 8%, #fff);
    }
    .help-search {
        max-width: 420px;
    }
    [data-lang-panel].d-none { display: none !important; }
    @media (max-width: 767.98px) {
        .help-hero { padding: 18px 16px; }
        .help-search { max-width: 100%; }
        .help-hero .btn { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid help-guide-page" id="help-guide-page" data-lang="en">
    <div class="help-hero mb-4" id="help-guide-root"
         data-tour-en='@json($tourCopy["en"])'
         data-tour-bn='@json($tourCopy["bn"])'
         data-help-url="{{ route('help.index') }}">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h3 class="mb-2 text-white" data-i18n="heroTitle">Help Guide & FAQ</h3>
                <p class="mb-0 opacity-75" data-i18n="heroSub">
                    Learn where every POS module lives and how it works — English & বাংলা.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <div class="btn-group help-lang" role="group">
                    <button type="button" class="btn btn-light btn-sm active" data-set-lang="en" style="font-family: var(--help-en-font);">English</button>
                    <button type="button" class="btn btn-light btn-sm" data-set-lang="bn" style="font-family: var(--help-bn-font);">বাংলা</button>
                </div>
                <button type="button" class="btn btn-warning btn-sm fw-bold" id="help-start-tour">
                    <i class="fa-solid fa-route me-1"></i> <span data-i18n="startTour">Start UI Tour</span>
                </button>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="mb-3" data-lang-panel="en">{{ $gettingStarted['en']['title'] }}</h5>
                    <h5 class="mb-3 d-none" data-lang-panel="bn">{{ $gettingStarted['bn']['title'] }}</h5>
                    <ul class="help-step-list" data-lang-panel="en">
                        @foreach ($gettingStarted['en']['steps'] as $step)
                            <li>{{ $step }}</li>
                        @endforeach
                    </ul>
                    <ul class="help-step-list d-none" data-lang-panel="bn">
                        @foreach ($gettingStarted['bn']['steps'] as $step)
                            <li>{{ $step }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h5 class="mb-0" data-i18n="modulesTitle">All Modules — Where & How</h5>
                        <input type="search" class="form-control form-control-sm help-search" id="help-module-search"
                               placeholder="Search modules..." data-i18n-placeholder="searchModules">
                    </div>
                    <div class="row g-3" id="help-modules">
                        @foreach ($modules as $module)
                            <div class="col-md-6 help-module-item" data-search="{{ strtolower(($module['en']['title'] ?? '').' '.($module['bn']['title'] ?? '').' '.($module['en']['what'] ?? '')) }}">
                                <div class="help-module-card">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="help-module-icon"><i class="fa-solid {{ $module['icon'] }}"></i></span>
                                        <div>
                                            <div class="fw-bold" data-lang-panel="en">{{ $module['en']['title'] }}</div>
                                            <div class="fw-bold d-none" data-lang-panel="bn">{{ $module['bn']['title'] }}</div>
                                            @if (!empty($module['route']) && \Illuminate\Support\Facades\Route::has($module['route']))
                                                <a href="{{ route($module['route']) }}" class="small text-primary">Open →</a>
                                            @endif
                                        </div>
                                    </div>
                                    <div data-lang-panel="en">
                                        <p class="mb-1 small text-muted"><strong>Where:</strong> {{ $module['en']['where'] }}</p>
                                        <p class="mb-1 small"><strong>What:</strong> {{ $module['en']['what'] }}</p>
                                        <p class="mb-0 small"><strong>How:</strong> {{ $module['en']['how'] }}</p>
                                    </div>
                                    <div class="d-none" data-lang-panel="bn">
                                        <p class="mb-1 small text-muted"><strong>কোথায়:</strong> {{ $module['bn']['where'] }}</p>
                                        <p class="mb-1 small"><strong>কী:</strong> {{ $module['bn']['what'] }}</p>
                                        <p class="mb-0 small"><strong>কীভাবে:</strong> {{ $module['bn']['how'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3" data-i18n="faqTitle">Frequently Asked Questions</h5>
            @foreach ($faqs as $fi => $group)
                <h6 class="text-muted mt-3 mb-2" data-lang-panel="en">{{ $group['category']['en'] }}</h6>
                <h6 class="text-muted mt-3 mb-2 d-none" data-lang-panel="bn">{{ $group['category']['bn'] }}</h6>
                <div class="accordion" id="faq-group-{{ $fi }}">
                    @foreach ($group['items'] as $qi => $item)
                        <div class="faq-item accordion-item border-0">
                            <h2 class="accordion-header" id="faq-h-{{ $fi }}-{{ $qi }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq-c-{{ $fi }}-{{ $qi }}">
                                    <span data-lang-panel="en">{{ $item['en']['q'] }}</span>
                                    <span class="d-none" data-lang-panel="bn">{{ $item['bn']['q'] }}</span>
                                </button>
                            </h2>
                            <div id="faq-c-{{ $fi }}-{{ $qi }}" class="accordion-collapse collapse"
                                 data-bs-parent="#faq-group-{{ $fi }}">
                                <div class="accordion-body">
                                    <div data-lang-panel="en">{{ $item['en']['a'] }}</div>
                                    <div class="d-none" data-lang-panel="bn">{{ $item['bn']['a'] }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
(function () {
    const root = document.getElementById('help-guide-root');
    if (!root) return;

    const ui = {
        en: {
            heroTitle: 'Help Guide & FAQ',
            heroSub: 'Learn where every POS module lives and how it works — English & বাংলা.',
            startTour: 'Start UI Tour',
            modulesTitle: 'All Modules — Where & How',
            searchModules: 'Search modules...',
            faqTitle: 'Frequently Asked Questions',
        },
        bn: {
            heroTitle: 'সাহায্য ও প্রশ্নোত্তর',
            heroSub: 'প্রতিটা পিওএস মডিউল কোথায় আছে আর কীভাবে কাজ করে — ইংরেজি ও বাংলা।',
            startTour: 'গাইড দেখুন',
            modulesTitle: 'সব মডিউল — কোথায় ও কীভাবে',
            searchModules: 'মডিউল খুঁজুন...',
            faqTitle: 'সাধারণ প্রশ্নোত্তর',
        }
    };

    function setLang(lang) {
        localStorage.setItem('pos_help_lang', lang);
        var page = document.getElementById('help-guide-page');
        if (page) page.setAttribute('data-lang', lang);
        document.querySelectorAll('[data-set-lang]').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-set-lang') === lang);
        });
        document.querySelectorAll('[data-lang-panel]').forEach(el => {
            el.classList.toggle('d-none', el.getAttribute('data-lang-panel') !== lang);
        });
        const copy = ui[lang] || ui.en;
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (copy[key]) el.textContent = copy[key];
        });
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            if (copy[key]) el.setAttribute('placeholder', copy[key]);
        });
        window.PosTourLang = lang;
    }

    document.querySelectorAll('[data-set-lang]').forEach(btn => {
        btn.addEventListener('click', () => setLang(btn.getAttribute('data-set-lang')));
    });

    const search = document.getElementById('help-module-search');
    if (search) {
        search.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.help-module-item').forEach(card => {
                const hay = card.getAttribute('data-search') || '';
                card.style.display = !q || hay.indexOf(q) !== -1 ? '' : 'none';
            });
        });
    }

    document.getElementById('help-start-tour')?.addEventListener('click', function () {
        if (window.PosTour && typeof window.PosTour.start === 'function') {
            window.PosTour.start(localStorage.getItem('pos_help_lang') || 'en');
        }
    });

    setLang(localStorage.getItem('pos_help_lang') || 'en');
})();
</script>
@endpush
