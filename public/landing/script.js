const WHATSAPP_NUMBER = "8801994433400";

const DEMO_LOGIN = {
  url: window.ONEPOS_LOGIN_URL || "/login",
  email: "superadmin@gmail.com",
  password: "12345678",
};

const demoModal = document.getElementById("demoModal");
const demoOpenLogin = document.getElementById("demoOpenLogin");

function openDemoModal() {
  if (!demoModal) return;
  demoModal.hidden = false;
  document.body.classList.add("demo-modal-open");
}

function closeDemoModal() {
  if (!demoModal) return;
  demoModal.hidden = true;
  document.body.classList.remove("demo-modal-open");
}

// Request a Demo → show credentials (POS login does not read URL query params yet)
document.querySelectorAll(".demo-login-btn").forEach((btn) => {
  btn.addEventListener("click", (event) => {
    event.preventDefault();
    openDemoModal();
  });
});

if (demoModal) {
  demoModal.querySelectorAll("[data-demo-close]").forEach((el) => {
    el.addEventListener("click", closeDemoModal);
  });
}

document.querySelectorAll(".demo-copy").forEach((btn) => {
  btn.addEventListener("click", async () => {
    const input = document.getElementById(btn.dataset.copy);
    if (!input) return;
    const i18n = window.OnePOS_I18N;
    const copyLabel = () => (i18n ? i18n.t("js.copy") : "Copy");
    const copiedLabel = () => (i18n ? i18n.t("js.copied") : "Copied");
    try {
      await navigator.clipboard.writeText(input.value);
      btn.textContent = copiedLabel();
      setTimeout(() => { btn.textContent = copyLabel(); }, 1400);
    } catch (_) {
      input.select();
      document.execCommand("copy");
    }
  });
});

if (demoOpenLogin) {
  demoOpenLogin.addEventListener("click", () => {
    // Keep query params in case POS later supports autofill
    const params = new URLSearchParams({
      email: DEMO_LOGIN.email,
      password: DEMO_LOGIN.password,
    });
    demoOpenLogin.href = `${DEMO_LOGIN.url}?${params.toString()}`;
  });
}

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && demoModal && !demoModal.hidden) closeDemoModal();
});

// Mobile navigation
const navToggle = document.querySelector('.nav-toggle');
const navLinks = document.querySelector('.nav-links');

if (navToggle && navLinks) {
  navToggle.addEventListener('click', () => {
    const isOpen = navLinks.classList.toggle('open');
    navToggle.setAttribute('aria-expanded', String(isOpen));
  });

  navLinks.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      navLinks.classList.remove('open');
      navToggle.setAttribute('aria-expanded', 'false');
    });
  });
}

// Package tabs (Monthly / One-Time)
const packageTabs = document.querySelectorAll('[data-package-tab]');
const packagePanels = document.querySelectorAll('[data-package-panel]');
packageTabs.forEach(tab => {
  tab.addEventListener('click', () => {
    const target = tab.dataset.packageTab;
    packageTabs.forEach(t => {
      const on = t.dataset.packageTab === target;
      t.classList.toggle('is-active', on);
      t.setAttribute('aria-selected', String(on));
    });
    packagePanels.forEach(panel => {
      const on = panel.dataset.packagePanel === target;
      panel.classList.toggle('is-active', on);
      if (on) panel.removeAttribute('hidden');
      else panel.setAttribute('hidden', '');
    });
  });
});

// Package card dropdowns
document.querySelectorAll('.pkg-drop-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const drop = btn.closest('.pkg-drop');
    const body = drop.querySelector('.pkg-drop-body');
    const willOpen = !drop.classList.contains('is-open');
    drop.classList.toggle('is-open', willOpen);
    btn.setAttribute('aria-expanded', String(willOpen));
    if (willOpen) body.removeAttribute('hidden');
    else body.setAttribute('hidden', '');
  });
});

// Package WhatsApp buttons — full package details
document.querySelectorAll('.package-btn').forEach(button => {
  button.addEventListener('click', () => {
    const card = button.closest('.price-card');
    if (!card) return;

    const i18n = window.OnePOS_I18N;
    const tr = (key, fallback) => (i18n ? i18n.t(key) : fallback);

    const packageName = card.dataset.package || tr("js.fallbackPackage", "OnePOS Package");
    const billing = card.dataset.billing || "";
    const regular = card.dataset.regular || "";
    const offer = card.dataset.offer || "";
    const monthly = card.dataset.monthly || "";

    const groups = [...card.querySelectorAll("[data-wa-group]")].map((list) => {
      const title = list.dataset.waGroup;
      const items = [...list.querySelectorAll("li")]
        .map((li) => `• ${li.textContent.trim()}`)
        .join("\n");
      return `${title}:\n${items}`;
    }).join("\n\n");

    const message = [
      tr("js.hello", "Hello ONETECH,"),
      "",
      `${tr("js.interested", "I am interested in:")} ${packageName} (${billing})`,
      `${tr("js.offerPrice", "Offer price:")} ${offer}`,
      `${tr("js.regular", "Regular:")} ${regular}`,
      monthly ? `${tr("js.notes", "Notes:")} ${monthly}` : "",
      "",
      "---",
      groups,
      "---",
      "",
      tr("js.closing", "Please share the next steps and demo details."),
    ].filter(Boolean).join("\n");

    const url = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(message)}`;
    window.open(url, '_blank', 'noopener,noreferrer');
  });
});

// Scroll reveal
const revealItems = document.querySelectorAll(".reveal");
const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add("visible");
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.12 });

revealItems.forEach((item) => observer.observe(item));

// Hero above-the-fold: start animation promptly on load
const heroAnimate = document.querySelector(".hero-animate");
if (heroAnimate) {
  requestAnimationFrame(() => {
    heroAnimate.classList.add("visible");
  });
}

// Hero headline: O-N-E-P-O-S letter reveal → ONEPOS → subtitle → loop
(function initHeroTypewriter() {
  const root = document.querySelector("[data-hero-typewriter]");
  if (!root) return;

  const titleEl = root.querySelector(".hero-type-title");
  const subEl = root.querySelector(".hero-type-sub");
  if (!titleEl || !subEl) return;

  let timer = null;
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

  const t = (key, fallback) =>
    window.OnePOS_I18N ? window.OnePOS_I18N.t(key) : fallback;

  const clearTimer = () => {
    if (timer) {
      clearTimeout(timer);
      timer = null;
    }
  };

  const buildLetters = (text) => {
    titleEl.textContent = "";
    titleEl.classList.remove("is-assembled");
    return [...String(text)].map((ch) => {
      const span = document.createElement("span");
      span.className = "hero-letter";
      span.textContent = ch;
      titleEl.appendChild(span);
      return span;
    });
  };

  const showStatic = () => {
    clearTimer();
    titleEl.textContent = t("hero.title", "ONEPOS");
    titleEl.classList.add("is-assembled");
    subEl.textContent = t("hero.titleSpan", "Built around your business.");
    subEl.classList.add("is-on");
  };

  const runCycle = () => {
    clearTimer();
    subEl.classList.remove("is-on");
    subEl.textContent = t("hero.titleSpan", "Built around your business.");

    if (reduceMotion.matches) {
      showStatic();
      return;
    }

    const letters = buildLetters(t("hero.title", "ONEPOS"));
    let index = 0;

    const showNextLetter = () => {
      if (index < letters.length) {
        letters[index].classList.add("is-on");
        index += 1;
        timer = setTimeout(showNextLetter, 280);
        return;
      }

      // Letters assembled into ONEPOS
      titleEl.classList.add("is-assembled");

      timer = setTimeout(() => {
        subEl.classList.add("is-on");

        // Hold, then reset and loop
        timer = setTimeout(() => {
          subEl.classList.remove("is-on");
          letters.forEach((el) => el.classList.remove("is-on"));
          titleEl.classList.remove("is-assembled");
          timer = setTimeout(runCycle, 500);
        }, 2800);
      }, 450);
    };

    timer = setTimeout(showNextLetter, 180);
  };

  timer = setTimeout(runCycle, 200);
  document.documentElement.addEventListener("onepos:langchange", runCycle);
  reduceMotion.addEventListener("change", runCycle);
})();

// Feature explorer (category nav + detail panels)
const featureExplorer = document.querySelector("[data-feature-explorer]");
if (featureExplorer) {
  const buttons = [...featureExplorer.querySelectorAll(".feature-nav-btn")];
  const panels = [...featureExplorer.querySelectorAll(".feature-panel")];
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

  const animatePanel = (panel) => {
    if (!panel) return;
    panel.classList.remove("is-shown");
    panel.classList.add("is-animating");
    // Force reflow so transition restarts
    void panel.offsetWidth;
    requestAnimationFrame(() => {
      panel.classList.add("is-shown");
      panel.classList.remove("is-animating");
    });
  };

  const showModule = (moduleId) => {
    buttons.forEach((btn) => {
      btn.classList.toggle("is-active", btn.dataset.module === moduleId);
    });
    panels.forEach((panel) => {
      const match = panel.dataset.panel === moduleId;
      panel.classList.toggle("is-active", match);
      if (match) {
        panel.removeAttribute("hidden");
        if (reduceMotion.matches) {
          panel.classList.add("is-shown");
          panel.classList.remove("is-animating");
        } else {
          animatePanel(panel);
        }
      } else {
        panel.setAttribute("hidden", "");
        panel.classList.remove("is-shown", "is-animating");
      }
    });
  };

  buttons.forEach((btn) => {
    btn.addEventListener("click", () => showModule(btn.dataset.module));
  });

  // Initial active panel animation when explorer scrolls into view
  const activePanel = featureExplorer.querySelector(".feature-panel.is-active");
  if (activePanel && !reduceMotion.matches) {
    activePanel.classList.add("is-animating");
    const startInitial = () => {
      if (featureExplorer.classList.contains("visible")) {
        animatePanel(activePanel);
        return true;
      }
      return false;
    };
    if (!startInitial()) {
      const waitVisible = new MutationObserver(() => {
        if (startInitial()) waitVisible.disconnect();
      });
      waitVisible.observe(featureExplorer, { attributes: true, attributeFilter: ["class"] });
    }
  } else if (activePanel) {
    activePanel.classList.add("is-shown");
  }
}

// Section titles: word-by-word reveal on scroll
(function initTitleAnimate() {
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
  const titles = [...document.querySelectorAll("[data-title-animate]")];
  if (!titles.length) return;

  const t = (key, fallback) =>
    window.OnePOS_I18N ? window.OnePOS_I18N.t(key) : fallback;

  const buildWords = (el) => {
    const key = el.getAttribute("data-i18n");
    const text = key ? t(key, el.textContent.trim()) : el.textContent.trim();
    el.classList.remove("is-title-on");
    el.textContent = "";
    text.split(/\s+/).filter(Boolean).forEach((word, index, arr) => {
      const span = document.createElement("span");
      span.className = "title-word";
      span.textContent = word;
      el.appendChild(span);
      if (index < arr.length - 1) el.appendChild(document.createTextNode(" "));
    });
  };

  const play = (el) => {
    buildWords(el);
    if (reduceMotion.matches) {
      el.classList.add("is-title-on");
      return;
    }
    requestAnimationFrame(() => {
      el.classList.add("is-title-on");
    });
  };

  const nearestReveal = (el) => el.closest(".reveal") || el;

  titles.forEach((el) => {
    buildWords(el);
    const host = nearestReveal(el);
    const start = () => {
      if (host.classList.contains("visible") || !host.classList.contains("reveal")) {
        play(el);
        return true;
      }
      return false;
    };

    if (!start()) {
      const mo = new MutationObserver(() => {
        if (start()) mo.disconnect();
      });
      mo.observe(host, { attributes: true, attributeFilter: ["class"] });
    }
  });

  document.documentElement.addEventListener("onepos:langchange", () => {
    titles.forEach((el) => {
      const host = nearestReveal(el);
      if (host.classList.contains("visible") || !host.classList.contains("reveal")) {
        play(el);
      } else {
        buildWords(el);
      }
    });
  });
})();

// Current year
const yearEl = document.getElementById("year");
if (yearEl) yearEl.textContent = new Date().getFullYear();

// Brand / logo → top
document.querySelectorAll('a[href="#top"]').forEach((link) => {
  link.addEventListener("click", (event) => {
    event.preventDefault();
    window.scrollTo({ top: 0, behavior: "smooth" });
    try {
      history.replaceState(null, "", "#top");
    } catch (_) { /* ignore */ }
  });
});

// Floating back-to-top (above WhatsApp)
const floatingTop = document.getElementById("floatingTop");
if (floatingTop) {
  const toggleFloatingTop = () => {
    floatingTop.hidden = window.scrollY < 320;
  };
  toggleFloatingTop();
  window.addEventListener("scroll", toggleFloatingTop, { passive: true });
  floatingTop.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });
}
