(() => {
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const q = (selector, parent = document) => parent.querySelector(selector);
  const qa = (selector, parent = document) => [...parent.querySelectorAll(selector)];
  const slides = qa('.slide');
  const dots = q('.dots');
  const hero = q('.hero');
  const toggle = q('.slider-toggle');
  let index = 0;
  let timer;
  let paused = reduced;

  function show(next) {
    if (!slides.length) return;
    index = (next + slides.length) % slides.length;
    slides.forEach((slide, i) => {
      const active = i === index;
      slide.classList.toggle('active', active);
      slide.setAttribute('aria-hidden', String(!active));
    });
    qa('button', dots).forEach((button, i) => {
      button.classList.toggle('active', i === index);
      button.setAttribute('aria-current', i === index ? 'true' : 'false');
    });
    const status = q('[data-slide-status]');
    if (status) status.textContent = `Ảnh ${index + 1} trên ${slides.length}`;
  }

  function start() {
    clearInterval(timer);
    if (!paused && !document.hidden && slides.length > 1) timer = setInterval(() => show(index + 1), 6500);
  }

  function setPaused(value) {
    paused = value;
    if (toggle) {
      toggle.setAttribute('aria-pressed', String(paused));
      toggle.setAttribute('aria-label', paused ? 'Tiếp tục trình chiếu' : 'Tạm dừng trình chiếu');
      const icon = q('i', toggle);
      if (icon) icon.className = `fi fi-rr-${paused ? 'play' : 'pause'}`;
    }
    start();
  }

  if (dots) {
    slides.forEach((_, i) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.setAttribute('aria-label', `Hiển thị ảnh ${i + 1}`);
      button.addEventListener('click', () => { show(i); start(); });
      dots.append(button);
    });
    show(0);
    q('.prev')?.addEventListener('click', () => { show(index - 1); start(); });
    q('.next')?.addEventListener('click', () => { show(index + 1); start(); });
    toggle?.addEventListener('click', () => setPaused(!paused));
    hero?.addEventListener('mouseenter', () => clearInterval(timer));
    hero?.addEventListener('mouseleave', start);
    hero?.addEventListener('focusin', () => clearInterval(timer));
    hero?.addEventListener('focusout', start);
    document.addEventListener('visibilitychange', start);
    start();
  }

  const count = q('.count');
  if (count) {
    const tick = () => {
      const seconds = Math.max(0, Math.floor((Date.parse(count.dataset.date) - Date.now()) / 1000));
      const values = [Math.floor(seconds / 86400), Math.floor(seconds / 3600) % 24, Math.floor(seconds / 60) % 60, seconds % 60];
      qa('strong', count).forEach((element, i) => { element.textContent = String(values[i]).padStart(2, '0'); });
    };
    tick(); setInterval(tick, 1000);
  }

  if (!reduced && 'IntersectionObserver' in window) {
    document.documentElement.classList.add('js');
    const observer = new IntersectionObserver(entries => entries.forEach(entry => {
      if (entry.isIntersecting) { entry.target.classList.add('visible'); observer.unobserve(entry.target); }
    }), { threshold: .1 });
    qa('.reveal').forEach(element => observer.observe(element));
    const petals = q('.petals');
    if (petals) for (let i = 0; i < (innerWidth < 650 ? 6 : 12); i++) {
      const petal = document.createElement('span'); petal.className = 'petal';
      petal.style.left = `${(i * 37) % 100}%`; petal.style.animationDelay = `-${i * .8}s`; petal.style.animationDuration = `${8 + i % 5}s`; petals.append(petal);
    }
  }

  qa('[data-open]').forEach(button => button.addEventListener('click', () => q(`#${button.dataset.open}`)?.showModal()));
  qa('[data-close]').forEach(button => button.addEventListener('click', () => button.closest('dialog')?.close()));
  qa('dialog').forEach(dialog => dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); }));
  const giftTabs = qa('[data-gift-tab]');
  function selectGift(side) {
    giftTabs.forEach(tab => {
      const selected = tab.dataset.giftTab === side;
      tab.setAttribute('aria-selected', String(selected));
      tab.tabIndex = selected ? 0 : -1;
      const panel = q(`#gift-panel-${tab.dataset.giftTab}`);
      if (panel) panel.hidden = !selected;
    });
  }
  giftTabs.forEach(tab => {
    tab.addEventListener('click', () => selectGift(tab.dataset.giftTab));
    tab.addEventListener('keydown', event => {
      if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
      event.preventDefault();
      const enabled = giftTabs.filter(item => !item.disabled);
      const current = enabled.indexOf(tab);
      const next = event.key === 'Home' ? 0 : event.key === 'End' ? enabled.length - 1 : (current + (event.key === 'ArrowRight' ? 1 : enabled.length - 1)) % enabled.length;
      enabled[next]?.focus();
      if (enabled[next]) selectGift(enabled[next].dataset.giftTab);
    });
  });
  const firstGiftTab = giftTabs.find(tab => !tab.disabled);
  if (firstGiftTab) selectGift(firstGiftTab.dataset.giftTab);
  qa('[data-photo]').forEach(button => button.addEventListener('click', () => { const image = q('#lightbox img'); if (image) image.src = button.dataset.photo; q('#lightbox')?.showModal(); }));
  const music = q('#music');
  const musicButton = q('.music');
  function syncMusicButton() {
    if (!musicButton || !music) return;
    const playing = !music.paused;
    musicButton.setAttribute('aria-pressed', String(playing));
    musicButton.setAttribute('aria-label', playing ? 'Tắt nhạc nền' : 'Phát nhạc nền');
    musicButton.title = playing ? 'Tắt nhạc nền' : 'Phát nhạc nền';
    musicButton.removeAttribute('data-error');
  }
  function showMusicError() {
    if (!musicButton) return;
    musicButton.dataset.error = 'Không tải được nhạc. Kiểm tra kết nối rồi thử lại.';
  }
  music?.addEventListener('play', syncMusicButton);
  music?.addEventListener('pause', syncMusicButton);
  music?.addEventListener('error', showMusicError);
  syncMusicButton();
  q('[data-invite]')?.addEventListener('click', () => { q('.envelope')?.classList.add('open'); music?.play().catch(showMusicError); setTimeout(() => { q('#invite')?.close(); q('#couple')?.scrollIntoView(); q('.envelope')?.classList.remove('open'); }, reduced ? 0 : 700); });
  musicButton?.addEventListener('click', async () => {
    if (!music) return;
    try {
      if (music.paused) await music.play();
      else music.pause();
      syncMusicButton();
    } catch {
      showMusicError();
    }
  });
  q('.menu')?.addEventListener('click', event => { const nav = q('.nav nav'); const open = nav?.classList.toggle('open') ?? false; event.currentTarget.setAttribute('aria-expanded', String(open)); event.currentTarget.setAttribute('aria-label', open ? 'Đóng menu' : 'Mở menu'); });
  qa('.nav nav a').forEach(link => link.addEventListener('click', () => { q('.nav nav')?.classList.remove('open'); q('.menu')?.setAttribute('aria-expanded', 'false'); }));
  const attendance = q('[name=attendance]');
  attendance?.addEventListener('change', () => { q('[name=guests]').disabled = attendance.value === 'no'; });
  setTimeout(() => q('.toast')?.remove(), 6500);
})();
