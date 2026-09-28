/**
 * Musumba Steel — interactive quote configurator (ES6 + fetch).
 * Listens to profile / gauge / finish / colour / length and updates price via calculer_prix.php.
 */
(() => {
  'use strict';

  const root = document.getElementById('quoteConfigurator');
  if (!root) return;

  const catalogEl = document.getElementById('configuratorCatalog');
  let catalog = [];
  try {
    catalog = JSON.parse(catalogEl ? catalogEl.textContent : '[]');
  } catch (_) {
    catalog = [];
  }

  const apiUrl = root.dataset.api || 'calculer_prix.php';
  const profileSelect = document.getElementById('cfgProfile');
  const gaugeSelect = document.getElementById('cfgGauge');
  const finishSelect = document.getElementById('cfgFinish');
  const colorsWrap = document.getElementById('cfgColors');
  const variantInput = document.getElementById('cfgVariantId');
  const lengthInput = document.getElementById('cfgLength');
  const errorEl = document.getElementById('cfgError');
  const priceEl = document.getElementById('cfgPrice');
  const priceMeta = document.getElementById('cfgPriceMeta');
  const colorLabel = document.getElementById('cfgColorLabel');
  const roofFill = document.getElementById('roofFill');
  const form = document.getElementById('configuratorForm');

  let selectedVariantId = null;
  let debounceTimer = null;
  let abortCtrl = null;

  const fmt = new Intl.NumberFormat(undefined, {
    style: 'currency',
    currency: 'TZS',
    maximumFractionDigits: 0,
  });

  function showError(msg) {
    if (!errorEl) return;
    if (!msg) {
      errorEl.hidden = true;
      errorEl.textContent = '';
      return;
    }
    errorEl.hidden = false;
    errorEl.textContent = msg;
  }

  function currentProduct() {
    const id = Number(profileSelect.value);
    return catalog.find((p) => Number(p.id) === id) || null;
  }

  function unique(values) {
    return [...new Set(values.filter(Boolean))];
  }

  function resetSelect(select, placeholder, enabled) {
    select.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = placeholder;
    select.appendChild(opt);
    select.disabled = !enabled;
  }

  function fillGauges(product) {
    resetSelect(gaugeSelect, 'Select gauge', !!product);
    resetSelect(finishSelect, 'Select finish', false);
    colorsWrap.innerHTML = '';
    variantInput.value = '';
    selectedVariantId = null;
    if (!product) return;
    unique(product.variants.map((v) => v.gauge)).forEach((g) => {
      const opt = document.createElement('option');
      opt.value = g;
      opt.textContent = g;
      gaugeSelect.appendChild(opt);
    });
  }

  function fillFinishes(product, gauge) {
    resetSelect(finishSelect, 'Select finish', !!gauge);
    colorsWrap.innerHTML = '';
    variantInput.value = '';
    selectedVariantId = null;
    if (!product || !gauge) return;
    const finishes = unique(
      product.variants.filter((v) => v.gauge === gauge).map((v) => v.finish)
    );
    finishes.forEach((f) => {
      const opt = document.createElement('option');
      opt.value = f;
      opt.textContent = f;
      finishSelect.appendChild(opt);
    });
  }

  function fillColors(product, gauge, finish) {
    colorsWrap.innerHTML = '';
    variantInput.value = '';
    selectedVariantId = null;
    colorLabel.textContent = 'Choose a colour';
    if (!product || !gauge || !finish) return;

    const variants = product.variants.filter(
      (v) => v.gauge === gauge && v.finish === finish
    );

    variants.forEach((v) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'swatch';
      btn.style.setProperty('--swatch', v.color_hex || '#888');
      btn.title = v.color_name;
      btn.setAttribute('role', 'option');
      btn.setAttribute('aria-label', v.color_name);
      btn.dataset.variantId = String(v.id);
      btn.dataset.hex = v.color_hex || '#888888';
      btn.dataset.name = v.color_name;
      btn.addEventListener('click', () => selectColor(btn));
      colorsWrap.appendChild(btn);
    });
  }

  function selectColor(btn) {
    colorsWrap.querySelectorAll('.swatch').forEach((el) => el.classList.remove('active'));
    btn.classList.add('active');
    selectedVariantId = Number(btn.dataset.variantId);
    variantInput.value = String(selectedVariantId);
    colorLabel.textContent = btn.dataset.name || '';
    if (roofFill) {
      roofFill.setAttribute('fill', btn.dataset.hex || '#888888');
      root.classList.add('has-color');
    }
    schedulePrice();
  }

  function validate() {
    const length = Number(lengthInput.value);
    if (!profileSelect.value) {
      return 'Please select a profile.';
    }
    if (!gaugeSelect.value) {
      return 'Please select a gauge.';
    }
    if (!finishSelect.value) {
      return 'Please select a finish.';
    }
    if (!selectedVariantId) {
      return 'Please select a colour.';
    }
    if (!lengthInput.value || Number.isNaN(length)) {
      return 'Please enter a length.';
    }
    if (length <= 0) {
      return 'Length cannot be negative or zero.';
    }
    if (length > 15) {
      return 'Maximum sheet length is 15 metres.';
    }
    return '';
  }

  async function fetchPrice() {
    const err = validate();
    if (err) {
      showError(err);
      priceEl.textContent = '—';
      return;
    }
    showError('');

    if (abortCtrl) abortCtrl.abort();
    abortCtrl = new AbortController();

    priceEl.classList.add('loading');
    try {
      const res = await fetch(apiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({
          product_id: Number(profileSelect.value),
          variant_id: selectedVariantId,
          length: Number(lengthInput.value),
        }),
        signal: abortCtrl.signal,
      });
      const data = await res.json();
      if (!data.ok) {
        showError(data.error || 'Price calculation failed.');
        priceEl.textContent = '—';
        return;
      }
      priceEl.textContent = fmt.format(data.total);
      if (priceMeta && data.breakdown) {
        const b = data.breakdown;
        priceMeta.textContent = `${b.profile_type} · ${b.gauge} · ${b.finish} · ${b.length_meters} m`;
      }
    } catch (e) {
      if (e.name === 'AbortError') return;
      showError('Network error. Please try again.');
      priceEl.textContent = '—';
    } finally {
      priceEl.classList.remove('loading');
    }
  }

  function schedulePrice() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fetchPrice, 220);
  }

  profileSelect.addEventListener('change', () => {
    fillGauges(currentProduct());
    priceEl.textContent = '—';
  });

  gaugeSelect.addEventListener('change', () => {
    fillFinishes(currentProduct(), gaugeSelect.value);
    priceEl.textContent = '—';
  });

  finishSelect.addEventListener('change', () => {
    fillColors(currentProduct(), gaugeSelect.value, finishSelect.value);
    priceEl.textContent = '—';
  });

  lengthInput.addEventListener('input', schedulePrice);
  lengthInput.addEventListener('change', schedulePrice);

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    fetchPrice();
  });
})();
