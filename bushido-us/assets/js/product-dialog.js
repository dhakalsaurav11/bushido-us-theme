/**
 * Product dialog: a card zooms open into a <dialog> over a blurred page.
 * Content comes from the card's <template id="bh-product-ID"> (template-parts/product-dialog.php).
 * Opening pushes the product URL; Back, Esc, the backdrop and Close all close it.
 */
(function () {
	const cfg = window.bushidoDialog || {};
	const dialog = document.querySelector('.bh-dialog');
	if (!dialog || typeof dialog.showModal !== 'function') return;

	const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
	let pushed = false; // true when our pushState is the current history entry
	let closing = false;
	let source = null; // the card figure the dialog zooms from and back to
	let restoreTitle = document.title;
	let step = null; // gallery stepper for the open product, used by the arrow keys

	const panel = () => dialog.querySelector('.bh-pd');

	function zoom(reverse) {
		const p = panel();
		if (!p || reduceMotion.matches || !p.animate) return Promise.resolve();
		const to = p.getBoundingClientRect();
		let frames;
		if (source && source.isConnected) {
			const from = source.getBoundingClientRect();
			frames = [
				{ transformOrigin: '0 0', opacity: 0, transform: `translate(${from.left - to.left}px, ${from.top - to.top}px) scale(${from.width / to.width}, ${from.height / to.height})` },
				{ transformOrigin: '0 0', opacity: 1, transform: 'none' },
			];
		} else {
			frames = [
				{ opacity: 0, transform: 'scale(.96)' },
				{ opacity: 1, transform: 'none' },
			];
		}
		if (reverse) frames.reverse();
		const animation = p.animate(frames, {
			duration: reverse ? 260 : 420,
			easing: 'cubic-bezier(.2, .7, .2, 1)',
			fill: 'both',
		});
		return animation.finished.catch(() => {});
	}

	function open(id, push) {
		const tpl = document.getElementById('bh-product-' + id);
		if (!tpl) return false;
		closing = false;
		dialog.replaceChildren(tpl.content.cloneNode(true));
		const title = dialog.querySelector('.bh-pd-name');
		if (title) dialog.setAttribute('aria-labelledby', title.id);
		source = document.querySelector(`.bh-card a[data-bh-product="${id}"] figure`);
		if (!dialog.open) {
			restoreTitle = document.title;
			dialog.showModal();
		}
		if (push) {
			history.pushState({ bhProduct: id }, '', tpl.dataset.url);
			pushed = true;
		}
		document.title = tpl.dataset.title + (cfg.siteName ? ' – ' + cfg.siteName : '');
		setup(panel());
		zoom(false);
		return true;
	}

	function finishClose() {
		if (dialog.open) dialog.close();
		dialog.replaceChildren();
		document.title = restoreTitle;
		step = null;
		closing = false;
		const link = source && source.closest('a');
		if (link) link.focus({ preventScroll: true });
	}

	function animateClose() {
		if (closing || !dialog.open) return;
		closing = true;
		zoom(true).then(finishClose);
	}

	// Close from inside the page: step back through history if we pushed, otherwise
	// (the product URL was opened directly) swap the address back to home.
	function requestClose() {
		if (pushed) {
			pushed = false;
			history.back(); // popstate does the closing
			return;
		}
		history.replaceState(null, '', cfg.home || '/');
		if (cfg.siteName) restoreTitle = cfg.siteName;
		animateClose();
	}

	window.addEventListener('popstate', (e) => {
		const id = e.state && e.state.bhProduct;
		if (id) {
			pushed = true;
			if (!open(id, false)) location.reload();
		} else if (dialog.open) {
			pushed = false;
			animateClose();
		}
	});

	// Plain left-clicks on a card open the dialog; Cmd/Ctrl/middle-click still open the URL.
	document.addEventListener('click', (e) => {
		if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
		const link = e.target.closest('a[data-bh-product]');
		if (link && open(link.dataset.bhProduct, true)) e.preventDefault();
	});

	dialog.addEventListener('cancel', (e) => {
		e.preventDefault();
		requestClose();
	});
	dialog.addEventListener('click', (e) => {
		if (e.target === dialog || e.target.closest('[data-bh-close]')) requestClose();
	});
	dialog.addEventListener('keydown', (e) => {
		if (!step || e.target.closest('input, textarea, select')) return;
		if (e.key === 'ArrowLeft') step(-1);
		if (e.key === 'ArrowRight') step(1);
	});

	function setup(p) {
		if (!p) return;

		// Gallery
		const thumbs = Array.from(p.querySelectorAll('[data-bh-image]'));
		const main = p.querySelector('.bh-pd-main');
		const index = p.querySelector('[data-bh-index]');
		let current = 0;
		function show(i) {
			if (!thumbs.length) return;
			current = (i + thumbs.length) % thumbs.length;
			const t = thumbs[current];
			if (t.dataset.srcset) main.srcset = t.dataset.srcset;
			else main.removeAttribute('srcset');
			main.src = t.dataset.src;
			main.alt = t.dataset.alt;
			thumbs.forEach((b, j) => b.setAttribute('aria-current', j === current ? 'true' : 'false'));
			if (index) index.textContent = current + 1;
		}
		step = thumbs.length > 1 ? (d) => show(current + d) : null;

		// Options and add to bag
		const data = JSON.parse(p.dataset.vars || '{}');
		const groups = Array.from(p.querySelectorAll('.bh-pd-opts'));
		const multi = groups.find((g) => g.dataset.multi === '1');
		const add = p.querySelector('[data-bh-add]');
		const status = p.querySelector('.bh-pd-status');

		function selection() {
			const singles = {};
			let missing = null;
			groups.forEach((g) => {
				if (g === multi) return;
				const b = g.querySelector('[aria-pressed="true"]');
				if (b) singles[g.dataset.key] = b.dataset.v;
				else missing = missing || g.dataset.label;
			});
			const picks = multi
				? Array.from(multi.querySelectorAll('[aria-pressed="true"]')).map((b) => ({ value: b.dataset.v, label: b.textContent.trim() }))
				: [];
			return { singles, missing, picks };
		}

		function refresh() {
			if (!add || data.type !== 'variable') return;
			const s = selection();
			add.disabled = !s.picks.length || !!s.missing;
			if (!s.picks.length) add.textContent = 'Select a ' + multi.dataset.label.toLowerCase();
			else if (s.missing) add.textContent = 'Select a ' + s.missing.toLowerCase();
			else add.textContent = 'Add to bag';
		}

		function match(attrs) {
			return (data.vars || []).find((v) => Object.keys(attrs).every((k) => !v.attrs[k] || v.attrs[k] === attrs[k]));
		}

		// One request at a time: parallel add-to-cart calls race on the cart session.
		async function addToBag() {
			const jobs = [];
			const failed = [];
			if (data.type === 'variable') {
				const s = selection();
				s.picks.forEach((pick) => {
					const v = match(Object.assign({}, s.singles, { [multi.dataset.key]: pick.value }));
					if (v && v.stock) jobs.push({ id: v.id, label: pick.label });
					else failed.push(pick.label);
				});
			} else {
				jobs.push({ id: data.id, label: '' });
			}

			add.disabled = true;
			status.textContent = 'Adding…';
			const added = [];
			let fragments = null;
			for (const job of jobs) {
				try {
					const res = await fetch(cfg.addUrl, {
						method: 'POST',
						credentials: 'same-origin',
						body: new URLSearchParams({ product_id: job.id, quantity: 1 }),
					});
					const json = await res.json();
					if (!json || json.error) failed.push(job.label);
					else {
						added.push(job.label);
						fragments = json.fragments || fragments;
					}
				} catch (err) {
					failed.push(job.label);
				}
			}

			if (fragments) {
				Object.keys(fragments).forEach((selector) => {
					document.querySelectorAll(selector).forEach((el) => { el.outerHTML = fragments[selector]; });
				});
			}
			if (added.length && window.jQuery) window.jQuery(document.body).trigger('wc_fragment_refresh');

			const parts = [];
			if (added.length) parts.push(data.type === 'variable' ? `Added ${added.join(', ')} to bag.` : 'Added to bag.');
			if (failed.length) parts.push(data.type === 'variable' ? `Couldn't add ${failed.join(', ')}.` : `Couldn't add this piece.`);
			status.textContent = parts.join(' ');

			if (added.length && multi) multi.querySelectorAll('[aria-pressed="true"]').forEach((b) => b.setAttribute('aria-pressed', 'false'));
			if (data.type === 'variable') refresh();
			else add.disabled = false;
		}

		p.addEventListener('click', (e) => {
			const thumb = e.target.closest('[data-bh-image]');
			if (thumb) return show(Number(thumb.dataset.bhImage));
			const arrow = e.target.closest('[data-bh-step]');
			if (arrow) return show(current + Number(arrow.dataset.bhStep));
			const opt = e.target.closest('.bh-pd-opt');
			if (opt && !opt.disabled) {
				const group = opt.closest('.bh-pd-opts');
				const on = opt.getAttribute('aria-pressed') !== 'true';
				if (group !== multi) group.querySelectorAll('.bh-pd-opt').forEach((b) => b.setAttribute('aria-pressed', 'false'));
				opt.setAttribute('aria-pressed', on ? 'true' : 'false');
				if (status) status.textContent = '';
				return refresh();
			}
			if (e.target.closest('[data-bh-add]') && add && !add.disabled) addToBag();
		});
	}

	// A product URL opened directly renders Home with this product's dialog open.
	if (cfg.open) open(cfg.open, false);
})();
